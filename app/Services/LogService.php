<?php

namespace App\Services;

use App\Models\Log;

class LogService
{
    public static function log($model, $modelId, $action, $oldData = null, $newData = null)
    {
        $changedFields = [];

        if ($action === 'update' && $oldData && $newData) {
            foreach ($newData as $key => $value) {
                if (($oldData[$key] ?? null) !== $value) {
                    $changedFields[] = $key;
                }
            }
        }

        Log::create([
            'model' => class_basename($model),
            'model_id' => $modelId,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'changed_fields' => implode(',', $changedFields),
            'user_id' => auth()->id() ?? null,
            'ip_address' => request()->ip(),
        ]);
    }
}