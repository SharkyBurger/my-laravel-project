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
        // specific for MySQL/MariaDB to modify ENUM
        DB::statement("ALTER TABLE leave_applications MODIFY COLUMN approval_status ENUM('pending', 'noted_by_academic_head', 'recommended_by_hr', 'approved_with_pay', 'approved_without_pay', 'rejected', 'cancelled') DEFAULT 'pending'");
        
        // Update auxiliary status columns if they are ENUMs (Assuming they are strings based on controller logic, but if ENUMs, use similar lines below)
        // If they are VARCHAR/String, no action needed.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE leave_applications MODIFY COLUMN approval_status ENUM('pending', 'noted_by_academic_head', 'recommended_by_hr', 'approved_with_pay', 'approved_without_pay', 'rejected') DEFAULT 'pending'");
    }
};