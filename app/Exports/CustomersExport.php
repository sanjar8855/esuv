<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $companyId;

    public function __construct(int $companyId = null)
    {
        $this->companyId = $companyId;
    }

    /**
     * Eksport uchun ma'lumotlar manbasini (query) qaytaradi.
     */
    public function query()
    {
        // Asosiy so'rov
        $query = Customer::query()
            ->with(['street']) // `kocha nomi` uchun street'ni yuklab olamiz
            ->select('customers.*') // `customers` jadvalidagi barcha ustunlarni tanlaymiz
            // Ichki so'rov yordamida har bir mijozning eng so'nggi ko'rsatkichini topamiz
            ->addSelect(['latest_reading' => DB::table('meter_readings as mr')
                ->join('water_meters as wm', 'mr.water_meter_id', '=', 'wm.id')
                ->whereColumn('wm.customer_id', 'customers.id')
                ->latest('mr.reading_date') // Eng so'nggi sana bo'yicha
                ->take(1)
                ->select('mr.reading')
            ]);

        // Agar ma'lum bir kompaniya tanlangan bo'lsa, faqat o'sha kompaniya mijozlarini olamiz
        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        // Natijalarni ko'cha va uy raqami bo'yicha tartiblaymiz
        return $query->orderBy('street_id')->orderBy('address');
    }

    /**
     * Excel faylning sarlavha (birinchi) qatorini belgilaydi.
     */
    public function headings(): array
    {
        return [
            'Ko\'cha ID',
            'Ko\'cha Nomi',
            'F.I.O.',
            'Telefon Raqami',
            'Uy Raqami',
            'Hisob Raqam',
            'Oila A\'zolari',
            'So\'nggi Ko\'rsatkich',
        ];
    }

    /**
     * Har bir qator ma'lumotlarini kerakli formatga o'tkazadi.
     */
    public function map($customer): array
    {
        return [
            $customer->street_id,
            $customer->street->name ?? 'Noma\'lum', // Bog'liqlikdan ko'cha nomini olamiz
            $customer->name,
            $customer->phone,
            $customer->address,
            "'" . $customer->account_number, // Excelda '0012345' kabi raqamlar to'g'ri ko'rinishi uchun
            $customer->has_water_meter ? '-' : $customer->family_members, // Faqat me'yoriylar uchun
            $customer->has_water_meter ? $customer->latest_reading : 'Me\'yoriy', // So'nggi ko'rsatkich yoki "Me'yoriy" yozuvi
        ];
    }
}
