<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the Status enum to include 'Pending' for mandatory exam workflow
        DB::statement("ALTER TABLE job_postings MODIFY COLUMN Status ENUM('Active', 'Closed', 'Draft', 'Paused', 'Pending') DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE job_postings MODIFY COLUMN Status ENUM('Active', 'Closed', 'Draft', 'Paused') DEFAULT 'Active'");
    }
};
