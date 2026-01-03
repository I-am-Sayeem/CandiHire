<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 9. JOB POSTINGS TABLE
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id('JobID');
            $table->unsignedBigInteger('CompanyID');
            $table->string('JobTitle', 255);
            $table->string('Department', 100)->nullable();
            $table->text('JobDescription')->nullable();
            $table->text('Requirements')->nullable();
            $table->text('Responsibilities')->nullable();
            $table->text('Skills')->nullable();
            $table->string('Location', 255)->nullable();
            $table->enum('JobType', ['full-time', 'part-time', 'contract', 'freelance', 'internship'])->default('full-time');
            $table->decimal('SalaryMin', 10, 2)->nullable();
            $table->decimal('SalaryMax', 10, 2)->nullable();
            $table->string('Currency', 3)->default('USD');
            $table->enum('ExperienceLevel', ['entry', 'mid', 'senior', 'lead', 'executive'])->nullable();
            $table->enum('EducationLevel', ['high-school', 'associate', 'bachelor', 'master', 'phd'])->nullable();
            $table->enum('Status', ['Active', 'Closed', 'Draft', 'Paused'])->default('Active');
            $table->timestamp('PostedDate')->useCurrent();
            $table->date('ClosingDate')->nullable();
            $table->integer('ApplicationCount')->default(0);
            $table->timestamps();
            $table->foreign('CompanyID')->references('CompanyID')->on('companies')->onDelete('cascade');
        });

        // 10. JOB APPLICATIONS TABLE
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id('ApplicationID');
            $table->unsignedBigInteger('CandidateID');
            $table->unsignedBigInteger('JobID');
            $table->timestamp('ApplicationDate')->useCurrent();
            $table->enum('Status', ['submitted', 'under-review', 'shortlisted', 'interview-scheduled', 'interviewed', 'offer-extended', 'accepted', 'rejected', 'withdrawn'])->default('submitted');
            $table->text('CoverLetter')->nullable();
            $table->string('ResumePath', 500)->nullable();
            $table->text('Notes')->nullable();
            $table->string('ContactPerson', 255)->nullable();
            $table->string('ContactEmail', 255)->nullable();
            $table->decimal('SalaryExpectation', 10, 2)->nullable();
            $table->date('AvailabilityDate')->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
            $table->foreign('JobID')->references('JobID')->on('job_postings')->onDelete('cascade');
            $table->unique(['CandidateID', 'JobID'], 'unique_application');
        });

        // 11. APPLICATION STATUS HISTORY TABLE
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id('StatusHistoryID');
            $table->unsignedBigInteger('ApplicationID');
            $table->string('Status', 50);
            $table->timestamp('StatusDate')->useCurrent();
            $table->text('Notes')->nullable();
            $table->string('UpdatedBy', 100)->nullable();
            $table->foreign('ApplicationID')->references('ApplicationID')->on('job_applications')->onDelete('cascade');
        });

        // 12. EXAMS TABLE
        Schema::create('exams', function (Blueprint $table) {
            $table->id('ExamID');
            $table->unsignedBigInteger('CompanyID');
            $table->string('ExamTitle', 255);
            $table->enum('ExamType', ['auto-generated', 'manual', 'mcq', 'coding', 'mixed']);
            $table->text('Description')->nullable();
            $table->text('Instructions')->nullable();
            $table->integer('Duration');
            $table->integer('QuestionCount')->default(0);
            $table->decimal('PassingScore', 5, 2)->default(70.00);
            $table->integer('MaxAttempts')->default(1);
            $table->boolean('IsActive')->default(true);
            $table->string('CreatedBy', 100)->nullable();
            $table->timestamps();
            $table->foreign('CompanyID')->references('CompanyID')->on('companies')->onDelete('cascade');
        });

        // 13. EXAM QUESTIONS TABLE
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id('QuestionID');
            $table->unsignedBigInteger('ExamID');
            $table->enum('QuestionType', ['multiple-choice', 'true-false', 'coding', 'essay', 'fill-blank']);
            $table->text('QuestionText');
            $table->integer('QuestionOrder');
            $table->decimal('Points', 5, 2)->default(1.00);
            $table->enum('Difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('Category', 100)->nullable();
            $table->text('Tags')->nullable();
            $table->timestamps();
            $table->foreign('ExamID')->references('ExamID')->on('exams')->onDelete('cascade');
        });

        // 14. EXAM QUESTION OPTIONS TABLE
        Schema::create('exam_question_options', function (Blueprint $table) {
            $table->id('OptionID');
            $table->unsignedBigInteger('QuestionID');
            $table->text('OptionText');
            $table->boolean('IsCorrect')->default(false);
            $table->integer('OptionOrder');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('QuestionID')->references('QuestionID')->on('exam_questions')->onDelete('cascade');
        });

        // 15. EXAM SCHEDULES TABLE
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id('ScheduleID');
            $table->unsignedBigInteger('ExamID');
            $table->unsignedBigInteger('CandidateID')->nullable();
            $table->unsignedBigInteger('JobID')->nullable();
            $table->date('ScheduledDate');
            $table->time('ScheduledTime');
            $table->enum('Status', ['scheduled', 'in-progress', 'completed', 'cancelled', 'expired'])->default('scheduled');
            $table->integer('Duration')->nullable();
            $table->integer('AttemptsUsed')->default(0);
            $table->integer('MaxAttempts')->default(1);
            $table->timestamps();
            $table->foreign('ExamID')->references('ExamID')->on('exams')->onDelete('cascade');
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
            $table->foreign('JobID')->references('JobID')->on('job_postings')->onDelete('cascade');
        });

        // 16. EXAM ATTEMPTS TABLE
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id('AttemptID');
            $table->unsignedBigInteger('ScheduleID');
            $table->unsignedBigInteger('CandidateID');
            $table->unsignedBigInteger('ExamID');
            $table->timestamp('StartTime')->useCurrent();
            $table->timestamp('EndTime')->nullable();
            $table->enum('Status', ['in-progress', 'completed', 'abandoned', 'timeout'])->default('in-progress');
            $table->decimal('Score', 5, 2)->nullable();
            $table->integer('TotalQuestions')->default(0);
            $table->integer('CorrectAnswers')->default(0);
            $table->integer('TimeSpent')->nullable();
            $table->timestamps();
            $table->foreign('ScheduleID')->references('ScheduleID')->on('exam_schedules')->onDelete('cascade');
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
            $table->foreign('ExamID')->references('ExamID')->on('exams')->onDelete('cascade');
        });

        // 17. EXAM ANSWERS TABLE
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id('AnswerID');
            $table->unsignedBigInteger('AttemptID');
            $table->unsignedBigInteger('QuestionID');
            $table->text('AnswerText')->nullable();
            $table->unsignedBigInteger('SelectedOptionID')->nullable();
            $table->boolean('IsCorrect')->nullable();
            $table->decimal('PointsEarned', 5, 2)->default(0.00);
            $table->integer('TimeSpent')->nullable();
            $table->timestamps();
            $table->foreign('AttemptID')->references('AttemptID')->on('exam_attempts')->onDelete('cascade');
            $table->foreign('QuestionID')->references('QuestionID')->on('exam_questions')->onDelete('cascade');
            $table->foreign('SelectedOptionID')->references('OptionID')->on('exam_question_options')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exam_question_options');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
    }
};
