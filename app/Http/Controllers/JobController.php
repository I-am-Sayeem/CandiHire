<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPosting;
use App\Models\JobApplication;

class JobController extends Controller
{
    // ---------------- PUBLIC JOB LIST ---------------- //
    public function index()
    {
        $jobs = JobPosting::latest()->paginate(10);
        return view('candidate.dashboard', compact('jobs'));
    }

    // ---------------- API: GET JOB POSTS (JSON) ---------------- //
    public function apiIndex(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $search = $request->input('search', '');
            
            $query = JobPosting::with('company')
                ->where('Status', 'Active')
                ->orderBy('created_at', 'desc');
            
            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('JobTitle', 'like', "%{$search}%")
                      ->orWhere('JobDescription', 'like', "%{$search}%")
                      ->orWhere('Skills', 'like', "%{$search}%")
                      ->orWhere('Location', 'like', "%{$search}%");
                });
            }
            
            // Additional filters
            if ($request->input('company')) {
                $query->whereHas('company', function($q) use ($request) {
                    $q->where('CompanyName', 'like', "%{$request->input('company')}%");
                });
            }
            if ($request->input('location')) {
                $query->where('Location', 'like', "%{$request->input('location')}%");
            }
            if ($request->input('jobType')) {
                $query->where('JobType', $request->input('jobType'));
            }
            if ($request->input('skills')) {
                $query->where('Skills', 'like', "%{$request->input('skills')}%");
            }
            
            $jobs = $query->skip($offset)->take($limit)->get();
            
            // Format the response to match what the frontend expects
            $posts = $jobs->map(function($job) {
                return [
                    'PostID' => $job->JobID,
                    'JobID' => $job->JobID,
                    'CompanyID' => $job->CompanyID,
                    'CompanyName' => $job->company ? $job->company->CompanyName : 'Unknown Company',
                    'CompanyLogo' => ($job->company && $job->company->Logo) ? asset($job->company->Logo) : null,
                    'JobTitle' => $job->JobTitle,
                    'JobDescription' => $job->JobDescription,
                    'Requirements' => $job->Requirements,
                    'Skills' => $job->Skills,
                    'Location' => $job->Location,
                    'JobType' => $job->JobType,
                    'SalaryMin' => $job->SalaryMin,
                    'SalaryMax' => $job->SalaryMax,
                    'Currency' => $job->Currency ?? 'USD',
                    'ExperienceLevel' => $job->ExperienceLevel,
                    'PostedDate' => $job->created_at ? $job->created_at->toIso8601String() : null,
                    'Status' => $job->Status,
                    'ApplicationCount' => $job->ApplicationCount ?? 0
                ];
            });
            
            return response()->json([
                'success' => true,
                'posts' => $posts,
                'total' => $query->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading posts: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------------- JOB DETAILS ---------------- //
    public function view($id)
    {
        $job = JobPosting::findOrFail($id);
        return view('company.job_post', compact('job'));
    }

    // ---------------- COMPANY JOB LIST ---------------- //
    public function companyJobs()
    {
        $companyId = session('user_id');
        $company = \App\Models\Company::find($companyId);
        $jobs = JobPosting::withCount('applications')
            ->where('CompanyID', $companyId)
            ->latest()
            ->get();
        
        // Format jobs for the view
        $jobPosts = $jobs->map(function($job) {
            return [
                'id' => $job->JobID,
                'title' => $job->JobTitle,
                'department' => $job->Department ?? 'General',
                'type' => $job->JobType,
                'location' => $job->Location,
                'posted_date' => $job->created_at ? $job->created_at->format('M d, Y') : 'N/A',
                'applications' => $job->applications_count ?? 0,
                'status' => $job->Status
            ];
        });
        
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;

        return view('company.job_post', compact('jobPosts', 'companyName', 'companyLogo'));
    }

    // ---------------- CREATE JOB FORM ---------------- //
    public function create()
    {
        return view('company.job_post');
    }

    // ---------------- STORE NEW JOB ---------------- //
    public function store(Request $request)
    {
        $request->validate([
            'JobTitle' => 'required',
            'JobDescription' => 'required'
        ]);

        $job = JobPosting::create([
            'CompanyID' => session('user_id'),
            'JobTitle' => $request->JobTitle,
            'JobDescription' => $request->JobDescription,
            'Requirements' => $request->Requirements,
            'Skills' => $request->Skills,
            'Location' => $request->Location,
            'JobType' => $request->JobType,
            'SalaryMin' => $request->SalaryMin,
            'SalaryMax' => $request->SalaryMax,
            'Department' => $request->Department ?? 'General',
            'Status' => 'Pending', // Job starts as Pending until exam is created
            'PostedDate' => now()
        ]);

        // Redirect to exam creation page - job won't be active until exam is created
        return redirect('/company/exams/create?job_id=' . $job->JobID . '&department=' . urlencode($job->Department ?? 'General'))
            ->with('info', 'Job saved! Please create an exam for this position. The job will be visible to candidates after the exam is created.');
    }


    // ---------------- API: STORE NEW JOB (JSON) ---------------- //
    public function apiStore(Request $request)
    {
        try {
            $request->validate([
                'jobTitle' => 'required|string|max:255',
                'jobDescription' => 'required|string',
                'location' => 'required|string|max:255',
                'jobType' => 'required|string'
            ]);

            $job = JobPosting::create([
                'CompanyID' => session('user_id'),
                'JobTitle' => $request->jobTitle,
                'Department' => $request->department ?? 'General',
                'JobDescription' => $request->jobDescription,
                'Requirements' => $request->requirements,
                'Responsibilities' => $request->responsibilities,
                'Skills' => $request->skills,
                'Location' => $request->location,
                'JobType' => $request->jobType,
                'SalaryMin' => $request->salaryMin ? floatval($request->salaryMin) : null,
                'SalaryMax' => $request->salaryMax ? floatval($request->salaryMax) : null,
                'Currency' => $request->currency ?? 'USD',
                'ExperienceLevel' => $request->experienceLevel ?? 'mid',
                'EducationLevel' => $request->educationLevel ?? 'bachelor',
                'ClosingDate' => $request->closingDate ?: null,
                'Status' => 'Pending', // Job starts as Pending until exam is created
                'PostedDate' => now(),
                'ApplicationCount' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job saved! Please create an exam for this position. The job will be visible to candidates after the exam is created.',
                'jobId' => $job->JobID,
                'status' => 'Pending',
                'requiresExam' => true,
                'examCreateUrl' => '/company/exams/create?job_id=' . $job->JobID . '&department=' . urlencode($job->Department ?? 'General')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }


    // ---------------- EDIT JOB ---------------- //
    public function edit($id)
    {
        $job = JobPosting::findOrFail($id);
        return view('company.job_post', compact('job'));
    }

    // ---------------- UPDATE JOB ---------------- //
    public function update(Request $request, $id)
    {
        $job = JobPosting::findOrFail($id);

        $job->update($request->all());

        return redirect('/company/jobs')->with('success', 'Job updated!');
    }

    // ---------------- DELETE JOB ---------------- //
    public function delete($id)
    {
        JobPosting::where('JobID', $id)->delete();
        return back()->with('success', 'Job deleted!');
    }

    // ---------------- API: GET SINGLE JOB (JSON) ---------------- //
    public function apiShow($id)
    {
        try {
            $companyId = session('user_id');
            $job = JobPosting::where('JobID', $id)
                ->where('CompanyID', $companyId)
                ->first();
            
            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'job' => $job
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading job: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------------- API: DELETE JOB (JSON) ---------------- //
    public function apiDelete($id)
    {
        try {
            $companyId = session('user_id');
            $job = JobPosting::where('JobID', $id)
                ->where('CompanyID', $companyId)
                ->first();
            
            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job not found or unauthorized'
                ], 404);
            }
            
            $job->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting job: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------------- API: UPDATE JOB (JSON) ---------------- //
    public function apiUpdate(Request $request, $id)
    {
        try {
            $companyId = session('user_id');
            $job = JobPosting::where('JobID', $id)
                ->where('CompanyID', $companyId)
                ->first();
            
            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job not found or unauthorized'
                ], 404);
            }
            
            // Update job fields
            $job->JobTitle = $request->jobTitle;
            $job->Department = $request->department;
            $job->JobDescription = $request->jobDescription;
            $job->Requirements = $request->requirements;
            $job->Responsibilities = $request->responsibilities;
            $job->Skills = $request->skills;
            $job->Location = $request->location;
            $job->JobType = $request->jobType;
            $job->SalaryMin = $request->salaryMin ? floatval($request->salaryMin) : null;
            $job->SalaryMax = $request->salaryMax ? floatval($request->salaryMax) : null;
            $job->Currency = $request->currency ?? 'USD';
            $job->ExperienceLevel = $request->experienceLevel ?? 'mid';
            $job->EducationLevel = $request->educationLevel ?? 'bachelor';
            $job->ClosingDate = $request->closingDate ?: null;
            
            $job->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Job updated successfully!',
                'jobId' => $job->JobID
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating job: ' . $e->getMessage()
            ], 500);
        }
    }
}
