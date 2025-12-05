<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\ExamSchedule;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\JobPosting;

class ExamController extends Controller
{
    // --------------------------------------------------
    // COMPANY: LIST ALL EXAMS
    // --------------------------------------------------
    public function index()
    {
        $companyId = session('user_id');
        $exams = Exam::where('CompanyID', $companyId)->latest()->get();

        return view('company.exams.index', compact('exams'));
    }

    // --------------------------------------------------
    // COMPANY: SHOW CREATE EXAM FORM
    // --------------------------------------------------
    public function create()
    {
        return view('company.exams.create');
    }

    // --------------------------------------------------
    // COMPANY: STORE EXAM (Manual or Auto)
    // --------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'ExamTitle' => 'required',
            'ExamType' => 'required',   // Manual / Auto
            'Duration' => 'required'
        ]);

        $exam = Exam::create([
            'CompanyID'     => session('user_id'),
            'ExamTitle'     => $request->ExamTitle,
            'Description'   => $request->Description,
            'Duration'      => $request->Duration,
            'ExamType'      => $request->ExamType,
            'QuestionCount' => $request->QuestionCount ?? 0,
            'PassingScore'  => $request->PassingScore ?? 0,
            'IsActive'      => 1
        ]);

        // ---- AUTO EXAM CREATION ---- //
        if ($request->ExamType === 'Auto') {
            $this->autoGenerateQuestions($exam);
        }

        return redirect('/company/exams')->with('success', 'Exam created successfully!');
    }

    // --------------------------------------------------
    // AUTO QUESTION GENERATOR (Simple Version)
    // --------------------------------------------------
    private function autoGenerateQuestions($exam)
    {
        for ($i = 1; $i <= $exam->QuestionCount; $i++) {

            $q = ExamQuestion::create([
                'ExamID'        => $exam->ExamID,
                'QuestionType'  => 'MCQ',
                'QuestionText'  => "Auto-generated question $i",
                'QuestionOrder' => $i,
                'Points'        => 1
            ]);

            // 4 Auto Options
            for ($j = 1; $j <= 4; $j++) {
                ExamQuestionOption::create([
                    'QuestionID'   => $q->QuestionID,
                    'OptionText'   => "Option $j",
                    'IsCorrect'    => $j === 1 ? 1 : 0,
                    'OptionOrder'  => $j
                ]);
            }
        }
    }

    // --------------------------------------------------
    // CHECK EXAM ASSIGNMENTS FOR A JOB
    // --------------------------------------------------
    public function jobExamAssignments($jobId)
    {
        $schedules = ExamSchedule::where('JobID', $jobId)->latest()->get();
        return view('company.exams.assignments', compact('schedules'));
    }

    // --------------------------------------------------
    // COMPANY: ASSIGN EXAM TO A CANDIDATE
    // --------------------------------------------------
    public function assign(Request $request)
    {
        $request->validate([
            'ExamID'      => 'required',
            'CandidateID' => 'required',
            'JobID'       => 'required'
        ]);

        ExamSchedule::create([
            'ExamID'      => $request->ExamID,
            'CandidateID' => $request->CandidateID,
            'JobID'       => $request->JobID,
            'ScheduledDate' => now()->toDateString(),
            'ScheduledTime' => now()->toTimeString(),
            'Status'      => 'Pending',
            'Duration'    => Exam::find($request->ExamID)->Duration,
            'MaxAttempts' => 1
        ]);

        return back()->with('success', 'Exam assigned successfully!');
    }

    // --------------------------------------------------
    // CANDIDATE: TAKE EXAM
    // --------------------------------------------------
    public function takeExam($scheduleId)
    {
        $schedule = ExamSchedule::findOrFail($scheduleId);
        $exam     = Exam::findOrFail($schedule->ExamID);
        $questions = $exam->questions()->with('options')->get();

        // Create attempt if not exists
        $attempt = ExamAttempt::firstOrCreate(
            ['ScheduleID' => $scheduleId, 'CandidateID' => session('user_id')],
            ['ExamID' => $exam->ExamID, 'StartTime' => now(), 'Status' => 'In Progress']
        );

        return view('exam.take', compact('exam', 'questions', 'attempt'));
    }

    // --------------------------------------------------
    // CANDIDATE: SUBMIT EXAM
    // --------------------------------------------------
    public function submitExam(Request $request, $scheduleId)
    {
        $schedule = ExamSchedule::findOrFail($scheduleId);
        $exam = Exam::findOrFail($schedule->ExamID);

        $attempt = ExamAttempt::where('ScheduleID', $scheduleId)
                    ->where('CandidateID', session('user_id'))
                    ->first();

        $score = 0;

        foreach ($request->answers as $questionId => $optionId) {

            $option = ExamQuestionOption::find($optionId);

            ExamAnswer::create([
                'AttemptID'       => $attempt->AttemptID,
                'QuestionID'      => $questionId,
                'SelectedOptionID'=> $optionId,
                'IsCorrect'        => $option->IsCorrect,
                'PointsEarned'     => $option->IsCorrect ? 1 : 0
            ]);

            if ($option->IsCorrect) {
                $score++;
            }
        }

        $attempt->update([
            'EndTime' => now(),
            'Score'   => $score,
            'Status'  => 'Completed'
        ]);

        return redirect('/candidate/dashboard')->with('success', 'Exam submitted successfully!');
    }
}
