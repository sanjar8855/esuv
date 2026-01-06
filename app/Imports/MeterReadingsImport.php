<?php

namespace App\Imports;

use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\Customer;
use App\Models\ImportLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MeterReadingsImport implements ToCollection, WithHeadingRow
{
    private int $successCount = 0;
    private int $failedCount = 0;
    private array $errors = [];
    private ?ImportLog $importLog = null;

    public function __construct(?ImportLog $importLog = null)
    {
        $this->importLog = $importLog;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Excel da 1-qator sarlavha, 2-qatordan boshlanadi

            try {
                $this->importReading($row->toArray(), $rowNumber);
                $this->successCount++;
            } catch (ValidationException $e) {
                $this->failedCount++;
                $errors = [];
                foreach ($e->errors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errors[] = $error;
                    }
                }
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => implode(', ', $errors),
                    'data' => $row->toArray(),
                ];
            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];
            }
        }

        // ImportLog ni yangilash
        if ($this->importLog) {
            $this->importLog->update([
                'total_rows' => $this->successCount + $this->failedCount,
                'success_count' => $this->successCount,
                'failed_count' => $this->failedCount,
                'errors' => $this->errors,
                'status' => $this->failedCount === 0 ? 'completed' : 'completed_with_errors',
            ]);
        }
    }

    /**
     * Import qilish uchun bitta ko'rsatkich
     */
    private function importReading(array $rowData, int $rowNumber)
    {
        $preparedData = [
            'hisob_raqam' => $rowData['hisob_raqam'] ?? null,
            'boshlangich_korsatkich' => $rowData['boshlangich_korsatkich'] ?? null,
            'oxirgi_korsatkich' => $rowData['oxirgi_korsatkich'] ?? null,
            'korsatkich_sanasi' => $this->parseExcelDate($rowData['korsatkich_sanasi'] ?? null) ?? now()->format('Y-m-d'),
        ];

        // ✅ Validatsiya
        $validator = Validator::make($preparedData, [
            'hisob_raqam' => 'required',
            'boshlangich_korsatkich' => 'nullable|numeric|min:0',
            'oxirgi_korsatkich' => 'nullable|numeric|min:0',
            'korsatkich_sanasi' => 'required|date|before_or_equal:today',
        ], [
            'hisob_raqam.required' => 'Hisob raqam majburiy',
            'boshlangich_korsatkich.numeric' => 'Boshlang\'ich ko\'rsatkich raqam bo\'lishi kerak',
            'oxirgi_korsatkich.numeric' => 'Oxirgi ko\'rsatkich raqam bo\'lishi kerak',
            'korsatkich_sanasi.required' => 'Ko\'rsatkich sanasi majburiy',
            'korsatkich_sanasi.date' => 'Ko\'rsatkich sanasi noto\'g\'ri formatda',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // ✅ Hisoblagichni topish
        $waterMeter = WaterMeter::where('meter_number', $validated['hisob_raqam'])->first();

        if (!$waterMeter) {
            throw new \Exception("Hisob raqam '{$validated['hisob_raqam']}' topilmadi. Hisoblagich mavjud emas.");
        }

        // ✅ Ko'rsatkichni aniqlash
        // Agar faqat boshlangich berilgan bo'lsa - uni ishlatamiz
        // Agar oxirgi ham berilgan bo'lsa:
        //   - Agar teng bo'lsa -> qarz 0
        //   - Agar oxirgi > boshlangich -> farq = qarz
        $readingValue = null;
        $boshlangich = $validated['boshlangich_korsatkich'];
        $oxirgi = $validated['oxirgi_korsatkich'];

        if ($boshlangich !== null && $oxirgi === null) {
            // Faqat boshlangich berilgan
            $readingValue = $boshlangich;
        } elseif ($boshlangich !== null && $oxirgi !== null) {
            // Ikkalasi ham berilgan
            if ($boshlangich == $oxirgi) {
                // Teng bo'lsa -> qarz 0, boshlangichni saqlaymiz
                $readingValue = $boshlangich;
            } elseif ($oxirgi > $boshlangich) {
                // Oxirgi katta bo'lsa -> oxirgi ko'rsatkichni saqlaymiz
                $readingValue = $oxirgi;
            } else {
                throw new \Exception("Oxirgi ko'rsatkich boshlang'ichdan kichik bo'lishi mumkin emas (Boshlangich: {$boshlangich}, Oxirgi: {$oxirgi})");
            }
        } else {
            throw new \Exception("Boshlang'ich ko'rsatkich kiritilishi shart");
        }

        // ✅ MeterReading yaratish
        MeterReading::create([
            'water_meter_id' => $waterMeter->id,
            'reading' => $readingValue,
            'reading_date' => $validated['korsatkich_sanasi'],
            'confirmed' => true,
        ]);
    }

    /**
     * ✅ Excel sanasini parse qilish
     */
    private function parseExcelDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Agar faqat yil kiritilgan bo'lsa (masalan: 2023)
        if (is_numeric($dateValue) && strlen((string)$dateValue) === 4) {
            return Carbon::createFromDate((int)$dateValue, 1, 1)->format('Y-m-d');
        }

        // Excel serial date format
        if (is_numeric($dateValue)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Oddiy sana string
        try {
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Import natijalarini olish
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'failed_count' => $this->failedCount,
            'errors' => $this->errors,
        ];
    }
}
