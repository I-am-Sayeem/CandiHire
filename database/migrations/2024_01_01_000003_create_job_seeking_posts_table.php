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
        if (!Schema::hasTable('job_seeking_posts')) {
            Schema::create('job_seeking_posts', function (Blueprint $table) {
                $table->id('PostID');
                $table->unsignedBigInteger('CandidateID');
                $table->string('JobTitle', 255);
                $table->text('CareerGoal');
                $table->text('KeySkills');
                $table->text('Experience')->nullable();
                $table->text('Education');
                $table->text('SoftSkills')->nullable();
                $table->text('ValueToEmployer')->nullable();
                $table->text('ContactInfo');
                $table->string('Status', 50)->default('active');
                $table->integer('Views')->default(0);
                $table->integer('Applications')->default(0);
                $table->timestamp('CreatedAt')->useCurrent();
                $table->timestamp('UpdatedAt')->nullable();
                
                $table->index('CandidateID');
                $table->index('Status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seeking_posts');
    }
};
