<?php
// app/Traits/RecordUserStamps.php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait RecordUserStamps
{
    /**
     * ✅ Boot the trait
     */
    protected static function bootRecordUserStamps()
    {
        // ✅ Yangi record yaratilganda
        static::creating(function ($model) {
            if (Auth::check()) {
                // ⚠️ MUHIM: Bazadagi ustun nomiga mos kelishi kerak!
                // Agar bazada 'created_by_user_id' bo'lsa, shu yerda ham shunday yozing
                $model->created_by_user_id = Auth::id();
                $model->updated_by_user_id = Auth::id();
            }
        });

        // ✅ Mavjud record yangilanganda
        static::updating(function ($model) {
            if (Auth::check()) {
                // ⚠️ MUHIM: Bazadagi ustun nomiga mos kelishi kerak!
                $model->updated_by_user_id = Auth::id();
            }
        });
    }

    /**
     * ✅ Relationship: Kim yaratgan
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    /**
     * ✅ Relationship: Kim yangilagan
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by_user_id');
    }
}

/*
📌 MUHIM ESLATMA:
Agar siz Variant 3 (migration) bilan ustun nomlarini 'created_by' va 'updated_by' ga o'zgartirsangiz,
unda bu traitdagi nom'larni ham o'zgartiring:

$model->created_by = Auth::id();
$model->updated_by = Auth::id();

Va relationlarda:
return $this->belongsTo(\App\Models\User::class, 'created_by');
return $this->belongsTo(\App\Models\User::class, 'updated_by');
*/
