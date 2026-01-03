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
        $sessionCandidateId = $candidateId;
        
        return view('cv.builder', compact('candidateName', 'candidateProfilePicture', 'sessionCandidateId'));
    }

    /**
     * Show the CV checker page.
     */
    public function checker()
    {
        $companyId = session('user_id');
        $company = \App\Models\Company::find($companyId);
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;
        
        return view('cv.checker', compact('companyName', 'companyLogo'));
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
