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
        return view('admin.complaints.index', compact('complaints'));
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
}
