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
            $table->decimal('estimated_hours', 8, 2)->nullable()->default(0)->after('priority');
        });

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->decimal('estimated_hours', 8, 2)->nullable()->default(0)->after('progress_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('estimated_hours');
        });

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn('estimated_hours');
        });
    }
};
