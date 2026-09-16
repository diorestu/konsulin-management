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
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('due_date');
            $table->string('review_status', 30)->default('none')->after('status'); // none, pending, approved, revision_requested
            $table->text('review_notes')->nullable()->after('notes');
        });

        Schema::create('task_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_checked')->default(false);
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index(['project_task_id', 'order']);
        });

        Schema::create('task_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 30); // approved, revision_requested
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_task_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reviews');
        Schema::dropIfExists('task_checklists');

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at', 'review_status', 'review_notes']);
        });
    }
};
