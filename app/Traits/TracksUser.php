<?php

namespace App\Traits;

use App\Models\User;

trait TracksUser
{
    /**
     * ✅ Kim yaratgan
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ Kim yangilagan
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * ✅ Scope: Specific user tomonidan yaratilgan
     */
    public function scopeCreatedByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * ✅ Scope: Specific user tomonidan yangilangan
     */
    public function scopeUpdatedByUser($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }
}
