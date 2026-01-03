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
        $candidate = Candidate::find($candidateId);

        $applications = JobApplication::where('CandidateID', $candidateId)->latest()->get();
        $scheduledExams = ExamSchedule::where('CandidateID', $candidateId)->latest()->get();
        $interviews = Interview::where('CandidateID', $candidateId)->latest()->get();

        // Variables expected by the view
        $candidateName = $candidate->FullName ?? 'User';
        $candidateProfilePicture = $candidate->ProfilePicture ?? null;
        $sessionCandidateId = $candidateId;

        return view('candidate.dashboard', compact(
            'applications', 'scheduledExams', 'interviews',
            'candidateName', 'candidateProfilePicture', 'sessionCandidateId'
        ));
    }

    // ---------------- COMPANY DASHBOARD ---------------- //
    public function company()
    {
        $companyId = session('user_id');
        $company = Company::find($companyId);

        $jobs = JobPosting::where('CompanyID', $companyId)->latest()->get();
        $applications = JobApplication::whereHas('job', function ($q) use ($companyId) {
            $q->where('CompanyID', $companyId);
        })->latest()->get();
        $interviews = Interview::where('CompanyID', $companyId)->latest()->get();

        // Variables expected by the view
        $companyName = $company->CompanyName ?? session('company_name', 'Company');
        $companyLogo = $company && $company->Logo ? asset($company->Logo) : null;
        $sessionCompanyId = $companyId;

        return view('company.dashboard', compact(
            'jobs', 'applications', 'interviews',
            'companyName', 'companyLogo', 'companyId'
        ));
    }

    // ---------------- ADMIN DASHBOARD ---------------- //
    public function admin()
    {
        $stats = [
            'candidates' => Candidate::count(),
            'companies' => Company::count(),
            'job_posts' => JobPosting::count(),
            'active_jobs' => JobPosting::where('Status', 'Active')->count(),
            'applications' => JobApplication::count(),
            'exams' => \App\Models\Exam::count(),
            'exam_assignments' => ExamSchedule::count(),
            'completed_exams' => \App\Models\ExamAttempt::where('Status', 'Completed')->count(),
        ];

        $recentActivity = [];

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }
}
