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
        return view('auth.login_signup');
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
        return view('candidate.register');
    }

    public function registerCandidate(Request $request) {

        $request->validate([
            'FullName' => 'required',
            'Email' => 'required|email|unique:candidates,Email',
            'Password' => 'required|min:6|confirmed'
        ]);

        Candidate::create([
            'FullName' => $request->FullName,
            'Email' => $request->Email,
            'Password' => Hash::make($request->Password),
            'PhoneNumber' => $request->PhoneNumber ?? null,
            'WorkType' => $request->WorkType ?? null,
            'Skills' => $request->Skills ?? null,
            'IsActive' => 1
        ]);

        return redirect('/login')->with('success', 'Registration successful! Please login.');
    }

    // ----- COMPANY REGISTER -----
    public function showCompanyRegister() {
        return view('auth.login_signup');
    }

    public function registerCompany(Request $request) {

        $request->validate([
            'CompanyName' => 'required',
            'Email' => 'required|email|unique:companies,Email',
            'Password' => 'required|min:6|confirmed'
        ]);

        Company::create([
            'CompanyName' => $request->CompanyName,
            'Email' => $request->Email,
            'Password' => Hash::make($request->Password),
            'PhoneNumber' => $request->PhoneNumber ?? null,
            'Industry' => $request->Industry ?? null,
            'CompanySize' => $request->CompanySize ?? null,
            'Description' => $request->Description ?? null,
            'IsActive' => 1
        ]);

        return redirect('/login')->with('success', 'Company registered! Please login.');
    }
}
