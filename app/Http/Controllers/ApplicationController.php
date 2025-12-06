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
