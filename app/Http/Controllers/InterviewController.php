<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\Candidate;
use App\Models\Company;

class InterviewController extends Controller
{
    // ---------------- COMPANY VIEW ALL INTERVIEWS ---------------- //
    public function index()
    {
        $companyId = session('user_id');
        $company = Company::find($companyId);
        
        // Get existing interviews with candidate and job info
        $interviews = Interview::where('CompanyID', $companyId)
            ->with(['candidate', 'job'])
            ->latest()
            ->get()
            ->map(function($interview) {
                return [
                    'InterviewID' => $interview->InterviewID ?? $interview->id,
                    'CandidateName' => $interview->candidate ? $interview->candidate->FullName : 'Unknown',
                    'CandidateEmail' => $interview->candidate ? $interview->candidate->Email : '',
                    'JobTitle' => $interview->job ? $interview->job->JobTitle : 'Unknown Position',
                    'ScheduledDate' => $interview->ScheduledDate,
                    'ScheduledTime' => $interview->ScheduledTime,
                    'InterviewMode' => $interview->InterviewMode,
                    'MeetingLink' => $interview->MeetingLink,
                    'Location' => $interview->Location,
                    'Status' => $interview->Status,
                    'Notes' => $interview->Notes
                ];
            });
        
        // Get job positions for the company
        $jobPositions = JobPosting::where('CompanyID', $companyId)
            ->where('Status', 'Active')
            ->get(['JobID', 'JobTitle']);
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;

        return view('interview.interview', compact(
            'interviews', 
            'companyName', 
            'companyLogo',
            'jobPositions',
            'companyId'
        ));
    }

