<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\JobPosting;

class ApplicationController extends Controller
{
    // ---------------- APPLY TO JOB ---------------- //
    public function apply(Request $request, $jobId)
    {
        $request->validate([
            'CoverLetter' => 'nullable',
        ]);

        JobApplication::create([
            'CandidateID' => session('user_id'),
            'JobID' => $jobId,
            'ApplicationDate' => now(),
            'Status' => 'Submitted',
            'CoverLetter' => $request->CoverLetter
        ]);

        return back()->with('success', 'Application submitted!');
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
            
            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'applicationId' => $application->ApplicationID ?? $application->id
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
        $apps = JobApplication::where('CandidateID', session('user_id'))->latest()->get();

        return view('candidate.application_status', compact('apps'));
    }

    // ---------------- COMPANY VIEW APPLICANTS ---------------- //
    public function jobApplicants($jobId)
    {
        $job = JobPosting::findOrFail($jobId);

        $apps = JobApplication::where('JobID', $jobId)->latest()->get();

        return view('company.applications', compact('job', 'apps'));
    }
}
