<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Http\Controllers\ExamController;


class ApplicationController extends Controller
{
    // ---------------- APPLY TO JOB ---------------- //
    public function apply(Request $request, $jobId)
    {
        $request->validate([
            'CoverLetter' => 'nullable',
        ]);

        $candidateId = session('user_id');

        JobApplication::create([
            'CandidateID' => $candidateId,
            'JobID' => $jobId,
            'ApplicationDate' => now(),
            'Status' => 'Submitted',
            'CoverLetter' => $request->CoverLetter
        ]);

        // AUTO-ASSIGN: Assign any existing exams for this job's company to the new applicant
        $examsAssigned = ExamController::assignExistingExamsToCandidate($candidateId, $jobId);
        
        $message = 'Application submitted!';
        if ($examsAssigned > 0) {
            $message .= " You have been assigned {$examsAssigned} exam(s). Check 'Attend Exam' to take them.";
        }

        return back()->with('success', $message);
    }

    // ---------------- API: STORE JOB APPLICATION (JSON) ---------------- //
    public function store(Request $request)
    {
        try {
            $candidateId = session('user_id');
            
            if (!$candidateId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to apply'
                ], 401);
            }
            
            $jobId = $request->input('jobId');
            $coverLetter = $request->input('coverLetter');
            $additionalNotes = $request->input('additionalNotes');
            
            // Check if already applied
            $existingApp = JobApplication::where('CandidateID', $candidateId)
                ->where('JobID', $jobId)
                ->first();
            
            if ($existingApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already applied to this job'
                ]);
            }
            
            // Create application
            $application = JobApplication::create([
                'CandidateID' => $candidateId,
                'JobID' => $jobId,
                'ApplicationDate' => now(),
                'Status' => 'Submitted',
                'CoverLetter' => $coverLetter,
                'Notes' => $additionalNotes
            ]);
            
            // Increment application count on job posting
            JobPosting::where('JobID', $jobId)->increment('ApplicationCount');
            
            // AUTO-ASSIGN: Assign any existing exams for this job's company to the new applicant
            $examsAssigned = ExamController::assignExistingExamsToCandidate($candidateId, $jobId);
            
            $message = 'Application submitted successfully!';
            if ($examsAssigned > 0) {
                $message .= " You have been assigned {$examsAssigned} exam(s). Check 'Attend Exam' to take them.";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'applicationId' => $application->ApplicationID ?? $application->id,
                'examsAssigned' => $examsAssigned
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting application: ' . $e->getMessage()
            ], 500);
        }
    }


    // ---------------- CANDIDATE VIEW APPLICATION STATUS ---------------- //
    public function candidateStatus()
    {
        $candidateId = session('user_id');
        
        // Get candidate info
        $candidate = \App\Models\Candidate::find($candidateId);
        $candidateName = $candidate ? $candidate->FullName : 'Candidate';
        $candidateProfilePicture = $candidate && $candidate->ProfilePicture 
            ? asset($candidate->ProfilePicture) 
            : null;
        
        // Get applications with job and company details
        $apps = JobApplication::where('CandidateID', $candidateId)
            ->with(['job.company'])
            ->latest()
            ->get();
        
        // Transform applications to the format expected by the view
        $applications = $apps->map(function($app) use ($candidate) {
            $job = $app->job;
            $company = $job ? $job->company : null;
            
            // Build salary range string
            $salaryRange = 'Not specified';
            if ($job && $job->SalaryMin && $job->SalaryMax) {
                $currency = $job->Currency ?? 'BDT';
                $salaryRange = number_format($job->SalaryMin) . ' - ' . number_format($job->SalaryMax) . ' ' . $currency;
            }
            
            return [
                'id' => $app->ApplicationID ?? $app->id,
                'jobId' => $job ? $job->JobID : null,
                'jobTitle' => $job ? $job->JobTitle : 'Unknown Position',
                'company' => $company ? $company->CompanyName : 'Unknown Company',
                'companyId' => $company ? $company->CompanyID : null,
                'location' => $job ? ($job->Location ?? 'Not specified') : 'Not specified',
                'jobType' => $job ? ($job->JobType ?? 'Not specified') : 'Not specified',
                'jobDescription' => $job ? ($job->JobDescription ?? '') : '',
                'requirements' => $job ? ($job->Requirements ?? '') : '',
                'status' => $app->Status ?? 'Submitted',
                'applicationDate' => $app->ApplicationDate ?? $app->created_at,
                'salaryRange' => $salaryRange,
                'isCompanyInvited' => $app->IsCompanyInvited ?? false,
                'coverLetter' => $app->CoverLetter ?? '',
                'notes' => $app->Notes ?? '',
                'contactPerson' => $company ? ($company->ContactPerson ?? $company->CompanyName) : 'Not available',
                'contactEmail' => $company ? ($company->Email ?? 'Not available') : 'Not available',
                // Candidate info for the modal
                'candidateName' => $candidate ? $candidate->FullName : 'Unknown',
                'candidateEmail' => $candidate ? $candidate->Email : 'Not available',
                'candidatePhone' => $candidate ? ($candidate->PhoneNumber ?? 'Not available') : 'Not available',
                'candidateSkills' => $candidate ? ($candidate->Skills ?? 'Not specified') : 'Not specified',
                'statusFlow' => [
                    [
                        'status' => 'Application Submitted',
                        'date' => $app->ApplicationDate ?? $app->created_at,
                        'completed' => true,
                        'notes' => ''
                    ]
                ]
            ];
        })->toArray();
        
        return view('candidate.application_status', compact(
            'applications',
            'candidateName',
            'candidateProfilePicture',
            'candidateId'
        ))->with('sessionCandidateId', $candidateId);
    }

    // ---------------- COMPANY VIEW APPLICANTS ---------------- //
    public function jobApplicants($jobId)
    {
        $job = JobPosting::findOrFail($jobId);

        $apps = JobApplication::where('JobID', $jobId)->latest()->get();

        return view('company.applications', compact('job', 'apps'));
    }
}
