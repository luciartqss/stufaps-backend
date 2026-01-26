<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;

use App\Http\Controllers\AuthController;
//Jed, added
use App\Http\Controllers\ScholarshipProgramController;

// ============================================
// Authentication Routes (Public)
// ============================================
Route::post('/login', [AuthController::class, 'login']);

// ============================================
// Public Routes (No Authentication Required)
// ============================================
// Scholarship Programs Routes (public read access)
Route::get('scholarship_programs', [ScholarshipProgramController::class, 'index']);
Route::get('scholarship_programs/totals', [ScholarshipProgramController::class, 'totals']);
Route::get('scholarship_programs/{scholarship_program}', [ScholarshipProgramController::class, 'show']);

// ============================================
// Protected Routes (Require Authentication)
// ============================================
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard Routes
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);

    // Scholarship Programs Routes (protected write access)
    Route::post('scholarship_programs', [ScholarshipProgramController::class, 'store']);
    Route::put('scholarship_programs/{scholarship_program}', [ScholarshipProgramController::class, 'update']);
    Route::delete('scholarship_programs/{scholarship_program}', [ScholarshipProgramController::class, 'destroy']);
    Route::post('scholarship_programs/update-slots', [ScholarshipProgramController::class, 'updateSlots']);
    // Students Routes
    Route::apiResource('students', StudentController::class);
    Route::post('/students/import', [StudentController::class, 'import']);
    Route::post('students/bulk-update-field', [StudentController::class, 'bulkUpdateField']);

    // Disbursements Routes
    Route::apiResource('disbursements', DisbursementController::class);

    // Logs Routes
    Route::get('logs', [LogController::class, 'index']);
    Route::post('logs', [LogController::class, 'store']);
    Route::post('logs/{id}/rollback', [LogController::class, 'rollback']);
});
