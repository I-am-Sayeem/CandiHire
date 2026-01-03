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
use App\Http\Controllers\JobSeekingController;
use App\Http\Controllers\CandidateProfileController;

// ============================================================
//                        AUTH ROUTES
// ============================================================
Route::get('/', fn() => view('welcome'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', fn() => redirect('/login'))->name('register');
Route::get('/register/candidate', [AuthController::class, 'showCandidateRegister']);
Route::post('/register/candidate', [AuthController::class, 'registerCandidate']);

Route::get('/register/company', [AuthController::class, 'showCompanyRegister']);
Route::post('/register/company', [AuthController::class, 'registerCompany']);

// Admin Login
Route::get('/admin/login', fn() => view('auth.admin_login'))->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);

// ============================================================
//                     CANDIDATE ROUTES
// ============================================================
Route::prefix('candidate')->name('candidate.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'candidate'])->name('dashboard');
    Route::get('/profile', [CandidateController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [CandidateController::class, 'updateProfile'])->name('profile.update');
    Route::get('/applications', [ApplicationController::class, 'candidateStatus'])->name('applications');
    Route::get('/messages', [MessagingController::class, 'index'])->name('messages');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/candidate/{id}', [CandidateController::class, 'details'])->name('candidate.details');

// ============================================================
//                      COMPANY ROUTES
// ============================================================
Route::prefix('company')->name('company.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'company'])->name('dashboard');
    
    // Job Management
    Route::get('/jobs', [JobController::class, 'companyJobs'])->name('jobs');
    Route::get('/job/create', [JobController::class, 'create'])->name('job.create');
    Route::post('/job/create', [JobController::class, 'store'])->name('job.store');
    Route::get('/job/edit/{id}', [JobController::class, 'edit'])->name('job.edit');
    Route::post('/job/update/{id}', [JobController::class, 'update'])->name('job.update');
    Route::get('/job/delete/{id}', [JobController::class, 'delete'])->name('job.delete');
    Route::get('/job-post', [CompanyController::class, 'jobPost'])->name('job-post');
    
    // Applications
    Route::get('/applications', [CompanyController::class, 'applications'])->name('applications');
    Route::get('/applications/{jobId}', [ApplicationController::class, 'jobApplicants'])->name('applications.job');
    
    // Exams
    Route::get('/exams', [ExamController::class, 'index'])->name('exams');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams/create', [ExamController::class, 'store'])->name('exams.store');
    Route::post('/exams/assign', [ExamController::class, 'assign'])->name('exams.assign');
    Route::get('/exams/assignments/{jobId}', [ExamController::class, 'jobExamAssignments'])->name('exams.assignments');
    Route::get('/exams/manual-creation', [ExamController::class, 'manualCreation'])->name('exams.manual-creation');
    Route::get('/exams/auto-creation', [ExamController::class, 'autoCreation'])->name('exams.auto-creation');
    Route::post('/exams/manual-store', [ExamController::class, 'storeManual'])->name('exams.manual-store');
    Route::post('/exams/auto-generate', [ExamController::class, 'storeAuto'])->name('exams.auto-generate');
    Route::get('/exams/{id}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::post('/exams/{id}/update', [ExamController::class, 'update'])->name('exams.update');
    Route::get('/mcq-results', [CompanyController::class, 'mcqResults'])->name('mcq-results');
    
    // Interviews
    Route::get('/interviews', [InterviewController::class, 'index'])->name('interviews');
    Route::post('/interviews/schedule', [InterviewController::class, 'schedule'])->name('interviews.schedule');
    
    // AI Matching
    Route::get('/ai-matching', [CompanyController::class, 'aiMatching'])->name('ai-matching');
    
    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ============================================================
//                       ADMIN ROUTES
// ============================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/jobs', [AdminController::class, 'jobs'])->name('jobs');
    Route::post('/jobs/delete', [AdminController::class, 'deleteJob'])->name('jobs.delete');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/reset', [AdminController::class, 'resetSettings'])->name('settings.reset');
    Route::get('/complaints', [AdminController::class, 'complaints'])->name('complaints');
    Route::post('/complaints/update', [AdminController::class, 'updateComplaint'])->name('complaints.update');
    
    // Report generation
    Route::post('/reports/generate', [ReportController::class, 'generateSystemReport'])->name('reports.generate');
    
    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ============================================================
//                       JOB ROUTES (PUBLIC)
// ============================================================
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{id}', [JobController::class, 'view'])->name('jobs.view');
Route::post('/jobs/apply/{id}', [ApplicationController::class, 'apply'])->name('jobs.apply');

// ============================================================
//                       EXAM ROUTES
// ============================================================
Route::get('/exam/attend', [ExamController::class, 'candidateExams'])->name('exam.attend');
Route::get('/exam/{scheduleId}', [ExamController::class, 'takeExam'])->name('exam.take');
Route::post('/exam/{scheduleId}/submit', [ExamController::class, 'submitExam'])->name('exam.submit');

// ============================================================
//                    INTERVIEW ROUTES
// ============================================================
Route::get('/interview/schedule', [InterviewController::class, 'candidateSchedule'])->name('interview.schedule');

// ============================================================
//                       CV ROUTES
// ============================================================
Route::prefix('cv')->name('cv.')->group(function () {
    Route::get('/builder', [CvController::class, 'builder'])->name('builder');
    Route::get('/checker', [CvController::class, 'checker'])->name('checker');
    Route::get('/processing', [CvController::class, 'processing'])->name('processing');
});

// ============================================================
//                     MESSAGING ROUTES
// ============================================================
Route::get('/messages', [MessagingController::class, 'index'])->name('messages.index');
Route::get('/messages/{conversationId}', [MessagingController::class, 'open'])->name('messages.open');
Route::post('/messages/send', [MessagingController::class, 'send'])->name('messages.send');
Route::post('/api/messages/send', [MessagingController::class, 'apiSendMessage'])->name('api.messages.send');
Route::get('/api/messages/unread-count', [MessagingController::class, 'apiGetUnreadCount'])->name('api.messages.unread-count');
Route::get('/api/conversations', [MessagingController::class, 'apiListConversations'])->name('api.conversations.index');
Route::get('/api/messages/{conversationId}', [MessagingController::class, 'apiGetMessages'])->name('api.messages.show');
Route::post('/api/typing/set', [MessagingController::class, 'apiSetTypingStatus'])->name('api.typing.set');
Route::get('/api/typing/{conversationId}', [MessagingController::class, 'apiGetTypingStatus'])->name('api.typing.get');


// ============================================================
//                   NOTIFICATION ROUTES
// ============================================================
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

// ============================================================
//                    COMPLAINT ROUTES
// ============================================================
Route::post('/complaints/submit', [ComplaintController::class, 'submit'])->name('complaints.submit');

// ============================================================
//                   JOB SEEKING API ROUTES
// ============================================================
Route::get('/api/job-seeking', [JobSeekingController::class, 'index'])->name('api.job-seeking.index');
Route::post('/api/job-seeking', [JobSeekingController::class, 'store'])->name('api.job-seeking.store');

// ============================================================
//                 CANDIDATE PROFILE API ROUTES
// ============================================================
Route::get('/api/candidate-profile', [CandidateProfileController::class, 'show'])->name('api.candidate-profile.show');
Route::post('/api/candidate-profile', [CandidateProfileController::class, 'update'])->name('api.candidate-profile.update');

// ============================================================
//                    JOB POSTS API ROUTES
// ============================================================
Route::get('/api/job-posts', [JobController::class, 'apiIndex'])->name('api.job-posts.index');
Route::post('/api/company/job/store', [JobController::class, 'apiStore'])->name('api.company.job.store');
Route::get('/api/company/job/{id}', [JobController::class, 'apiShow'])->name('api.company.job.show');
Route::post('/api/company/job/delete/{id}', [JobController::class, 'apiDelete'])->name('api.company.job.delete');
Route::post('/api/company/job/update/{id}', [JobController::class, 'apiUpdate'])->name('api.company.job.update');
Route::post('/api/job-applications', [ApplicationController::class, 'store'])->name('api.job-applications.store');
Route::get('/api/company-profile/{id}', [CompanyController::class, 'apiShow'])->name('api.company-profile.show');
Route::post('/api/company-profile/update', [CompanyController::class, 'apiUpdateProfile'])->name('api.company-profile.update');
Route::post('/api/job-reports', [ComplaintController::class, 'storeJobReport'])->name('api.job-reports.store');
Route::get('/api/job-seeking-posts', [JobSeekingController::class, 'index'])->name('api.job-seeking-posts.index');

// ============================================================
//                   INTERVIEW API ROUTES
// ============================================================
Route::get('/api/company/job-positions', [InterviewController::class, 'apiGetJobPositions'])->name('api.company.job-positions');
Route::get('/api/company/candidates/{jobId}', [InterviewController::class, 'apiGetCandidates'])->name('api.company.candidates');
Route::post('/api/company/interviews/schedule', [InterviewController::class, 'apiSchedule'])->name('api.company.interviews.schedule');

// ============================================================
//                   CV PROCESSING API ROUTES
// ============================================================
Route::post('/api/cv/upload', [App\Http\Controllers\CvProcessingController::class, 'upload'])->name('api.cv.upload');
Route::post('/api/cv/process', [App\Http\Controllers\CvProcessingController::class, 'process'])->name('api.cv.process');
Route::post('/api/cv/filter', [App\Http\Controllers\CvProcessingController::class, 'filter'])->name('api.cv.filter');
