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
use App\Models\JobApplication;
use Illuminate\Support\Facades\Log;


class ExamController extends Controller
{
    // --------------------------------------------------
    // COMPANY: LIST ALL EXAMS
    // --------------------------------------------------
    public function index()
    {
        $companyId = session('user_id');
        $exams = Exam::where('CompanyID', $companyId)->latest()->get();

        return view('exam.review', compact('exams'));
    }

    // --------------------------------------------------
    // COMPANY: SHOW CREATE EXAM FORM
    // --------------------------------------------------
    public function create(Request $request)
    {
        $companyId = session('user_id');
        
        // Get active and pending job posts for the dropdown
        // Include Pending jobs so companies can create exams for them
        $jobPosts = JobPosting::where('CompanyID', $companyId)
            ->whereIn('Status', ['Active', 'Pending'])
            ->select('JobID', 'JobTitle', 'Department', 'Location', 'JobType', 'SalaryMin', 'SalaryMax', 'Currency', 'Status')
            ->get()
            ->toArray();
        
        // Get selected job_id from query parameter (if redirected from job post)
        $selectedJobId = $request->query('job_id');
        $selectedDepartment = $request->query('department');
        
        return view('exam.create', compact('jobPosts', 'selectedJobId', 'selectedDepartment'));
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
    // AUTO QUESTION GENERATOR (CS Engineering Questions)
    // --------------------------------------------------
    private function autoGenerateQuestions($exam)
    {
        // Define comprehensive CS engineering questions
        $questionBank = [
            // Data Structures
            [
                'question' => 'What is the time complexity of searching an element in a balanced Binary Search Tree?',
                'options' => ['O(log n)', 'O(n)', 'O(n²)', 'O(1)'],
                'correct' => 0,
                'category' => 'Data Structures'
            ],
            [
                'question' => 'Which data structure uses LIFO (Last In First Out) principle?',
                'options' => ['Stack', 'Queue', 'Array', 'Linked List'],
                'correct' => 0,
                'category' => 'Data Structures'
            ],
            [
                'question' => 'What is the worst-case time complexity of QuickSort?',
                'options' => ['O(n²)', 'O(n log n)', 'O(n)', 'O(log n)'],
                'correct' => 0,
                'category' => 'Algorithms'
            ],
            [
                'question' => 'Which data structure is best suited for implementing a priority queue?',
                'options' => ['Heap', 'Stack', 'Array', 'Linked List'],
                'correct' => 0,
                'category' => 'Data Structures'
            ],
            [
                'question' => 'What is the space complexity of Merge Sort?',
                'options' => ['O(n)', 'O(1)', 'O(log n)', 'O(n²)'],
                'correct' => 0,
                'category' => 'Algorithms'
            ],
            // Object-Oriented Programming
            [
                'question' => 'Which OOP principle allows a class to inherit properties from another class?',
                'options' => ['Inheritance', 'Encapsulation', 'Polymorphism', 'Abstraction'],
                'correct' => 0,
                'category' => 'OOP'
            ],
            [
                'question' => 'What is the purpose of the "final" keyword in Java?',
                'options' => ['Prevents inheritance/modification', 'Makes methods public', 'Creates interfaces', 'Allocates memory'],
                'correct' => 0,
                'category' => 'OOP'
            ],
            [
                'question' => 'Which design pattern ensures only one instance of a class exists?',
                'options' => ['Singleton', 'Factory', 'Observer', 'Strategy'],
                'correct' => 0,
                'category' => 'Design Patterns'
            ],
            [
                'question' => 'What is method overloading?',
                'options' => ['Same method name with different parameters', 'Redefining parent method in child', 'Hiding class members', 'Creating abstract methods'],
                'correct' => 0,
                'category' => 'OOP'
            ],
            [
                'question' => 'Which principle states that a class should have only one reason to change?',
                'options' => ['Single Responsibility Principle', 'Open/Closed Principle', 'Liskov Substitution', 'Interface Segregation'],
                'correct' => 0,
                'category' => 'Design Patterns'
            ],
            // Database
            [
                'question' => 'Which SQL keyword is used to retrieve unique values from a column?',
                'options' => ['DISTINCT', 'UNIQUE', 'DIFFERENT', 'SINGLE'],
                'correct' => 0,
                'category' => 'Database'
            ],
            [
                'question' => 'What does ACID stand for in database transactions?',
                'options' => ['Atomicity, Consistency, Isolation, Durability', 'Access, Control, Integration, Data', 'Automated, Controlled, Indexed, Distributed', 'Available, Consistent, Isolated, Durable'],
                'correct' => 0,
                'category' => 'Database'
            ],
            [
                'question' => 'Which type of JOIN returns all rows when there is a match in either table?',
                'options' => ['FULL OUTER JOIN', 'INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN'],
                'correct' => 0,
                'category' => 'Database'
            ],
            [
                'question' => 'What is normalization in databases?',
                'options' => ['Organizing data to reduce redundancy', 'Encrypting database records', 'Creating database backups', 'Indexing database columns'],
                'correct' => 0,
                'category' => 'Database'
            ],
            [
                'question' => 'Which index type is most efficient for equality searches?',
                'options' => ['Hash Index', 'B-Tree Index', 'Bitmap Index', 'Full-Text Index'],
                'correct' => 0,
                'category' => 'Database'
            ],
            // Networking
            [
                'question' => 'Which protocol is used for secure web communication?',
                'options' => ['HTTPS', 'HTTP', 'FTP', 'SMTP'],
                'correct' => 0,
                'category' => 'Networking'
            ],
            [
                'question' => 'What is the default port number for HTTP?',
                'options' => ['80', '443', '21', '22'],
                'correct' => 0,
                'category' => 'Networking'
            ],
            [
                'question' => 'Which layer of the OSI model handles routing?',
                'options' => ['Network Layer', 'Transport Layer', 'Data Link Layer', 'Application Layer'],
                'correct' => 0,
                'category' => 'Networking'
            ],
            [
                'question' => 'What does DNS stand for?',
                'options' => ['Domain Name System', 'Digital Network Service', 'Data Network Security', 'Dynamic Name Server'],
                'correct' => 0,
                'category' => 'Networking'
            ],
            [
                'question' => 'Which protocol is connectionless?',
                'options' => ['UDP', 'TCP', 'HTTP', 'FTP'],
                'correct' => 0,
                'category' => 'Networking'
            ],
            // Operating Systems
            [
                'question' => 'What is a deadlock in operating systems?',
                'options' => ['Processes waiting indefinitely for resources', 'CPU running at full capacity', 'Memory overflow condition', 'Disk fragmentation'],
                'correct' => 0,
                'category' => 'Operating Systems'
            ],
            [
                'question' => 'Which scheduling algorithm may cause starvation?',
                'options' => ['Priority Scheduling', 'Round Robin', 'FCFS', 'Shortest Job First'],
                'correct' => 0,
                'category' => 'Operating Systems'
            ],
            [
                'question' => 'What is virtual memory?',
                'options' => ['Using disk space as extended RAM', 'Cloud-based storage', 'CPU cache memory', 'Graphics memory'],
                'correct' => 0,
                'category' => 'Operating Systems'
            ],
            [
                'question' => 'Which memory allocation strategy causes external fragmentation?',
                'options' => ['Contiguous allocation', 'Paging', 'Segmentation with paging', 'Pure demand paging'],
                'correct' => 0,
                'category' => 'Operating Systems'
            ],
            [
                'question' => 'What is a semaphore used for?',
                'options' => ['Process synchronization', 'Memory management', 'File handling', 'Network communication'],
                'correct' => 0,
                'category' => 'Operating Systems'
            ],
            // Web Development
            [
                'question' => 'Which HTTP method is idempotent and used to update resources?',
                'options' => ['PUT', 'POST', 'PATCH', 'DELETE'],
                'correct' => 0,
                'category' => 'Web Development'
            ],
            [
                'question' => 'What does REST stand for?',
                'options' => ['Representational State Transfer', 'Remote Execution Server Technology', 'Reliable Endpoint Service Transfer', 'Request Entity State Type'],
                'correct' => 0,
                'category' => 'Web Development'
            ],
            [
                'question' => 'Which status code indicates a resource was successfully created?',
                'options' => ['201', '200', '204', '301'],
                'correct' => 0,
                'category' => 'Web Development'
            ],
            [
                'question' => 'What is CORS in web development?',
                'options' => ['Cross-Origin Resource Sharing', 'Content Origin Restriction System', 'Central Object Request Service', 'Common Origin Response Standard'],
                'correct' => 0,
                'category' => 'Web Development'
            ],
            [
                'question' => 'Which JavaScript method is used to make asynchronous HTTP requests?',
                'options' => ['fetch()', 'request()', 'http()', 'ajax()'],
                'correct' => 0,
                'category' => 'Web Development'
            ],
            // Programming Concepts
            [
                'question' => 'What is recursion?',
                'options' => ['A function calling itself', 'Looping through arrays', 'Object inheritance', 'Memory allocation'],
                'correct' => 0,
                'category' => 'Programming'
            ],
            [
                'question' => 'Which sorting algorithm has the best average-case time complexity?',
                'options' => ['Merge Sort - O(n log n)', 'Bubble Sort - O(n²)', 'Selection Sort - O(n²)', 'Insertion Sort - O(n²)'],
                'correct' => 0,
                'category' => 'Algorithms'
            ],
            [
                'question' => 'What is the purpose of garbage collection?',
                'options' => ['Automatic memory management', 'File deletion', 'Network cleanup', 'Cache clearing'],
                'correct' => 0,
                'category' => 'Programming'
            ],
            [
                'question' => 'Which data structure is used in BFS (Breadth-First Search)?',
                'options' => ['Queue', 'Stack', 'Heap', 'Tree'],
                'correct' => 0,
                'category' => 'Algorithms'
            ],
            [
                'question' => 'What is the time complexity of accessing an element by index in an array?',
                'options' => ['O(1)', 'O(n)', 'O(log n)', 'O(n²)'],
                'correct' => 0,
                'category' => 'Data Structures'
            ],
            // Software Engineering
            [
                'question' => 'What is the purpose of version control systems like Git?',
                'options' => ['Track and manage code changes', 'Compile code faster', 'Debug applications', 'Deploy to servers'],
                'correct' => 0,
                'category' => 'Software Engineering'
            ],
            [
                'question' => 'Which development methodology emphasizes iterative development and customer feedback?',
                'options' => ['Agile', 'Waterfall', 'V-Model', 'Big Bang'],
                'correct' => 0,
                'category' => 'Software Engineering'
            ],
            [
                'question' => 'What does CI/CD stand for?',
                'options' => ['Continuous Integration/Continuous Deployment', 'Code Integration/Code Delivery', 'Central Interface/Central Database', 'Component Integration/Component Development'],
                'correct' => 0,
                'category' => 'Software Engineering'
            ],
            [
                'question' => 'What is unit testing?',
                'options' => ['Testing individual components in isolation', 'Testing the entire system', 'Testing user interface', 'Testing database connections'],
                'correct' => 0,
                'category' => 'Software Engineering'
            ],
            [
                'question' => 'Which principle suggests preferring composition over inheritance?',
                'options' => ['Favor Composition', 'Open/Closed Principle', 'Dependency Inversion', 'Single Responsibility'],
                'correct' => 0,
                'category' => 'Design Patterns'
            ],
            // Security
            [
                'question' => 'What is SQL injection?',
                'options' => ['Inserting malicious SQL code through user input', 'Optimizing SQL queries', 'Creating SQL backups', 'Migrating SQL databases'],
                'correct' => 0,
                'category' => 'Security'
            ],
            [
                'question' => 'Which encryption type uses the same key for encryption and decryption?',
                'options' => ['Symmetric encryption', 'Asymmetric encryption', 'Hashing', 'Digital signature'],
                'correct' => 0,
                'category' => 'Security'
            ],
            [
                'question' => 'What is XSS (Cross-Site Scripting)?',
                'options' => ['Injecting malicious scripts into web pages', 'Server-side caching', 'Cross-browser compatibility', 'Session management'],
                'correct' => 0,
                'category' => 'Security'
            ],
            [
                'question' => 'What is the purpose of hashing passwords?',
                'options' => ['Store passwords securely without reversibility', 'Encrypt passwords for transmission', 'Compress password data', 'Validate password format'],
                'correct' => 0,
                'category' => 'Security'
            ],
            [
                'question' => 'What does JWT stand for?',
                'options' => ['JSON Web Token', 'Java Web Technology', 'JavaScript Worker Thread', 'Joint Wireless Transfer'],
                'correct' => 0,
                'category' => 'Security'
            ],
            // Additional Questions
            [
                'question' => 'What is polymorphism in OOP?',
                'options' => ['Objects behaving differently based on their type', 'Creating multiple classes', 'Hiding implementation details', 'Combining multiple objects'],
                'correct' => 0,
                'category' => 'OOP'
            ],
            [
                'question' => 'Which data structure is best for implementing undo functionality?',
                'options' => ['Stack', 'Queue', 'Array', 'Hash Table'],
                'correct' => 0,
                'category' => 'Data Structures'
            ],
            [
                'question' => 'What is Big O notation used for?',
                'options' => ['Describing algorithm efficiency', 'Memory allocation', 'Variable naming', 'Code formatting'],
                'correct' => 0,
                'category' => 'Algorithms'
            ],
            [
                'question' => 'Which HTTP status code indicates "Not Found"?',
                'options' => ['404', '500', '403', '200'],
                'correct' => 0,
                'category' => 'Web Development'
            ],
            [
                'question' => 'What is the difference between == and === in JavaScript?',
                'options' => ['=== checks type and value, == only value', '== is for strings, === for numbers', 'No difference', '=== is deprecated'],
                'correct' => 0,
                'category' => 'Programming'
            ],
        ];

        // Shuffle questions and pick required count
        shuffle($questionBank);
        $selectedQuestions = array_slice($questionBank, 0, min($exam->QuestionCount, count($questionBank)));

        foreach ($selectedQuestions as $index => $questionData) {
            $q = ExamQuestion::create([
                'ExamID'        => $exam->ExamID,
                'QuestionType'  => 'multiple-choice',
                'QuestionText'  => $questionData['question'],
                'QuestionOrder' => $index + 1,
                'Points'        => 1
            ]);

            // Shuffle options while tracking correct answer
            $options = $questionData['options'];
            $correctIndex = $questionData['correct'];
            $correctText = $options[$correctIndex];
            
            // Create shuffled indices
            $indices = range(0, count($options) - 1);
            shuffle($indices);

            foreach ($indices as $order => $originalIndex) {
                ExamQuestionOption::create([
                    'QuestionID'   => $q->QuestionID,
                    'OptionText'   => $options[$originalIndex],
                    'IsCorrect'    => ($options[$originalIndex] === $correctText) ? 1 : 0,
                    'OptionOrder'  => $order + 1
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
        return view('exam.bulk_assignment', compact('schedules'));
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
    // CANDIDATE: VIEW AVAILABLE EXAMS
    // --------------------------------------------------
    public function candidateExams()
    {
        $candidateId = session('user_id');
        
        // Get candidate info
        $candidate = \App\Models\Candidate::find($candidateId);
        $candidateName = $candidate ? $candidate->FullName : 'Candidate';
        $candidateProfilePicture = $candidate && $candidate->ProfilePicture 
            ? asset($candidate->ProfilePicture) 
            : null;
        
        // Get all exam schedules for this candidate
        $schedules = ExamSchedule::where('CandidateID', $candidateId)
            ->with(['exam.company', 'job'])
            ->get();
        
        // Split into scheduled and completed
        $scheduledExams = [];
        $completedExams = [];
        
        foreach ($schedules as $schedule) {
            $exam = $schedule->exam;
            $job = $schedule->job;
            
            $examData = [
                'id' => $schedule->ScheduleID ?? $schedule->id,
                'examTitle' => $exam ? $exam->ExamTitle : 'Unknown Exam',
                'company' => $exam && $exam->company ? $exam->company->CompanyName : 'Unknown Company',
                'jobPosition' => $job ? $job->JobTitle : 'General Exam',
                'examDate' => $schedule->ScheduledDate ?? now()->toDateString(),
                'duration' => ($exam ? $exam->Duration : 30) . ' minutes',
                'questionCount' => $exam ? ($exam->QuestionCount ?? 0) : 0,
                'status' => $schedule->Status ?? 'Scheduled',
                'passingScore' => ($exam ? ($exam->PassingScore ?? 70) : 70) . '%',
                'score' => null
            ];
            
            // Check if completed
            $attempt = ExamAttempt::where('ScheduleID', $schedule->ScheduleID ?? $schedule->id)
                ->where('CandidateID', $candidateId)
                ->first();
                
            if ($attempt && $attempt->Status === 'Completed') {
                $examData['status'] = 'Completed';
                $totalQuestions = $exam ? ($exam->QuestionCount ?? 1) : 1;
                $examData['score'] = round(($attempt->Score / max($totalQuestions, 1)) * 100) . '%';
                $completedExams[] = $examData;
            } elseif ($schedule->Status === 'Completed') {
                $examData['status'] = 'Completed';
                $completedExams[] = $examData;
            } else {
                $scheduledExams[] = $examData;
            }
        }
        
        return view('exam.attend', compact(
            'scheduledExams',
            'completedExams',
            'candidateName',
            'candidateProfilePicture'
        ));
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

    // --------------------------------------------------
    // COMPANY: SHOW MANUAL EXAM CREATION FORM
    // --------------------------------------------------
    public function manualCreation(Request $request)
    {
        $companyId = session('user_id');
        $jobId = $request->query('job_id');
        $department = $request->query('department');
        
        // Get job details if jobId is provided
        $jobDetails = null;
        $positionDisplay = 'Selected Position';
        
        if ($jobId) {
            $jobDetails = JobPosting::where('JobID', $jobId)
                ->where('CompanyID', $companyId)
                ->first();
            
            if ($jobDetails) {
                $positionDisplay = $jobDetails->JobTitle;
            }
        }
        
        return view('exam.manual_creation', compact('jobId', 'department', 'positionDisplay'));
    }

    // --------------------------------------------------
    // COMPANY: SHOW AUTO EXAM CREATION FORM
    // --------------------------------------------------
    public function autoCreation(Request $request)
    {
        $companyId = session('user_id');
        $jobId = $request->query('job_id');
        $department = $request->query('department');
        
        // Get job details if jobId is provided
        $jobDetails = null;
        $positionDisplay = 'Selected Position';
        
        if ($jobId) {
            $jobDetails = JobPosting::where('JobID', $jobId)
                ->where('CompanyID', $companyId)
                ->first();
            
            if ($jobDetails) {
                $positionDisplay = $jobDetails->JobTitle;
            }
        }
        
        return view('exam.auto_creation', compact('jobId', 'department', 'positionDisplay'));
    }

    // --------------------------------------------------
    // COMPANY: STORE MANUAL EXAM WITH QUESTIONS
    // --------------------------------------------------
    public function storeManual(Request $request)
    {
        $request->validate([
            'quizTitle' => 'required|string|max:255',
            'examDuration' => 'required|integer|min:1',
            'job_id' => 'required',
            'department' => 'required'
        ]);

        $companyId = session('user_id');
        
        // Create exam
        $exam = Exam::create([
            'CompanyID' => $companyId,
            'ExamTitle' => $request->quizTitle,
            'Description' => 'Manual exam created for ' . $request->department,
            'Duration' => $request->examDuration * 60, // Convert to seconds
            'ExamType' => 'manual',
            'QuestionCount' => $request->totalQuestions ?? 0,
            'PassingScore' => $request->passingMark ?? 70,
            'IsActive' => 1
        ]);

        // Process questions
        $questionNumber = 1;
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'question_') === 0 && is_string($value)) {
                $questionNum = substr($key, 9);
                $questionText = $value;
                
                // Create question
                $question = ExamQuestion::create([
                    'ExamID' => $exam->ExamID,
                    'QuestionType' => 'multiple-choice',
                    'QuestionText' => $questionText,
                    'QuestionOrder' => $questionNumber,
                    'Points' => 1
                ]);

                // Get correct answer for this question
                $correctAnswer = $request->input("correct_{$questionNum}");
                $options = ['A', 'B', 'C', 'D'];
                
                foreach ($options as $index => $option) {
                    $optionText = $request->input("option_{$questionNum}_{$option}");
                    if ($optionText) {
                        ExamQuestionOption::create([
                            'QuestionID' => $question->QuestionID,
                            'OptionText' => $optionText,
                            'IsCorrect' => ($option === $correctAnswer) ? 1 : 0,
                            'OptionOrder' => $index + 1
                        ]);
                    }
                }
                
                $questionNumber++;
            }
        }

        // Update question count
        $exam->update(['QuestionCount' => $questionNumber - 1]);

        // AUTO-ASSIGN: Assign this exam to all existing applicants for this job
        $jobId = $request->job_id;
        $assignedCount = $this->assignExamToExistingApplicants($exam->ExamID, $jobId);
        
        // ACTIVATE JOB: Change job status from 'Pending' to 'Active' now that exam is created
        if ($jobId && ($questionNumber - 1) > 0) {
            JobPosting::where('JobID', $jobId)
                ->where('Status', 'Pending')
                ->update(['Status' => 'Active']);
        }
        
        $successMsg = 'Exam created and job is now active! Candidates can now see and apply to this job.';
        if ($assignedCount > 0) {
            $successMsg .= " Exam auto-assigned to {$assignedCount} existing applicant(s).";
        }

        return redirect('/company/exams/' . $exam->ExamID . '/edit')->with('success', $successMsg);
    }


    // --------------------------------------------------
    // COMPANY: AUTO-GENERATE EXAM WITH QUESTIONS
    // --------------------------------------------------
    public function storeAuto(Request $request)
    {
        $request->validate([
            'examTitle' => 'required|string|max:255',
            'examDuration' => 'required|integer|min:1',
            'questionCount' => 'required|integer|min:1',
            'passingScore' => 'required|integer|min:1|max:100',
            'job_id' => 'required',
            'department' => 'required'
        ]);

        $companyId = session('user_id');
        
        // Create exam
        $exam = Exam::create([
            'CompanyID' => $companyId,
            'ExamTitle' => $request->examTitle,
            'Description' => 'Auto-generated exam for ' . $request->department,
            'Duration' => $request->examDuration * 60, // Convert to seconds
            'ExamType' => 'auto-generated',
            'QuestionCount' => $request->questionCount,
            'PassingScore' => $request->passingScore,
            'IsActive' => 1
        ]);

        // Auto-generate placeholder questions
        $this->autoGenerateQuestions($exam);

        // AUTO-ASSIGN: Assign this exam to all existing applicants for this job
        $jobId = $request->job_id;
        $assignedCount = $this->assignExamToExistingApplicants($exam->ExamID, $jobId);
        
        // ACTIVATE JOB: Change job status from 'Pending' to 'Active' now that exam is created
        if ($jobId) {
            JobPosting::where('JobID', $jobId)
                ->where('Status', 'Pending')
                ->update(['Status' => 'Active']);
        }
        
        $successMsg = 'Exam created and job is now active! Candidates can now see and apply to this job.';
        if ($assignedCount > 0) {
            $successMsg .= " Exam auto-assigned to {$assignedCount} existing applicant(s).";
        }

        return redirect('/company/exams/' . $exam->ExamID . '/edit')->with('success', $successMsg);
    }


    // --------------------------------------------------
    // COMPANY: SHOW EXAM EDIT PAGE
    // --------------------------------------------------
    public function edit($id)
    {
        $companyId = session('user_id');
        
        $exam = Exam::where('ExamID', $id)
            ->where('CompanyID', $companyId)
            ->firstOrFail();
        
        $questions = ExamQuestion::where('ExamID', $id)
            ->with('options')
            ->orderBy('QuestionOrder')
            ->get();
        
        return view('exam.edit', compact('exam', 'questions'));
    }

    // --------------------------------------------------
    // COMPANY: UPDATE EXAM QUESTIONS
    // --------------------------------------------------
    public function update(Request $request, $id)
    {
        $companyId = session('user_id');
        
        $exam = Exam::where('ExamID', $id)
            ->where('CompanyID', $companyId)
            ->firstOrFail();
        
        // Delete removed questions
        if ($request->has('deleted_questions')) {
            ExamQuestion::whereIn('QuestionID', $request->deleted_questions)
                ->where('ExamID', $id)
                ->delete();
        }
        
        // Update existing questions
        if ($request->has('questions')) {
            foreach ($request->questions as $questionId => $questionData) {
                $question = ExamQuestion::where('QuestionID', $questionId)
                    ->where('ExamID', $id)
                    ->first();
                
                if ($question) {
                    // Update question text
                    $question->update([
                        'QuestionText' => $questionData['text']
                    ]);
                    
                    // Update options
                    if (isset($questionData['options'])) {
                        foreach ($questionData['options'] as $optionId => $optionText) {
                            ExamQuestionOption::where('OptionID', $optionId)
                                ->where('QuestionID', $questionId)
                                ->update([
                                    'OptionText' => $optionText,
                                    'IsCorrect' => ($questionData['correct'] == $optionId) ? 1 : 0
                                ]);
                        }
                    }
                }
            }
        }
        
        // Add new questions
        if ($request->has('new_questions')) {
            $maxOrder = ExamQuestion::where('ExamID', $id)->max('QuestionOrder') ?? 0;
            
            foreach ($request->new_questions as $newQuestionData) {
                if (!empty($newQuestionData['text'])) {
                    $maxOrder++;
                    
                    // Create the question
                    $question = ExamQuestion::create([
                        'ExamID' => $id,
                        'QuestionType' => 'multiple-choice',
                        'QuestionText' => $newQuestionData['text'],
                        'QuestionOrder' => $maxOrder,
                        'Points' => 1
                    ]);
                    
                    // Create options
                    if (isset($newQuestionData['options'])) {
                        $correctIndex = $newQuestionData['correct'] ?? 0;
                        
                        foreach ($newQuestionData['options'] as $optIndex => $optionText) {
                            ExamQuestionOption::create([
                                'QuestionID' => $question->QuestionID,
                                'OptionText' => $optionText,
                                'IsCorrect' => ($optIndex == $correctIndex) ? 1 : 0,
                                'OptionOrder' => $optIndex + 1
                            ]);
                        }
                    }
                }
            }
        }
        
        // Update question count
        $exam->update([
            'QuestionCount' => ExamQuestion::where('ExamID', $id)->count()
        ]);
        
        return redirect('/company/exams/' . $id . '/edit')->with('success', 'Exam questions updated successfully!');
    }

    // --------------------------------------------------
    // HELPER: ASSIGN EXAM TO ALL EXISTING APPLICANTS FOR A JOB
    // This is called when an exam is created to retroactively 
    // assign it to candidates who have already applied
    // --------------------------------------------------
    private function assignExamToExistingApplicants($examId, $jobId)
    {
        try {
            // Get all applicants for this job who don't already have this exam assigned
            $applicants = JobApplication::where('JobID', $jobId)
                ->whereNotExists(function ($query) use ($examId, $jobId) {
                    $query->select('ScheduleID')
                        ->from('exam_schedules')
                        ->whereColumn('exam_schedules.CandidateID', 'job_applications.CandidateID')
                        ->where('exam_schedules.ExamID', $examId)
                        ->where('exam_schedules.JobID', $jobId);
                })
                ->get();

            $assignedCount = 0;
            $exam = Exam::find($examId);

            foreach ($applicants as $applicant) {
                try {
                    ExamSchedule::create([
                        'ExamID' => $examId,
                        'CandidateID' => $applicant->CandidateID,
                        'JobID' => $jobId,
                        'ScheduledDate' => now()->toDateString(),
                        'ScheduledTime' => now()->toTimeString(),
                        'Status' => 'scheduled',
                        'Duration' => $exam ? $exam->Duration : 3600,
                        'MaxAttempts' => 1
                    ]);
                    $assignedCount++;
                } catch (\Exception $e) {
                    Log::warning("Failed to assign exam {$examId} to candidate {$applicant->CandidateID}: " . $e->getMessage());
                }
            }

            Log::info("Exam {$examId} auto-assigned to {$assignedCount} existing applicants for job {$jobId}");
            return $assignedCount;

        } catch (\Exception $e) {
            Log::error("Error in assignExamToExistingApplicants: " . $e->getMessage());
            return 0;
        }
    }

    // --------------------------------------------------
    // STATIC HELPER: ASSIGN EXISTING EXAMS TO A NEW APPLICANT
    // This is called from ApplicationController when a candidate applies
    // Only assigns ONE exam per job (the most recently created active exam)
    // --------------------------------------------------
    public static function assignExistingExamsToCandidate($candidateId, $jobId)
    {
        try {
            // Get the company that owns this job
            $job = JobPosting::find($jobId);
            if (!$job) {
                return 0;
            }
            
            $companyId = $job->CompanyID;

            // Check if candidate already has an exam assigned for this job
            $existingAssignment = ExamSchedule::where('CandidateID', $candidateId)
                ->where('JobID', $jobId)
                ->first();

            if ($existingAssignment) {
                Log::info("Candidate {$candidateId} already has exam assigned for job {$jobId}");
                return 0;
            }

            // Get the most recent active exam from this company that has questions
            // (matching original PHP logic: ORDER BY CreatedAt DESC LIMIT 1)
            $exam = Exam::where('CompanyID', $companyId)
                ->where('IsActive', 1)
                ->where('QuestionCount', '>', 0)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$exam) {
                Log::info("No active exam with questions found for company {$companyId}");
                return 0;
            }

            // Assign the exam to candidate
            try {
                ExamSchedule::create([
                    'ExamID' => $exam->ExamID,
                    'CandidateID' => $candidateId,
                    'JobID' => $jobId,
                    'ScheduledDate' => now()->toDateString(),
                    'ScheduledTime' => now()->toTimeString(),
                    'Status' => 'scheduled',
                    'Duration' => $exam->Duration ?? 3600,
                    'MaxAttempts' => 1
                ]);
                Log::info("Exam {$exam->ExamID} auto-assigned to new applicant {$candidateId} for job {$jobId}");
                return 1;
            } catch (\Exception $e) {
                Log::warning("Failed to assign exam {$exam->ExamID} to candidate {$candidateId}: " . $e->getMessage());
                return 0;
            }

        } catch (\Exception $e) {
            Log::error("Error in assignExistingExamsToCandidate: " . $e->getMessage());
            return 0;
        }
    }

}


