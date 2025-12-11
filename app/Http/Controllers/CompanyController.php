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

    /**
     * API: Get company profile as JSON.
     */
    public function apiShow($id)
    {
        try {
            $company = Company::find($id);
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'company' => [
                    'CompanyID' => $company->CompanyID,
                    'CompanyName' => $company->CompanyName,
                    'Industry' => $company->Industry,
                    'CompanySize' => $company->CompanySize,
                    'Email' => $company->Email,
                    'PhoneNumber' => $company->PhoneNumber,
                    'CompanyDescription' => $company->CompanyDescription,
                    'Website' => $company->Website,
                    'Logo' => $company->Logo,
                    'Address' => $company->Address,
                    'City' => $company->City,
                    'State' => $company->State,
                    'Country' => $company->Country
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading company details'
            ], 500);
        }
    }
}
