<?php

namespace App\Services;

use App\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log a user action
     *
     * @param string $action
     * @param string|null $description
     * @param \Illuminate\Database\Eloquent\Model|null $model
     * @return ActivityLog
     */
    public static function log($action, $description = null, $model = null)
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a system action (no authenticated user)
     *
     * @param \Illuminate\Database\Eloquent\Model|null $model
     */
    public static function logSystem($action, $description = null, $model = null)
    {
        return ActivityLog::create([
            'user_id' => null,
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
