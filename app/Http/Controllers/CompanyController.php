<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\JobPosting;

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
        $companyId = session('user_id');
        $company = Company::find($companyId);
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;
        
        // Get job posts for the dropdown selector
        $jobPosts = JobPosting::where('CompanyID', $companyId)
            ->select('JobID as id', 'JobTitle as title')
            ->get()
            ->toArray();
        
        return view('company.applications', compact('companyName', 'companyLogo', 'jobPosts'));
    }

    /**
     * Show the job posting form.
     */
    public function jobPost()
    {
        $companyId = session('user_id');
        $company = Company::find($companyId);
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;
        
        return view('company.job_post', compact('companyName', 'companyLogo'));
    }

    /**
     * Show MCQ results for company's exams.
     */
    public function mcqResults(Request $request)
    {
        $companyId = session('user_id');
        $company = Company::find($companyId);
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;
        
        // Get job positions for the selector
        $jobPositions = JobPosting::where('CompanyID', $companyId)
            ->select('JobID', 'JobTitle', 'Department')
            ->get()
            ->toArray();
        
        $selectedJobId = $request->query('job_id');
        
        return view('company.mcq_results', compact('companyName', 'companyLogo', 'jobPositions', 'selectedJobId'));
    }

    /**
     * Show AI matching results.
     */
    public function aiMatching()
    {
        $companyId = session('user_id');
        $company = Company::find($companyId);
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;
        
        return view('company.ai_matching', compact('companyName', 'companyLogo'));
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

    /**
     * API: Update company profile.
     */
    public function apiUpdateProfile(Request $request)
    {
        try {
            $companyId = session('user_id');
            
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            $company = Company::find($companyId);
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $extension = strtolower($file->getClientOriginalExtension());
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'
                    ], 422);
                }
                
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Logo file is too large. Maximum size is 5MB.'
                    ], 422);
                }
                
                $fileName = 'company_' . $companyId . '_' . time() . '.' . $extension;
                $file->move(public_path('uploads/logos'), $fileName);
                $company->Logo = 'uploads/logos/' . $fileName;
            }
            
            // Update text fields
            if ($request->has('companyName')) {
                $company->CompanyName = $request->companyName;
                session(['company_name' => $request->companyName]);
            }
            if ($request->has('industry')) {
                $company->Industry = $request->industry;
            }
            if ($request->has('companySize')) {
                $company->CompanySize = $request->companySize;
            }
            if ($request->has('phoneNumber')) {
                $company->PhoneNumber = $request->phoneNumber;
            }
            if ($request->has('companyDescription')) {
                $company->CompanyDescription = $request->companyDescription;
            }
            if ($request->has('website')) {
                $company->Website = $request->website;
            }
            if ($request->has('address')) {
                $company->Address = $request->address;
            }
            if ($request->has('city')) {
                $company->City = $request->city;
            }
            if ($request->has('state')) {
                $company->State = $request->state;
            }
            if ($request->has('country')) {
                $company->Country = $request->country;
            }
            if ($request->has('postalCode')) {
                $company->PostalCode = $request->postalCode;
            }
            
            $company->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'companyName' => $company->CompanyName,
                'logo' => $company->Logo ? asset($company->Logo) : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
