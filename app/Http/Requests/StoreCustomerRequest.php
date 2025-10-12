<?php
// app/Http/Requests/StoreCustomerRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Ruxsat tekshiruvi
     */
    public function authorize()
    {
        // Admin yoki company_owner yarata oladi
        return auth()->user()->hasAnyRole(['admin', 'company_owner', 'employee']);
    }

    /**
     * Validatsiya qoidalari
     */
    public function rules()
    {
        $user = auth()->user();
        $hasWaterMeter = $this->boolean('has_water_meter');

        $rules = [
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^\+?998[0-9]{9}$/', // ✅ O'zbekiston format
            ],
            'address' => 'nullable|string|max:500',
            'account_meter_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'account_number'),
            ],
            'family_members' => [
                $hasWaterMeter ? 'nullable' : 'required', // ✅ Shartli majburiy
                'integer',
                'min:1',
                'max:50', // ✅ Maksimum cheklov
            ],
            'has_water_meter' => 'nullable|boolean',
            'initial_reading' => [
                'nullable',
                Rule::requiredIf($hasWaterMeter),
                'numeric',
                'min:0',
                'max:999999', // ✅ Maksimum ko'rsatkich
            ],
            'reading_date' => [
                'nullable',
                Rule::requiredIf($hasWaterMeter),
                'date',
                'before_or_equal:today', // ✅ Kelajak sana yo'q
            ],
        ];

        // ✅ Admin bo'lsa - kompaniya tanlash majburiy
        if ($user->hasRole('admin')) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        // ✅ Hisoblagich bo'lsa - meter_number ham unique bo'lishi kerak
        if ($hasWaterMeter) {
            $rules['account_meter_number'][] = Rule::unique('water_meters', 'meter_number');
        }

        return $rules;
    }

    /**
     * ✅ Xato xabarlari
     */
    public function messages()
    {
        return [
            'name.required' => 'FIO kiritish majburiy',
            'name.max' => 'FIO 255 ta belgidan oshmasligi kerak',

            'street_id.required' => 'Ko\'chani tanlash majburiy',
            'street_id.exists' => 'Tanlangan ko\'cha mavjud emas',

            'account_meter_number.required' => 'Hisob raqam kiritish majburiy',
            'account_meter_number.unique' => 'Bu hisob raqam allaqachon mavjud',
            'account_meter_number.max' => 'Hisob raqam 50 ta belgidan oshmasligi kerak',

            'phone.regex' => 'Telefon raqam +998901234567 formatida bo\'lishi kerak',

            'family_members.required' => 'Oila a\'zolari sonini kiritish majburiy (meyoriy uchun)',
            'family_members.min' => 'Oila a\'zolari soni kamida 1 bo\'lishi kerak',
            'family_members.max' => 'Oila a\'zolari soni 50 dan oshmasligi kerak',

            'initial_reading.required' => 'Boshlang\'ich ko\'rsatkichni kiritish majburiy',
            'initial_reading.min' => 'Ko\'rsatkich 0 dan kichik bo\'lmasligi kerak',
            'initial_reading.max' => 'Ko\'rsatkich juda katta',

            'reading_date.required' => 'O\'qish sanasini kiritish majburiy',
            'reading_date.before_or_equal' => 'O\'qish sanasi bugundan kech bo\'lmasligi kerak',
        ];
    }

    /**
     * ✅ Validatsiyadan keyingi qayta ishlash
     */
    protected function prepareForValidation()
    {
        // Bo'sh joylarni tozalash
        if ($this->has('account_meter_number')) {
            $this->merge([
                'account_meter_number' => str_replace(' ', '', $this->account_meter_number)
            ]);
        }
    }
}
