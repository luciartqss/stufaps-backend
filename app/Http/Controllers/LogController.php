<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Student;
use App\Models\Disbursement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = Log::latest()->get();
        return response()->json($logs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'model_id' => 'required|integer',
            'action' => 'required|in:create,update,delete',
            'old_data' => 'nullable|json',
            'new_data' => 'nullable|json',
            'ip_address' => 'nullable|string',
        ]);

        Log::create($validated);

        return response()->json(['message' => 'Log created successfully'], 201);
    }

    public function rollback($id): JsonResponse
    {
        $log = Log::find($id);

        if (!$log) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        try {
            $modelClass = "App\\Models\\{$log->model}";

            if (!class_exists($modelClass)) {
                return response()->json(['error' => 'Model class not found'], 400);
            }

            $oldData = is_array($log->old_data) ? $log->old_data : json_decode($log->old_data, true);

            if ($log->action === 'update' && $oldData) {
                // For bulk updates, we need to use the primary key based on model
                $primaryKey = $log->model === 'Student' ? 'seq' : 'id';
                
                // Find and update the record
                $record = $modelClass::where($primaryKey, $log->model_id)->first();

                if (!$record) {
                    return response()->json(['error' => 'Record not found'], 404);
                }

                $currentValues = $record->toArray();
                $record->update($oldData);

                // Log the rollback action
                Log::create([
                    'model' => $log->model,
                    'model_id' => $log->model_id,
                    'action' => 'update',
                    'old_data' => json_encode(array_intersect_key($currentValues, $oldData)),
                    'new_data' => json_encode($oldData),
                    'changed_fields' => $log->changed_fields,
                    'user_id' => auth()->id() ?? null,
                    'ip_address' => request()->ip(),
                ]);

                return response()->json(['message' => 'Record reverted successfully']);
            }

            return response()->json(['error' => 'Cannot rollback this action'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Rollback failed: ' . $e->getMessage()
            ], 500);
        }
    }
}