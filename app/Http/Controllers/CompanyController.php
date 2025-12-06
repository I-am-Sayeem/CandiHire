<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    /**
     * Show the company dashboard.
     */
    public function dashboard()
    {
        $company = Company::find(session('user_id'));
        return view('company.dashboard', compact('company'));
    }

    /**
     * Show the applications received by the company.
     */
    public function applications()
    {
        return view('company.applications');
    }

    /**
     * Show the job posting form.
     */
    public function jobPost()
    {
        return view('company.job_post');
    }

    /**
     * Show MCQ results for company's exams.
     */
    public function mcqResults()
    {
        return view('company.mcq_results');
    }

    /**
     * Show AI matching results.
     */
    public function aiMatching()
    {
        return view('company.ai_matching');
    }
}
