<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AuthController;
//Jed, added
use App\Http\Controllers\ScholarshipProgramController;
use App\Http\Controllers\ScholarshipProgramRecordController;

// Dashboard Routes
Route::get('dashboard/stats', [DashboardController::class, 'stats']);
Route::get('dashboard/warnings/no-uii', [DashboardController::class, 'getNoUiiStudents']);
Route::get('dashboard/warnings/no-lrn', [DashboardController::class, 'getNoLrnStudents']);
Route::get('dashboard/warnings/no-award-number', [DashboardController::class, 'getNoAwardNumberStudents']);
Route::get('dashboard/warnings/incomplete-info', [DashboardController::class, 'getIncompleteInfoStudents']);

// Students Routes
Route::get('students/filter-options', [StudentController::class, 'filterOptions']);
Route::get('students/export', [StudentController::class, 'export']);
Route::get('students/masterlist', [StudentController::class, 'masterlist']);
Route::apiResource('students', StudentController::class);
Route::post('/students/import', [StudentController::class, 'import']);
Route::post('students/bulk-update-field', [StudentController::class, 'bulkUpdateField']);
Route::post('students/lookup-program-info', [StudentController::class, 'lookupProgramInfo']);
Route::post('students/{student}/fill-missing-fields', [StudentController::class, 'fillMissingFields']);
Route::get('students/institutions', [StudentController::class, 'getInstitutions']);
Route::get('students/programs-by-uii', [StudentController::class, 'getProgramsByUii']);
Route::get('students/search-institutions', [StudentController::class, 'searchInstitutions']);
Route::post('students/debug-lookup', [StudentController::class, 'debugLookup']);

// Disbursements Routes
Route::apiResource('disbursements', DisbursementController::class);
Route::post('disbursements/bulk', [DisbursementController::class, 'bulk']);

// Scholarship Programs Routes, Jed added these two lines
Route::apiResource('scholarship_programs', ScholarshipProgramController::class);
Route::get('scholarship_programs/totals', [ScholarshipProgramController::class, 'totals']);
Route::POST('scholarship_programs/update-slots', [ScholarshipProgramController::class, 'updateSlots']);
Route::post('scholarship_programs/edit-slots', [ScholarshipProgramController::class, 'editSlot']);
//ends here

Route::get('scholarship_program_records/grouped', [ScholarshipProgramRecordController::class, 'getPrograms']);
Route::put('scholarship_program_records/updateSlots', [ScholarshipProgramRecordController::class, 'updateSlots']);
Route::apiResource('scholarship_program_records', ScholarshipProgramRecordController::class);

// Logs Routes
Route::get('logs', [LogController::class, 'index']);
Route::post('logs', [LogController::class, 'store']);
Route::post('logs/{id}/rollback', [LogController::class, 'rollback']);

// Auth
Route::post('auth/login', [AuthController::class, 'login']);
