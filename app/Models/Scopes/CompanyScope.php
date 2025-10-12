<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Global scope - barcha query larga avtomatik qo'shiladi
     */
    public function apply(Builder $builder, Model $model)
    {
        // Faqat tizimga kirgan va admin bo'lmagan userlar uchun
        if (auth()->check() && !auth()->user()->hasRole('admin')) {
            // Model jadval nomini olish (customers, invoices, payments...)
            $table = $model->getTable();

            // WHERE company_id = auth()->user()->company_id
            $builder->where("{$table}.company_id", auth()->user()->company_id);
        }
    }
}
