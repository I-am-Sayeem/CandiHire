<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Admin;

class AuthController extends Controller
{
    // ----- SHOW LOGIN PAGE -----
    public function showLogin() {
        return view('auth.login');
    }

    // ----- LOGIN HANDLER -----
    public function login(Request $request) {

        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        // Candidate login
        $candidate = Candidate::where('Email', $request->email)->first();
        if ($candidate && Hash::check($request->password, $candidate->Password)) {
            session(['user_type' => 'candidate', 'user_id' => $candidate->CandidateID]);
            return redirect('/candidate/dashboard');
        }

        // Company login
        $company = Company::where('Email', $request->email)->first();
        if ($company && Hash::check($request->password, $company->Password)) {
            session(['user_type' => 'company', 'user_id' => $company->CompanyID]);
            return redirect('/company/dashboard');
        }

        // Admin login
        $admin = Admin::where('email', $request->email)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {
            session(['user_type' => 'admin', 'user_id' => $admin->id]);
            return redirect('/admin/dashboard');
        }

        return back()->with('error', 'Invalid email or password');
    }

    // ----- LOGOUT -----
    public function logout() {
        session()->flush();
        return redirect('/login');
    }

    // ----- CANDIDATE REGISTER -----
    public function showCandidateRegister() {
        return view('auth.candidate_register');
    }

    public function registerCandidate(Request $request) {

        $request->validate([
            'FullName' => 'required',
            'Email' => 'required|email',
            'Password' => 'required|min:6'
        ]);

        Candidate::create([
            'FullName' => $request->FullName,
            'Email' => $request->Email,
            'Password' => Hash::make($request->Password),
            'IsActive' => 1
        ]);

        return redirect('/login')->with('success', 'Registration successful!');
    }

    // ----- COMPANY REGISTER -----
    public function showCompanyRegister() {
        return view('auth.company_register');
    }

    public function registerCompany(Request $request) {

        $request->validate([
            'CompanyName' => 'required',
            'Email' => 'required|email',
            'Password' => 'required|min:6'
        ]);

        Company::create([
            'CompanyName' => $request->CompanyName,
            'Email' => $request->Email,
            'Password' => Hash::make($request->Password),
            'IsActive' => 1
        ]);

        return redirect('/login')->with('success', 'Company registered!');
    }
}
