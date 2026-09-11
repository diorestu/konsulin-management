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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('project_staff', function (Blueprint $table) {
            if (!Schema::hasColumn('project_staff', 'role')) {
                $table->string('role', 50)->nullable()->after('staff_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_staff', function (Blueprint $table) {
            if (Schema::hasColumn('project_staff', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'reviewer_id')) {
                $table->dropConstrainedForeignId('reviewer_id');
            }
        });
    }
};
