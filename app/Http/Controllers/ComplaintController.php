<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;

class ComplaintController extends Controller
{
    // ---------------- ADMIN VIEW COMPLAINTS ---------------- //
    public function index()
    {
        $complaints = Complaint::latest()->get();
        return view('admin.complaints', compact('complaints'));
    }

    // ---------------- USER SUBMITS COMPLAINT ---------------- //
    public function submit(Request $request)
    {
        Complaint::create([
            'UserID'   => session('user_id'),
            'UserType' => session('user_type'),
            'Subject'  => $request->Subject,
            'Message'  => $request->Message,
            'Status'   => 'Pending'
        ]);

        return back()->with('success', 'Complaint submitted!');
    }

    // ---------------- API: STORE JOB REPORT (JSON) ---------------- //
    public function storeJobReport(Request $request)
    {
        try {
            $userId = session('user_id');
            $userType = session('user_type') ?? 'candidate';
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to report'
                ], 401);
            }
            
            $jobId = $request->input('jobId');
            $companyId = $request->input('companyId');
            $reason = $request->input('reason');
            $details = $request->input('details');
            
            // Create the complaint/report
            Complaint::create([
                'UserID'   => $userId,
                'UserType' => $userType,
                'Subject'  => "Job Report: {$reason}",
                'Message'  => "Job ID: {$jobId}\nCompany ID: {$companyId}\nReason: {$reason}\nDetails: {$details}",
                'Status'   => 'Pending'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting report: ' . $e->getMessage()
            ], 500);
        }
    }
}
