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
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ma'lumotlarni tozalash
            $accountNumber = isset($row['hisob_raqam']) ? str_replace(' ', '', $row['hisob_raqam']) : null;
            $hasWaterMeter = filter_var($row['hisoblagich_bormi'], FILTER_VALIDATE_BOOLEAN);

            // Customer yaratish
            $customer = Customer::create([
                'company_id'      => $row['kompaniya_id'],
                'street_id'       => $row['kocha_id'],
                'name'            => $row['fio'],
                'phone'           => $row['telefon_raqami'],
                'address'         => $row['uy_raqami'],
                'account_number'  => $accountNumber,
                'has_water_meter' => $hasWaterMeter,
                'family_members'  => $hasWaterMeter ? null : $row['oila_azolari'],
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
            '*.oila_azolari' => ['required_if:*.hisoblagich_bormi,0', 'nullable', 'integer', 'min:1'],
            '*.hisoblagich_ornatilgan_sana' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'date'],
            '*.amal_qilish_muddati' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'integer', 'min:0'],
            '*.boshlangich_korsatkich' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'numeric', 'min:0'],
            '*.korsatkich_sanasi' => ['required_if:*.hisoblagich_bormi,1', 'nullable', 'date'],
            '*.hisoblagich_bormi' => ['required', 'boolean'],
            '*.telefon_raqami' => ['nullable', 'string', 'max:30'],
            '*.uy_raqami' => ['nullable', 'string', 'max:255'],
        ];
    }
}
