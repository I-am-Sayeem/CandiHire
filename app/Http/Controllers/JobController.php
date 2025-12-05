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
        return view('jobs.index', compact('jobs'));
    }

    // ---------------- JOB DETAILS ---------------- //
    public function view($id)
    {
        $job = JobPosting::findOrFail($id);
        return view('jobs.view', compact('job'));
    }

    // ---------------- COMPANY JOB LIST ---------------- //
    public function companyJobs()
    {
        $companyId = session('user_id');
        $jobs = JobPosting::where('CompanyID', $companyId)->latest()->get();

        return view('company.jobs.index', compact('jobs'));
    }

    // ---------------- CREATE JOB FORM ---------------- //
    public function create()
    {
        return view('company.jobs.create');
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
        return view('company.jobs.edit', compact('job'));
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
