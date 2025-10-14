<?php
// app/Traits/RecordUserStamps.php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * ✅ STANDARTLASHTIRILGAN TRAIT
 *
 * Bu trait avtomatik ravishda created_by va updated_by ustunlarini to'ldiradi
 *
 * Bazada bo'lishi kerak bo'lgan ustunlar:
 * - created_by (foreignId)
 * - updated_by (foreignId)
 */
trait RecordUserStamps
{
    /**
     * ✅ Boot the trait
     */
    protected static function bootRecordUserStamps()
    {
        // Yangi record yaratilganda
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        // Mavjud record yangilanganda
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * ✅ Kim yaratgan (Relationship)
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * ✅ Kim yangilagan (Relationship)
     */
    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * ✅ Scope: Faqat men yaratgan ma'lumotlarni olish
     */
    public function scopeCreatedByMe($query)
    {
        return $query->where('created_by', Auth::id());
    }

    /**
     * ✅ Scope: Faqat men yangilagan ma'lumotlarni olish
     */
    public function scopeUpdatedByMe($query)
    {
        return $query->where('updated_by', Auth::id());
    }
}

/*
📌 ISHLATISH:
Model'da faqat shuni yozasiz:

use App\Traits\RecordUserStamps;

class Payment extends Model {
    use RecordUserStamps;
}

Va endi:
- $payment->createdBy->name ishlaydi
- $payment->updatedBy->name ishlaydi
- created_by va updated_by avtomatik to'ldiriladi
*/
