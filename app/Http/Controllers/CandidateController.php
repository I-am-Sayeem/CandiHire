<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;

class CandidateController extends Controller
{
    /**
     * Show the candidate dashboard.
     */
    public function dashboard()
    {
        return view('candidate.dashboard');
    }

    /**
     * Show the candidate profile page.
     */
    public function profile()
    {
        $candidate = Candidate::find(session('user_id'));
        return view('candidate.profile', compact('candidate'));
    }

    /**
     * Update the candidate profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Email' => 'required|email',
            'PhoneNumber' => 'nullable|string|max:20',
            'Location' => 'nullable|string|max:255',
            'Summary' => 'nullable|string',
            'Skills' => 'nullable|string',
        ]);

        $candidate = Candidate::find(session('user_id'));
        $candidate->update($request->only([
            'FullName', 'Email', 'PhoneNumber', 'Location', 'Summary', 'Skills'
        ]));

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Show candidate details (public view).
     */
    public function details($id)
    {
        $candidate = Candidate::findOrFail($id);
        return view('candidate.details', compact('candidate'));
    }
}
