<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\DashboardController;

// Dashboard Routes
Route::get('dashboard/stats', [DashboardController::class, 'stats']);

// Students Routes
Route::apiResource('students', StudentController::class);

// Disbursements Routes
Route::apiResource('disbursements', DisbursementController::class);

//Jed line
Route::post('/students/import', [StudentController::class, 'import']);

Route::post('students/bulk-update-field', [StudentController::class, 'bulkUpdateField']);
