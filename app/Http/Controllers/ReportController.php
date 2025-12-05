<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\JobApplication;

class ReportController extends Controller
{
    // ---------------- ADMIN VIEW REPORTS ---------------- //
    public function index()
    {
        $reports = Report::latest()->get();
        return view('admin.reports.index', compact('reports'));
    }

    // Example: Generate simple system stats
    public function generateSystemReport()
    {
        $report = Report::create([
            'ReportType'  => 'System Overview',
            'Description' => 'Auto-generated system stats report',
        ]);

        return back()->with('success', 'Report generated!');
    }
}
