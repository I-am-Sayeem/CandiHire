<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SettingsController;

// ---------------- AUTH ---------------- //
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/register/candidate', [AuthController::class, 'showCandidateRegister']);
Route::post('/register/candidate', [AuthController::class, 'registerCandidate']);

Route::get('/register/company', [AuthController::class, 'showCompanyRegister']);
Route::post('/register/company', [AuthController::class, 'registerCompany']);

// ---------------- DASHBOARD ---------------- //
Route::get('/candidate/dashboard', [DashboardController::class, 'candidate']);
Route::get('/company/dashboard', [DashboardController::class, 'company']);
Route::get('/admin/dashboard', [DashboardController::class, 'admin']);

// ---------------- JOB SYSTEM ---------------- //
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{id}', [JobController::class, 'view']);
Route::post('/jobs/apply/{id}', [ApplicationController::class, 'apply']);

// ---------------- COMPANY JOB MANAGEMENT ---------------- //
Route::get('/company/jobs', [JobController::class, 'companyJobs']);
Route::get('/company/job/create', [JobController::class, 'create']);
Route::post('/company/job/create', [JobController::class, 'store']);
Route::get('/company/job/edit/{id}', [JobController::class, 'edit']);
Route::post('/company/job/update/{id}', [JobController::class, 'update']);
Route::get('/company/job/delete/{id}', [JobController::class, 'delete']);

// ---------------- EXAM SYSTEM ---------------- //
Route::get('/exam/{scheduleId}', [ExamController::class, 'takeExam']);
Route::post('/exam/{scheduleId}/submit', [ExamController::class, 'submitExam']);

Route::get('/company/exams', [ExamController::class, 'index']);
Route::get('/company/exams/create', [ExamController::class, 'create']);
Route::post('/company/exams/create', [ExamController::class, 'store']);

// ---------------- MESSAGING ---------------- //
Route::get('/messages', [MessagingController::class, 'index']);
Route::get('/messages/{conversationId}', [MessagingController::class, 'open']);
Route::post('/messages/send', [MessagingController::class, 'send']);

// ---------------- INTERVIEW ---------------- //
Route::get('/company/interviews', [InterviewController::class, 'index']);
Route::post('/company/interviews/schedule', [InterviewController::class, 'schedule']);

// ---------------- NOTIFICATIONS ---------------- //
Route::get('/notifications', [NotificationController::class, 'index']);

// ---------------- REPORTS & COMPLAINTS ---------------- //
Route::get('/admin/reports', [ReportController::class, 'index']);
Route::get('/admin/complaints', [ComplaintController::class, 'index']);
