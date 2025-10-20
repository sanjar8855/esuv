<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy'da tekshiriladi
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'tariff_id' => 'required|exists:tariffs,id',
            'billing_period' => 'required|string',
            'amount_due' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Mijozni tanlash majburiy.',
            'customer_id.exists' => 'Tanlangan mijoz topilmadi.',
            'tariff_id.required' => 'Tarifni tanlash majburiy.',
            'tariff_id.exists' => 'Tanlangan tarif topilmadi.',
            'billing_period.required' => 'Hisoblash davri majburiy.',
            'amount_due.required' => 'To\'lov summasi majburiy.',
            'amount_due.numeric' => 'To\'lov summasi raqam bo\'lishi kerak.',
            'amount_due.min' => 'To\'lov summasi 0 dan katta bo\'lishi kerak.',
            'due_date.required' => 'To\'lov muddati majburiy.',
            'due_date.date' => 'To\'lov muddati sana formatida bo\'lishi kerak.',
            'status.required' => 'Holat majburiy.',
            'status.in' => 'Noto\'g\'ri holat tanlangan.',
        ];
    }
}
