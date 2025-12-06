<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;

class CvController extends Controller
{
    /**
     * Show the CV builder page.
     */
    public function builder()
    {
        $candidateId = session('user_id');
        $candidate = Candidate::find($candidateId);
        
        $candidateName = $candidate->FullName ?? 'User';
        $candidateProfilePicture = $candidate->ProfilePicture ?? null;
        
        return view('cv.builder', compact('candidateName', 'candidateProfilePicture'));
    }

    /**
     * Show the CV checker page.
     */
    public function checker()
    {
        $candidateId = session('user_id');
        $candidate = Candidate::find($candidateId);
        
        $candidateName = $candidate->FullName ?? 'User';
        $candidateProfilePicture = $candidate->ProfilePicture ?? null;
        
        return view('cv.checker', compact('candidateName', 'candidateProfilePicture'));
    }

    /**
     * Show the CV processing page.
     */
    public function processing()
    {
        $candidateId = session('user_id');
        $candidate = Candidate::find($candidateId);
        
        $candidateName = $candidate->FullName ?? 'User';
        $candidateProfilePicture = $candidate->ProfilePicture ?? null;
        
        return view('cv.processing', compact('candidateName', 'candidateProfilePicture'));
    }
}
