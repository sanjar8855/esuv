<?php
// app/Http/Requests/UpdateCustomerRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        // Faqat o'z kompaniyasidagi mijozni tahrirlash mumkin
        $user = auth()->user();
        $customer = $this->route('customer');

        if ($user->hasRole('admin')) {
            return true;
        }

        return $customer->company_id === $user->company_id;
    }

    public function rules()
    {
        $user = auth()->user();
        $customer = $this->route('customer');

        $rules = [
            'street_id' => 'required|exists:streets,id',
            'name' => 'required|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'regex:/^[0-9]{9}$/',  // ✅ Faqat 9 ta raqam
            ],
            'address' => 'nullable|string|max:500',  // ✅ required dan nullable ga
            'account_number' => [
                'required',
                'string',
                'max:20',
                'unique:customers,account_number,' . $customer->id,  // ✅ O'zini ignore
            ],
            'family_members' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
            'pdf_file' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:2048',  // 2MB
            ],
            'is_active' => 'nullable|boolean',
            'has_water_meter' => 'nullable|boolean',
            'balance' => 'nullable|numeric',
        ];

        // ✅ Admin bo'lsa - kompaniya o'zgartirish mumkin
        if ($user->hasRole('admin')) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'FIO kiritish majburiy',
            'name.max' => 'FIO 255 ta belgidan oshmasligi kerak',

            'street_id.required' => 'Ko\'chani tanlash majburiy',
            'street_id.exists' => 'Tanlangan ko\'cha mavjud emas',

            'account_number.required' => 'Hisob raqam kiritish majburiy',
            'account_number.unique' => 'Bu hisob raqam allaqachon mavjud',
            'account_number.max' => 'Hisob raqam 20 ta belgidan oshmasligi kerak',

            'phone.regex' => 'Telefon raqam 9 ta raqamdan iborat bo\'lishi kerak (masalan: 901234567)',

            'family_members.min' => 'Oila a\'zolari soni kamida 1 bo\'lishi kerak',
            'family_members.max' => 'Oila a\'zolari soni 50 dan oshmasligi kerak',

            'pdf_file.mimes' => 'Faqat PDF fayl yuklash mumkin',
            'pdf_file.max' => 'Fayl hajmi 2MB dan oshmasligi kerak',
        ];
    }

    /**
     * ✅ Validatsiyadan oldin tozalash
     */
    protected function prepareForValidation()
    {
        if ($this->has('account_number')) {
            $this->merge([
                'account_number' => str_replace(' ', '', $this->account_number)
            ]);
        }
    }
}
