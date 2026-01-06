<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_type',
        'file_name',
        'user_id',
        'company_id',
        'total_rows',
        'success_count',
        'failed_count',
        'status',
        'errors',
    ];

    protected $casts = [
        'errors' => 'array',
        'total_rows' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /**
     * Import qilgan foydalanuvchi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Import qilingan kompaniya
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Import muvaffaqiyatli tugaganmi?
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Import xatoliklari bormi?
     */
    public function hasErrors(): bool
    {
        return $this->failed_count > 0 && !empty($this->errors);
    }

    /**
     * Import to'liq muvaffaqiyatsiz bo'lganmi?
     */
    public function hasFailed(): bool
    {
        return $this->success_count === 0 && $this->failed_count > 0;
    }
}