    // ---------------- API: GET CANDIDATES FOR INTERVIEW ---------------- //
    public function apiGetCandidates(Request $request, $jobId)
    {
        try {
            $companyId = session('user_id');
            
            // Get candidates who have applied and either:
            // 1. Passed the MCQ exam, OR
            // 2. No exam was assigned (direct application)
            $candidates = JobApplication::where('JobID', $jobId)
                ->with('candidate')
                ->get()
                ->filter(function($app) {
                    // Include candidates who applied and haven't been rejected
                    return $app->Status !== 'Rejected' && $app->candidate;
                })
                ->map(function($app) {
                    $candidate = $app->candidate;
                    return [
                        'candidateId' => $candidate->CandidateID ?? $candidate->id,
                        'name' => $candidate->FullName,
                        'email' => $candidate->Email,
                        'applicationDate' => $app->ApplicationDate ?? $app->created_at,
                        'status' => $app->Status,
                        'examScore' => $app->ExamScore ?? null,
                        'passed' => $app->ExamPassed ?? true // Default to true if no exam
                    ];
                })
                ->values();
            
            return response()->json([
                'success' => true,
                'candidates' => $candidates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading candidates: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------------- API: SCHEDULE INTERVIEWS ---------------- //
    public function apiSchedule(Request $request)
    {
        try {
            $companyId = session('user_id');
            
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }
            
            // Validate request
            $request->validate([
                'interviewMethod' => 'required|in:virtual,onsite',
                'positionId' => 'required',
                'candidateIds' => 'required|array|min:1',
                'interviewDate' => 'required|date',
                'interviewTime' => 'required'
            ]);
            
            $interviewMethod = $request->input('interviewMethod');
            $meetingLink = $request->input('meetingLink', '');
            $interviewLocation = $request->input('interviewLocation', '');
            $positionId = $request->input('positionId');
            $candidateIds = $request->input('candidateIds');
            $interviewDate = $request->input('interviewDate');
            $interviewTime = $request->input('interviewTime');
            
            // Validate method-specific fields
            if ($interviewMethod === 'virtual' && empty($meetingLink)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meeting link is required for virtual interviews'
                ], 400);
            }
            
            if ($interviewMethod === 'onsite' && empty($interviewLocation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location is required for on-site interviews'
                ], 400);
            }
            
            // Get job title for interview title
            $job = JobPosting::find($positionId);
            $interviewTitle = $job ? $job->JobTitle . ' - Interview' : 'Interview';
            
            $interviewIds = [];
            
            // Create interview records for each candidate
            foreach ($candidateIds as $candidateId) {
                $location = $interviewMethod === 'virtual' ? 'Online' : $interviewLocation;
                $platform = $interviewMethod === 'virtual' ? 'Virtual Meeting' : 'In-person';
                $storedMeetingLink = $interviewMethod === 'virtual' ? $meetingLink : '';
                
                $interview = Interview::create([
                    'CandidateID' => $candidateId,
                    'CompanyID' => $companyId,
                    'JobID' => $positionId,
                    'InterviewTitle' => $interviewTitle,
                    'InterviewType' => 'technical',
                    'InterviewMode' => $interviewMethod,
                    'Platform' => $platform,
                    'MeetingLink' => $storedMeetingLink,
                    'ScheduledDate' => $interviewDate,
                    'ScheduledTime' => $interviewTime,
                    'Location' => $location,
                    'Status' => 'Scheduled'
                ]);
                
                $interviewIds[] = $interview->InterviewID ?? $interview->id;
                
                // Update application status to 'Interview Scheduled'
                JobApplication::where('CandidateID', $candidateId)
                    ->where('JobID', $positionId)
                    ->update(['Status' => 'Interview Scheduled']);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Interview scheduled successfully for ' . count($candidateIds) . ' candidate(s)',
                'interviewIds' => $interviewIds
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scheduling interview: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------------- SCHEDULE AN INTERVIEW (FORM POST) ---------------- //
    public function schedule(Request $request)
    {
        $request->validate([
            'CandidateID' => 'required',
            'JobID'       => 'required',
            'InterviewDate' => 'required',
            'InterviewTime' => 'required'
        ]);

        Interview::create([
            'CandidateID'   => $request->CandidateID,
            'CompanyID'     => session('user_id'),
            'JobID'         => $request->JobID,
            'ScheduledDate' => $request->InterviewDate,
            'ScheduledTime' => $request->InterviewTime,
            'InterviewMode' => $request->InterviewMode ?? 'Online',
            'Status'        => 'Scheduled',
            'Notes'         => $request->Notes,
            'MeetingLink'   => $request->MeetingLink,
            'Location'      => $request->Location
        ]);

        return back()->with('success', 'Interview scheduled successfully!');
    }

    // ---------------- CANDIDATE VIEW INTERVIEW SCHEDULE ---------------- //
    public function candidateSchedule()
    {
        $candidateId = session('user_id');
        
        // Get candidate info
        $candidate = Candidate::find($candidateId);
        $candidateName = $candidate ? $candidate->FullName : 'User';
        $candidateProfilePicture = $candidate && $candidate->ProfilePicture 
            ? asset($candidate->ProfilePicture) 
            : null;
        
        // Get all interviews for this candidate
        $interviews = Interview::where('CandidateID', $candidateId)
            ->with(['company', 'job'])
            ->orderBy('ScheduledDate', 'desc')
            ->get();
        
        $now = now();
        $upcomingInterviews = [];
        $pastInterviews = [];
        
        foreach ($interviews as $interview) {
            $interviewDateTime = \Carbon\Carbon::parse(
                $interview->ScheduledDate . ' ' . ($interview->ScheduledTime ?? '09:00:00')
            );
            
            $interviewData = [
                'InterviewID' => $interview->InterviewID ?? $interview->id,
                'InterviewTitle' => $interview->InterviewTitle ?? ($interview->job ? $interview->job->JobTitle : 'Interview'),
                'CompanyName' => $interview->company ? $interview->company->CompanyName : 'Company',
                'ScheduledDate' => $interview->ScheduledDate,
                'ScheduledTime' => $interview->ScheduledTime ?? '09:00:00',
                'InterviewMode' => $interview->InterviewMode ?? 'Online',
                'Platform' => $interview->Platform ?? 'Video Call',
                'MeetingLink' => $interview->MeetingLink ?? null,
                'Location' => $interview->Location ?? 'Online',
                'Status' => $interview->Status ?? 'Scheduled',
                'Notes' => $interview->Notes
            ];
            
            if ($interviewDateTime->gt($now)) {
                $upcomingInterviews[] = $interviewData;
            } else {
                $pastInterviews[] = $interviewData;
            }
        }
        
        return view('interview.schedule', compact(
            'upcomingInterviews', 
            'pastInterviews',
            'candidateName',
            'candidateProfilePicture'
        ));
    }

    // ---------------- API: GET COMPANY JOB POSITIONS ---------------- //
    public function apiGetJobPositions()
    {
        try {
            $companyId = session('user_id');
            
            $jobs = JobPosting::where('CompanyID', $companyId)
                ->where('Status', 'Active')
                ->get(['JobID', 'JobTitle']);
            
            return response()->json([
                'success' => true,
                'jobs' => $jobs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading jobs: ' . $e->getMessage()
            ], 500);
        }
    }
}
