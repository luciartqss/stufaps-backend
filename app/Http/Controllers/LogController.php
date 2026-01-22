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
        $logs = Log::latest('created_at')->get();
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

        // Check if already rolled back
        if ($log->is_rolled_back) {
            return response()->json(['error' => 'This log has already been rolled back'], 400);
        }

        try {
            $modelClass = "App\\Models\\{$log->model}";

            if (!class_exists($modelClass)) {
                return response()->json(['error' => 'Model class not found'], 400);
            }

            $oldData = is_array($log->old_data) ? $log->old_data : json_decode($log->old_data, true);

            if ($log->action === 'update' && $oldData) {
                if ($log->model_id == 0) {
                    // Handle bulk update
                    $field = array_key_first($oldData);
                    $oldValue = $oldData[$field];
                    $newData = json_decode($log->new_data, true);
                    $newValue = $newData[$field];

                    $count = $modelClass::where($field, $newValue)->update([$field => $oldValue]);

                    // Mark as rolled back (do NOT create a new log entry)
                    $log->update(['is_rolled_back' => true]);

                    return response()->json(['message' => "Reverted $count records successfully"]);
                }

                // Handle individual update
                $primaryKey = $log->model === 'Student' ? 'seq' : 'id';
                $record = $modelClass::where($primaryKey, $log->model_id)->first();

                if (!$record) {
                    return response()->json(['error' => 'Record not found'], 404);
                }

                $record->update($oldData);

                // Mark as rolled back (do NOT create a new log entry)
                $log->update(['is_rolled_back' => true]);

                return response()->json(['message' => 'Record reverted successfully']);
            }

            return response()->json(['error' => 'Cannot rollback this action'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Rollback failed: ' . $e->getMessage()], 500);
        }
    }
}