<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,transfer,online',
            'payment_date' => 'nullable|date|before_or_equal:today',
            'confirmed' => 'sometimes|boolean',
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
            'amount.required' => 'To\'lov summasi majburiy.',
            'amount.numeric' => 'To\'lov summasi raqam bo\'lishi kerak.',
            'amount.min' => 'To\'lov summasi kamida 1 so\'m bo\'lishi kerak.',
            'payment_method.required' => 'To\'lov usulini tanlash majburiy.',
            'payment_method.in' => 'Noto\'g\'ri to\'lov usuli tanlangan.',
            'payment_date.date' => 'To\'lov sanasi noto\'g\'ri formatda.',
            'payment_date.before_or_equal' => 'To\'lov sanasi kelajakda bo\'lishi mumkin emas.',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Agar payment_date yuborilmagan bo'lsa, bugungi kunni qo'yamiz
        if (!$this->has('payment_date')) {
            $this->merge([
                'payment_date' => now()->format('Y-m-d'),
            ]);
        }
    }
}
