<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interview;

class InterviewController extends Controller
{
    // ---------------- COMPANY VIEW ALL INTERVIEWS ---------------- //
    public function index()
    {
        $companyId = session('user_id');
        $interviews = Interview::where('CompanyID', $companyId)->latest()->get();

        return view('interview.interview', compact('interviews'));
    }

    // ---------------- SCHEDULE AN INTERVIEW ---------------- //
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
            'InterviewDate' => $request->InterviewDate,
            'InterviewTime' => $request->InterviewTime,
            'InterviewMode' => $request->InterviewMode ?? 'Online',
            'Status'        => 'Scheduled',
            'Notes'         => $request->Notes,
            'MeetingLink'   => $request->MeetingLink,
            'Location'      => $request->Location
        ]);

        return back()->with('success', 'Interview scheduled successfully!');
    }
}
