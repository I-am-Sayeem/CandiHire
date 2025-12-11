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
                    'CompanyLogo' => $job->company ? $job->company->Logo : null,
                    'JobTitle' => $job->JobTitle,
                    'JobDescription' => $job->JobDescription,
                    'Requirements' => $job->Requirements,
                    'Skills' => $job->Skills,
                    'Location' => $job->Location,
                    'JobType' => $job->JobType,
                    'SalaryMin' => $job->SalaryMin,
                    'SalaryMax' => $job->SalaryMax,
                    'ExperienceLevel' => $job->ExperienceLevel,
                    'PostedDate' => $job->created_at ? $job->created_at->diffForHumans() : 'Recently',
                    'Status' => $job->Status
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
        $jobs = JobPosting::where('CompanyID', $companyId)->latest()->get();

        return view('company.job_post', compact('jobs'));
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

        JobPosting::create([
            'CompanyID' => session('user_id'),
            'JobTitle' => $request->JobTitle,
            'JobDescription' => $request->JobDescription,
            'Requirements' => $request->Requirements,
            'Skills' => $request->Skills,
            'Location' => $request->Location,
            'JobType' => $request->JobType,
            'SalaryMin' => $request->SalaryMin,
            'SalaryMax' => $request->SalaryMax,
            'Status' => 'Active'
        ]);

        return redirect('/company/jobs')->with('success', 'Job posted!');
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
}
