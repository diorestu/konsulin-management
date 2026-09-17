<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectKanbanController;
use App\Http\Controllers\ProjectProgressController;
use App\Http\Controllers\ProjectThreatController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WebsiteContentController;
use App\Models\WebsiteContent;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome', ['contents' => WebsiteContent::where('is_published', true)->orderBy('sort_order')->get()->keyBy('key')]);
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/website-content', [WebsiteContentController::class, 'index'])->name('website-content.index');
    Route::put('/website-content/{websiteContent}', [WebsiteContentController::class, 'update'])->name('website-content.update');

    Route::get('/kanban', [ProjectKanbanController::class, 'index'])->name('kanban.index');
    Route::patch('/kanban/projects/{project}/status', [ProjectKanbanController::class, 'updateStatus'])->name('kanban.update-status');

    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('clients', ClientController::class);
    Route::resource('project-categories', ProjectCategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('staff', StaffController::class)->except(['create', 'show', 'edit']);

    Route::post('/projects/{project}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::patch('/projects/{project}/tasks/{task}/status', [ProjectController::class, 'updateTaskStatus'])->name('projects.tasks.update-status');
    Route::post('/projects/{project}/tasks/{task}/submit-review', [\App\Http\Controllers\TaskReviewController::class, 'submitForReview'])->name('projects.tasks.submit-review');
    Route::post('/projects/{project}/tasks/{task}/review', [\App\Http\Controllers\TaskReviewController::class, 'review'])->name('projects.tasks.review');
    Route::get('/projects/{project}/tasks/{task}/review-data', [\App\Http\Controllers\TaskReviewController::class, 'getReviewData'])->name('projects.tasks.review-data');
    Route::patch('/projects/{project}/tasks/{task}/checklists/{checklist}', [\App\Http\Controllers\TaskReviewController::class, 'toggleChecklist'])->name('projects.tasks.checklists.toggle');
    Route::post('/projects/{project}/tasks/{task}/checklists', [\App\Http\Controllers\TaskReviewController::class, 'addChecklist'])->name('projects.tasks.checklists.store');
    Route::post('/projects/{project}/progress', [ProjectProgressController::class, 'store'])->name('projects.progress.store');
    Route::post('/projects/{project}/threats', [ProjectThreatController::class, 'store'])->name('projects.threats.store');

    // Client Input Document Checklist & Vault
    Route::post('/projects/{project}/documents', [\App\Http\Controllers\ClientDocumentController::class, 'store'])->name('projects.documents.store');
    Route::patch('/projects/{project}/documents/{document}', [\App\Http\Controllers\ClientDocumentController::class, 'updateStatus'])->name('projects.documents.update-status');
    Route::post('/projects/{project}/documents/{document}/escalate', [\App\Http\Controllers\ClientDocumentController::class, 'escalateThreat'])->name('projects.documents.escalate');
    Route::post('/projects/{project}/documents/populate-defaults', [\App\Http\Controllers\ClientDocumentController::class, 'populateDefaults'])->name('projects.documents.populate-defaults');
    Route::delete('/projects/{project}/documents/{document}', [\App\Http\Controllers\ClientDocumentController::class, 'destroy'])->name('projects.documents.destroy');

    // Time Tracking & Desktop Floating Widget Endpoints
    Route::get('/time-logs/active', [\App\Http\Controllers\TaskTimeLogController::class, 'active'])->name('time-logs.active');
    Route::post('/time-logs/start', [\App\Http\Controllers\TaskTimeLogController::class, 'start'])->name('time-logs.start');
    Route::post('/time-logs/stop', [\App\Http\Controllers\TaskTimeLogController::class, 'stop'])->name('time-logs.stop');
    Route::get('/time-logs/my-tasks', [\App\Http\Controllers\TaskTimeLogController::class, 'myTasks'])->name('time-logs.my-tasks');
});
