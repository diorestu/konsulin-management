<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
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

    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::resource('clients', ClientController::class);
    Route::resource('project-categories', ProjectCategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('staff', StaffController::class)->except(['create', 'show', 'edit']);

    Route::post('/projects/{project}/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');
    Route::patch('/projects/{project}/tasks/{task}/status', [ProjectController::class, 'updateTaskStatus'])->name('projects.tasks.update-status');
    Route::post('/projects/{project}/progress', [ProjectProgressController::class, 'store'])->name('projects.progress.store');
    Route::post('/projects/{project}/threats', [ProjectThreatController::class, 'store'])->name('projects.threats.store');
});
