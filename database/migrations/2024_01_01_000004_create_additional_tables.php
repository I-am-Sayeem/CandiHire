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
        // 18. INTERVIEWS TABLE
        Schema::create('interviews', function (Blueprint $table) {
            $table->id('InterviewID');
            $table->unsignedBigInteger('CandidateID');
            $table->unsignedBigInteger('CompanyID');
            $table->unsignedBigInteger('JobID')->nullable();
            $table->string('InterviewTitle', 255);
            $table->enum('InterviewType', ['technical', 'hr', 'behavioral', 'panel', 'final']);
            $table->enum('InterviewMode', ['virtual', 'onsite', 'phone']);
            $table->string('Platform', 100)->nullable();
            $table->date('ScheduledDate');
            $table->time('ScheduledTime');
            $table->integer('Duration')->default(60);
            $table->text('Location')->nullable();
            $table->string('InterviewerName', 255)->nullable();
            $table->string('InterviewerEmail', 255)->nullable();
            $table->string('InterviewerPhone', 20)->nullable();
            $table->enum('Status', ['scheduled', 'in-progress', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('Notes')->nullable();
            $table->text('Feedback')->nullable();
            $table->decimal('Rating', 3, 2)->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
            $table->foreign('CompanyID')->references('CompanyID')->on('companies')->onDelete('cascade');
            $table->foreign('JobID')->references('JobID')->on('job_postings')->onDelete('set null');
        });

        // 19. AI MATCHING RESULTS TABLE
        Schema::create('ai_matching_results', function (Blueprint $table) {
            $table->id('MatchID');
            $table->unsignedBigInteger('CandidateID');
            $table->unsignedBigInteger('JobID');
            $table->unsignedBigInteger('CompanyID');
            $table->decimal('MatchPercentage', 5, 2);
            $table->decimal('SkillsMatch', 5, 2)->nullable();
            $table->decimal('ExperienceMatch', 5, 2)->nullable();
            $table->decimal('EducationMatch', 5, 2)->nullable();
            $table->decimal('LocationMatch', 5, 2)->nullable();
            $table->decimal('SalaryMatch', 5, 2)->nullable();
            $table->text('MatchFactors')->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
            $table->foreign('JobID')->references('JobID')->on('job_postings')->onDelete('cascade');
            $table->foreign('CompanyID')->references('CompanyID')->on('companies')->onDelete('cascade');
            $table->unique(['CandidateID', 'JobID'], 'unique_match');
        });

        // 20. CONVERSATIONS TABLE
        Schema::create('conversations', function (Blueprint $table) {
            $table->id('ConversationID');
            $table->unsignedBigInteger('Participant1ID');
            $table->string('Participant1Type', 20);
            $table->unsignedBigInteger('Participant2ID');
            $table->string('Participant2Type', 20);
            $table->timestamp('LastMessageAt')->nullable();
            $table->timestamps();
        });

        // 21. MESSAGES TABLE
        Schema::create('messages', function (Blueprint $table) {
            $table->id('MessageID');
            $table->unsignedBigInteger('ConversationID');
            $table->unsignedBigInteger('SenderID');
            $table->string('SenderType', 20);
            $table->text('MessageText');
            $table->boolean('IsRead')->default(false);
            $table->timestamp('ReadAt')->nullable();
            $table->timestamps();
            $table->foreign('ConversationID')->references('ConversationID')->on('conversations')->onDelete('cascade');
        });

        // 22. NOTIFICATIONS TABLE
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('NotificationID');
            $table->unsignedBigInteger('UserID');
            $table->enum('UserType', ['candidate', 'company']);
            $table->string('Title', 255);
            $table->text('Message');
            $table->enum('Type', ['info', 'success', 'warning', 'error', 'interview', 'exam', 'application']);
            $table->boolean('IsRead')->default(false);
            $table->string('ActionUrl', 500)->nullable();
            $table->timestamp('ReadAt')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 23. COMPLAINTS/REPORTS TABLE
        Schema::create('complaints', function (Blueprint $table) {
            $table->id('ComplaintID');
            $table->unsignedBigInteger('ReporterID');
            $table->string('ReporterType', 20);
            $table->unsignedBigInteger('ReportedID');
            $table->string('ReportedType', 20);
            $table->unsignedBigInteger('JobID')->nullable();
            $table->string('Reason', 100);
            $table->text('Description')->nullable();
            $table->string('Contact', 255)->nullable();
            $table->enum('Status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->text('AdminNotes')->nullable();
            $table->timestamps();
        });

        // 24. CV PROCESSING RECORDS TABLE
        Schema::create('cv_processing_records', function (Blueprint $table) {
            $table->id('ProcessID');
            $table->unsignedBigInteger('CandidateID');
            $table->string('FileName', 255);
            $table->string('FilePath', 500);
            $table->enum('Status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('ExtractedData')->nullable();
            $table->text('ErrorMessage')->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
        });

        // 25. TRENDING SKILLS TABLE
        Schema::create('trending_skills', function (Blueprint $table) {
            $table->id('SkillID');
            $table->string('SkillName', 100);
            $table->string('Category', 50)->nullable();
            $table->integer('Demand')->default(0);
            $table->decimal('GrowthRate', 5, 2)->nullable();
            $table->timestamps();
        });

        // 26. SYSTEM SETTINGS TABLE
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id('SettingID');
            $table->string('SettingKey', 100)->unique();
            $table->text('SettingValue')->nullable();
            $table->text('Description')->nullable();
            $table->string('Category', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('trending_skills');
        Schema::dropIfExists('cv_processing_records');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('ai_matching_results');
        Schema::dropIfExists('interviews');
    }
};
