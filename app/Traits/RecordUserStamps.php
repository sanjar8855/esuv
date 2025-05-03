<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait RecordUserStamps
{
    protected static function bootRecordUserStamps()
    {
        // Ma'lumot yangilanayotganda ishlaydi
        static::updating(function ($model) {
            // Agar foydalanuvchi autentifikatsiyadan o'tgan bo'lsa
            if (Auth::check() && Schema::hasColumn($model->getTable(), 'updated_by_user_id')) {
                // modelda updated_by_user_id ustuni borligini tekshiramiz
                $model->updated_by_user_id = Auth::id();
            }
        });

        // Optional: Agar ma'lumot yaratilayotganda ham kim yaratganini yozish kerak bo'lsa:
         static::creating(function ($model) {
             if (Auth::check()) {
                 if (Schema::hasColumn($model->getTable(), 'created_by_user_id')) {
                     $model->created_by_user_id = Auth::id();
                 }
                 // Agar updated_by ham kerak bo'lsa:
                 if (Schema::hasColumn($model->getTable(), 'updated_by_user_id')) {
                     $model->updated_by_user_id = Auth::id();
                 }
             }
         });
    }
}
