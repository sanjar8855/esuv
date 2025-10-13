<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait RecordUserStamps
{
    /**
     * ✅ Boot metodi
     */
    protected static function bootRecordUserStamps()
    {
        // ✅ Yaratilganda
        static::creating(function ($model) {
            // Agar allaqachon o'rnatilgan bo'lsa, o'zgartirmaslik
            if (!isset($model->created_by) && Auth::check()) {
                $model->created_by = Auth::id();
            }

            if (!isset($model->updated_by) && Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // ✅ Yangilanganda
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
