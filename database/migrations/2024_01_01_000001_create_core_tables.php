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
        // 1. CANDIDATES TABLE
        Schema::create('candidates', function (Blueprint $table) {
            $table->id('CandidateID');
            $table->string('FullName', 255);
            $table->string('Email', 255)->unique();
            $table->string('PhoneNumber', 20);
            $table->enum('WorkType', ['full-time', 'part-time', 'contract', 'freelance', 'internship', 'fresher']);
            $table->text('Skills')->nullable();
            $table->string('Password', 255);
            $table->boolean('IsActive')->default(true);
            $table->string('ProfilePicture', 500)->nullable();
            $table->string('Location', 255)->nullable();
            $table->text('Summary')->nullable();
            $table->string('LinkedIn', 500)->nullable();
            $table->string('GitHub', 500)->nullable();
            $table->string('Portfolio', 500)->nullable();
            $table->integer('YearsOfExperience')->default(0);
            $table->timestamps();
        });

        // 2. COMPANIES TABLE
        Schema::create('companies', function (Blueprint $table) {
            $table->id('CompanyID');
            $table->string('CompanyName', 255);
            $table->string('Industry', 100)->nullable();
            $table->enum('CompanySize', ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'])->nullable();
            $table->string('Email', 255)->unique();
            $table->string('PhoneNumber', 20)->nullable();
            $table->text('CompanyDescription')->nullable();
            $table->string('Password', 255);
            $table->boolean('IsActive')->default(true);
            $table->string('Website', 500)->nullable();
            $table->string('Logo', 500)->nullable();
            $table->text('Address')->nullable();
            $table->string('City', 100)->nullable();
            $table->string('State', 100)->nullable();
            $table->string('Country', 100)->nullable();
            $table->string('PostalCode', 20)->nullable();
            $table->timestamps();
        });

        // 3. ADMINS TABLE
        Schema::create('admins', function (Blueprint $table) {
            $table->id('AdminID');
            $table->string('Username', 100)->unique();
            $table->string('Email', 255)->unique();
            $table->string('Password', 255);
            $table->string('FullName', 255)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->timestamps();
        });

        // 4. CANDIDATE CV DATA TABLE
        Schema::create('candidate_cv_data', function (Blueprint $table) {
            $table->id('CvID');
            $table->unsignedBigInteger('CandidateID');
            $table->string('FirstName', 100)->nullable();
            $table->string('LastName', 100)->nullable();
            $table->string('Email', 255)->nullable();
            $table->string('Phone', 20)->nullable();
            $table->text('Address')->nullable();
            $table->text('Summary')->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
        });

        // 5. CANDIDATE EXPERIENCE TABLE
        Schema::create('candidate_experience', function (Blueprint $table) {
            $table->id('ExperienceID');
            $table->unsignedBigInteger('CandidateID');
            $table->string('JobTitle', 255)->nullable();
            $table->string('Company', 255)->nullable();
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->text('Description')->nullable();
            $table->string('Location', 255)->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
        });

        // 6. CANDIDATE EDUCATION TABLE
        Schema::create('candidate_education', function (Blueprint $table) {
            $table->id('EducationID');
            $table->unsignedBigInteger('CandidateID');
            $table->string('Degree', 255)->nullable();
            $table->string('Institution', 255)->nullable();
            $table->year('StartYear')->nullable();
            $table->year('EndYear')->nullable();
            $table->decimal('GPA', 3, 2)->nullable();
            $table->string('Location', 255)->nullable();
            $table->text('Coursework')->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
        });

        // 7. CANDIDATE SKILLS TABLE
        Schema::create('candidate_skills', function (Blueprint $table) {
            $table->id('SkillID');
            $table->unsignedBigInteger('CandidateID');
            $table->text('ProgrammingLanguages')->nullable();
            $table->text('Frameworks')->nullable();
            $table->text('Databases')->nullable();
            $table->text('Tools')->nullable();
            $table->text('SoftSkills')->nullable();
            $table->text('Languages')->nullable();
            $table->text('Certifications')->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
        });

        // 8. CANDIDATE PROJECTS TABLE
        Schema::create('candidate_projects', function (Blueprint $table) {
            $table->id('ProjectID');
            $table->unsignedBigInteger('CandidateID');
            $table->string('ProjectName', 255)->nullable();
            $table->string('Role', 255)->nullable();
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->text('Description')->nullable();
            $table->text('Technologies')->nullable();
            $table->string('ProjectUrl', 500)->nullable();
            $table->timestamps();
            $table->foreign('CandidateID')->references('CandidateID')->on('candidates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_projects');
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_education');
        Schema::dropIfExists('candidate_experience');
        Schema::dropIfExists('candidate_cv_data');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('candidates');
    }
};
