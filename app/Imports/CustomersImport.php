<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\WaterMeter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * Validatsiyadan oldin ma'lumotlarni tayyorlash
     */
    public function prepareForValidation($data, $index)
    {
        // Telefon va uy raqamlarini string'ga konvert qilish
        return [
            'kompaniya_id' => $data['kompaniya_id'] ?? null,
            'kocha_id' => $data['kocha_id'] ?? null,
            'fio' => $data['fio'] ?? null,
            'hisob_raqam' => $data['hisob_raqam'] ?? null,
            'oila_azolari' => $data['oila_azolari'] ?? null,
            'hisoblagich_ornatilgan_sana' => $data['hisoblagich_ornatilgan_sana'] ?? null,
            'amal_qilish_muddati' => $data['amal_qilish_muddati'] ?? null,
            'boshlangich_korsatkich' => $data['boshlangich_korsatkich'] ?? null,
            'korsatkich_sanasi' => $data['korsatkich_sanasi'] ?? null,
            'hisoblagich_bormi' => $data['hisoblagich_bormi'] ?? null,
            'telefon_raqami' => isset($data['telefon_raqami']) ? (string)$data['telefon_raqami'] : null,
            'uy_raqami' => isset($data['uy_raqami']) ? (string)$data['uy_raqami'] : null,
        ];
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ma'lumotlarni tozalash
            $accountNumber = isset($row['hisob_raqam']) ? str_replace(' ', '', (string)$row['hisob_raqam']) : null;
            $hasWaterMeter = filter_var($row['hisoblagich_bormi'], FILTER_VALIDATE_BOOLEAN);

            // Telefon raqamini tozalash (integer yoki string bo'lishi mumkin)
            $phone = isset($row['telefon_raqami']) && !empty($row['telefon_raqami'])
                ? (string)$row['telefon_raqami']
                : null;

            // Uy raqamini tozalash (integer yoki string bo'lishi mumkin)
            $address = isset($row['uy_raqami']) && !empty($row['uy_raqami'])
                ? (string)$row['uy_raqami']
                : null;

            // Oila a'zolari - 0 bo'lsa null qilish
            $familyMembers = null;
            if (!$hasWaterMeter && isset($row['oila_azolari'])) {
                $familyMembers = $row['oila_azolari'] > 0 ? (int)$row['oila_azolari'] : null;
            }

            // Customer yaratish
            $customer = Customer::create([
                'company_id'      => $row['kompaniya_id'],
                'street_id'       => $row['kocha_id'],
                'name'            => $row['fio'],
                'phone'           => $phone,
                'address'         => $address,
                'account_number'  => $accountNumber,
                'has_water_meter' => $hasWaterMeter,
                'family_members'  => $familyMembers,
                'is_active'       => true,
                'balance'         => 0,
            ]);

            // Agar hisoblagich bo'lsa, uni ham yaratish
            if ($hasWaterMeter) {
                $installationDate = $row['hisoblagich_ornatilgan_sana'] ? Carbon::parse($row['hisoblagich_ornatilgan_sana']) : Carbon::now();
                $validityPeriod = (int)($row['amal_qilish_muddati'] ?? 8);
                $expirationDate = $installationDate->copy()->addYears($validityPeriod);

                $waterMeter = $customer->waterMeter()->create([
                    'meter_number'      => $accountNumber,
                    'installation_date' => $installationDate->toDateString(),
                    'validity_period'   => $validityPeriod,
                    'expiration_date'   => $expirationDate->toDateString(),
                ]);

                // Boshlang'ich ko'rsatkichni yaratish
                if ($waterMeter && isset($row['boshlangich_korsatkich'])) {
                    $readingDate = isset($row['korsatkich_sanasi']) ? Carbon::parse($row['korsatkich_sanasi']) : $installationDate;
                    MeterReading::create([
                        'water_meter_id' => $waterMeter->id,
                        'reading'        => $row['boshlangich_korsatkich'],
                        'reading_date'   => $readingDate->toDateString(),
                        'confirmed'      => true,
                    ]);
                }
            }
        }
    }

    /**
     * Har bir qator uchun validatsiya qoidalari.
     * Bu yerda qoidalar satr ko'rinishida yoziladi va ancha barqaror ishlaydi.
     */
    public function rules(): array
    {
        // Har bir qator uchun alohida validatsiya
        // `*.` prefiksi har bir qatorga qo'llanilishini bildiradi
        return [
            '*.kompaniya_id' => ['required', 'integer', 'exists:companies,id'],
            '*.kocha_id' => ['required', 'integer', 'exists:streets,id'],
            '*.fio' => ['required', 'string', 'max:255'],
            '*.hisob_raqam' => [
                'required',
                'string',
                'max:50',
                // Bu qator har doim customers.account_number da unikal bo'lishini tekshiradi
                Rule::unique('customers', 'account_number'),
                // Bu qator esa hisoblagich bormi 1 bo'lgandagina water_meters.meter_number da unikal bo'lishini tekshiradi
                Rule::unique('water_meters', 'meter_number')->when(function($input) {
                    return filter_var($input->hisoblagich_bormi, FILTER_VALIDATE_BOOLEAN);
                }),
            ],
            '*.oila_azolari' => ['nullable', 'numeric', 'min:1'],
            '*.hisoblagich_ornatilgan_sana' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'date'],
            '*.amal_qilish_muddati' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'integer', 'min:0'],
            '*.boshlangich_korsatkich' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'numeric', 'min:0'],
            '*.korsatkich_sanasi' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'date'],
            '*.hisoblagich_bormi' => ['required', 'boolean'],
            // Telefon va uy_raqami validation'siz - collection() metodida handle qilinadi
        ];
    }
}
