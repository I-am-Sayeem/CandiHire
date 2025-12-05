<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\ExamSchedule;
use App\Models\Interview;

class DashboardController extends Controller
{
    // ---------------- CANDIDATE DASHBOARD ---------------- //
    public function candidate()
    {
        $candidateId = session('user_id');

        $applications = JobApplication::where('CandidateID', $candidateId)->latest()->get();
        $scheduledExams = ExamSchedule::where('CandidateID', $candidateId)->latest()->get();
        $interviews = Interview::where('CandidateID', $candidateId)->latest()->get();

        return view('dashboard.candidate', compact('applications', 'scheduledExams', 'interviews'));
    }

    // ---------------- COMPANY DASHBOARD ---------------- //
    public function company()
    {
        $companyId = session('user_id');

        $jobs = JobPosting::where('CompanyID', $companyId)->latest()->get();
        $applications = JobApplication::whereHas('job', function ($q) use ($companyId) {
            $q->where('CompanyID', $companyId);
        })->latest()->get();
        $interviews = Interview::where('CompanyID', $companyId)->latest()->get();

        return view('dashboard.company', compact('jobs', 'applications', 'interviews'));
    }

    // ---------------- ADMIN DASHBOARD ---------------- //
    public function admin()
    {
        $totalCandidates = Candidate::count();
        $totalCompanies  = Company::count();
        $totalJobs       = JobPosting::count();
        $totalApps       = JobApplication::count();

        return view('dashboard.admin', compact('totalCandidates', 'totalCompanies', 'totalJobs', 'totalApps'));
    }
}
