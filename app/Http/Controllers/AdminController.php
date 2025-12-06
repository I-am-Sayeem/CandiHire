<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Complaint;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function dashboard() {
        return view('admin.dashboard');
    }

    /**
     * Show the user management page.
     */
    public function users() {
        $candidates = Candidate::latest()->get();
        $companies = Company::latest()->get();
        return view('admin.users', compact('candidates', 'companies'));
    }

    /**
     * Delete a user (candidate or company).
     */
    public function deleteUser(Request $request) {
        if ($request->user_type === 'candidate') {
            Candidate::where('CandidateID', $request->user_id)->delete();
        } else {
            Company::where('CompanyID', $request->user_id)->delete();
        }
        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Show the job management page.
     */
    public function jobs() {
        $jobs = JobPosting::with('company')->latest()->get();
        return view('admin.jobs', compact('jobs'));
    }

    /**
     * Delete a job posting.
     */
    public function deleteJob(Request $request) {
        JobPosting::where('JobID', $request->job_id)->delete();
        return back()->with('success', 'Job deleted successfully!');
    }

    /**
     * Show the reports page.
     */
    public function reports() {
        return view('admin.reports');
    }

    /**
     * Show the settings page.
     */
    public function settings() {
        return view('admin.settings');
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request) {
        // Handle settings update logic here
        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Reset settings to defaults.
     */
    public function resetSettings() {
        // Handle settings reset logic here
        return back()->with('success', 'Settings reset to defaults!');
    }

    /**
     * Show the complaints page.
     */
    public function complaints() {
        $complaints = Complaint::latest()->get();
        return view('admin.complaints', compact('complaints'));
    }

    /**
     * Update a complaint status/reply.
     */
    public function updateComplaint(Request $request) {
        $complaint = Complaint::find($request->complaint_id);
        if ($complaint) {
            $complaint->update([
                'Status' => $request->status ?? $complaint->Status,
                'AdminReply' => $request->admin_reply ?? $complaint->AdminReply,
            ]);
        }
        return back()->with('success', 'Complaint updated successfully!');
    }
}
