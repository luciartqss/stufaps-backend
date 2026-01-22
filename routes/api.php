<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;

// Dashboard Routes
Route::get('dashboard/stats', [DashboardController::class, 'stats']);

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
