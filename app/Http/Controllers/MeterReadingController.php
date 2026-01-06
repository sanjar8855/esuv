<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Tariff;
use App\Models\WaterMeter;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;    // Auth Facade
use Illuminate\Support\Facades\Storage; // Storage Facade (rasm uchun kerak bo'lishi mumkin)
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BasicExcelImport;
use App\Imports\MeterReadingsImport;
use Yajra\DataTables\Facades\DataTables; // DataTables Facade

class MeterReadingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- Agar so'rov AJAX orqali DataTables'dan kelsa ---
        if (request()->ajax()) {
            // Asosiy query (kerakli bog'liqliklar bilan)
            $query = MeterReading::with([
                // Mijoz va hisoblagich ma'lumotlarini oldindan yuklash (N+1 optimizatsiyasi)
                'waterMeter.customer'
            ])->select('meter_readings.*'); // Boshqa jadvallar bilan ishlaganda kerak

            // Admin bo'lmasa, kompaniya bo'yicha filtr
            if (!$user->hasRole('admin') && $user->company) {
                $query->whereHas('waterMeter.customer', function ($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                });
            }

            // DataTables'ga uzatish va ustunlarni sozlash
            return DataTables::eloquent($query)
                ->addIndexColumn() // "N" ustuni uchun (DT_RowIndex)
                ->addColumn('customer_link', function (MeterReading $meterReading) { // Mijoz linki
                    // Null-safe operator (?->) yordamida xatolikni oldini olish
                    if ($customer = $meterReading->waterMeter?->customer) {
                        // e() funksiyasi XSS hujumlaridan himoyalaydi
                        return '<a href="'.route('customers.show', $customer->id).'" class="badge badge-outline text-blue">'.e($customer->name).'</a>';
                    }
                    return '<span class="text-muted">Mijoz topilmadi</span>';
                })
                ->addColumn('meter_link', function (MeterReading $meterReading) { // Hisoblagich linki
                    if ($waterMeter = $meterReading->waterMeter) { // Null-safe uchun tekshirish
                        // number_format raqamni chiroyli ko'rinishga keltiradi
                        return '<a href="'.route('water_meters.show', $waterMeter->id).'" class="badge badge-outline text-blue">'.number_format($waterMeter->meter_number, 0, '.', ' ').'</a>';
                    }
                    return '<span class="text-muted">Hisoblagich topilmadi</span>';
                })
                ->editColumn('reading', function (MeterReading $meterReading) { // O'qishni formatlash
                    return number_format($meterReading->reading, 0, '.', ' ');
                })
                ->editColumn('created_at', function (MeterReading $meterReading) { // Sanani formatlash (ixtiyoriy)
                    if (empty($meterReading->created_at)) {
                        return '-';
                    }
                    // Vaqtni O'zbekiston (Toshkent) vaqt mintaqasiga o'tkazamiz va formatlaymiz
                    return $meterReading->created_at->setTimezone(config('app.timezone', 'Asia/Tashkent'))
                        ->format('d.m.Y H:i:s');
                })
                ->addColumn('photo_display', function (MeterReading $meterReading) { // Rasm ko'rinishi
                    if ($meterReading->photo) {
                        $imageUrl = asset('storage/' . $meterReading->photo);
                        // Rasm yuklanmasa, placeholder ko'rsatish uchun onerror atributi
                        $placeholderUrl = "https://placehold.co/50x50/e2e8f0/94a3b8?text=Rasm";
                        return '<a href="'.$imageUrl.'" target="_blank"><img src="'.$imageUrl.'" alt="O‘qish rasmi" width="50" height="50" class="rounded border" style="object-fit: cover;" onerror="this.onerror=null; this.src=\''.$placeholderUrl.'\';"></a>';
                    }
                    return '<span class="text-muted">Rasm yo‘q</span>';
                })
                ->addColumn('status_badge', function (MeterReading $meterReading) { // Holat badge
                    // Ternary operator bilan qisqa yozish
                    return $meterReading->confirmed
                        ? '<span class="badge bg-green text-green-fg">Tasdiqlangan</span>'
                        : '<span class="badge bg-red text-red-fg">Tasdiqlanmagan</span>';
                })
                ->addColumn('actions', function (MeterReading $meterReading) { // Amallar tugmalari
                    $showUrl = route('meter_readings.show', $meterReading->id);
                    $editUrl = route('meter_readings.edit', $meterReading->id);
                    $deleteUrl = route('meter_readings.destroy', $meterReading->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    // Tasdiqlash tugmasi logikasi (agar kerak bo'lsa)
                    $confirmButton = '';
                    // if (!$meterReading->confirmed) {
                    //     $confirmUrl = route('meter_readings.confirm', $meterReading->id); // Bu route mavjudligiga ishonch hosil qiling
                    //     $confirmMethod = method_field('PATCH'); // Yoki POST
                    //     $confirmButton = <<<HTML
                    //     <form action="{$confirmUrl}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham tasdiqlamoqchimisiz?');">
                    //         {$csrf}
                    //         {$confirmMethod}
                    //         <button type="submit" class="btn btn-success btn-sm">Tasdiqlash</button>
                    //     </form>
                    //     HTML;
                    // }

                    // Heredoc sintaksisi HTMLni qulayroq yozishga yordam beradi
                    return <<<HTML
                        <a href="{$showUrl}" class="btn btn-info btn-sm">Batafsil</a>
                        <a href="{$editUrl}" class="btn btn-warning btn-sm">Tahrirlash</a>
                        {$confirmButton}
                        <form action="{$deleteUrl}" method="POST" style="display:inline;" onsubmit="return confirm('Haqiqatan ham o‘chirmoqchimisiz?');">
                            {$csrf}
                            {$method}
                            <button type="submit" class="btn btn-danger btn-sm">O‘chirish</button>
                        </form>
                    HTML;
                })
                // Mijoz va Hisoblagich bo'yicha qidirish/saralash uchun maxsus filtrlar
                ->filterColumn('customer_link', function($query, $keyword) {
                    // Bog'langan jadval ustuni bo'yicha qidirish
                    $query->whereHas('waterMeter.customer', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('meter_link', function($query, $keyword) {
                    // Raqamli qidiruv uchun faqat raqamlarni qoldirish
                    $cleanedKeyword = preg_replace('/[^0-9]/', '', $keyword);
                    if (!empty($cleanedKeyword)) {
                        $query->whereHas('waterMeter', function($q) use ($cleanedKeyword) {
                            $q->where('meter_number', 'like', "%{$cleanedKeyword}%");
                        });
                    }
                })
                // Mijoz va Hisoblagich bo'yicha saralash uchun maxsus tartiblash
                ->orderColumn('customer_link', function ($query, $order) {
                    // To'g'ri saralash uchun jadvallarni birlashtirish (JOIN)
                    $query->join('water_meters', 'meter_readings.water_meter_id', '=', 'water_meters.id')
                        ->join('customers', 'water_meters.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $order)
                        ->select('meter_readings.*'); // JOIN dan keyin asosiy jadval ustunlarini tanlash muhim
                })
                ->orderColumn('meter_link', function ($query, $order) {
                    $query->join('water_meters', 'meter_readings.water_meter_id', '=', 'water_meters.id')
                        ->orderBy('water_meters.meter_number', $order)
                        ->select('meter_readings.*');
                })
                ->rawColumns(['customer_link', 'meter_link', 'photo_display', 'status_badge', 'actions']) // HTML tarkibli ustunlarni belgilash
                ->make(true); // JSON javobini yaratish va qaytarish
        }

        // --- Agar so'rov AJAX emas, oddiy GET bo'lsa ---
        // Faqat umumiy sonni hisoblab, view'ni qaytaramiz
        $meterReadingsQuery = MeterReading::query();
        if (!$user->hasRole('admin') && $user->company) {
            $meterReadingsQuery->whereHas('waterMeter.customer', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });
        }
        $meterReadingsCount = $meterReadingsQuery->count();

        // View'ni faqat son bilan qaytarish
        return view('meter_readings.index', compact('meterReadingsCount'));
    }

    public function create()
    {
        $waterMeters = WaterMeter::with('customer')->get();
        return view('meter_readings.create', compact('waterMeters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading' => 'required|numeric|min:0',
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('meter_readings', 'public');
        }

        // **Oxirgi tasdiqlangan o'qishni olish**
        $lastConfirmedReading = MeterReading::where('water_meter_id', $validated['water_meter_id'])
            ->where('confirmed', true)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastConfirmedReading && $validated['reading'] <= $lastConfirmedReading->reading) {
            return redirect()->back()->withErrors([
                'reading' => 'Yangi ko‘rsatkich (' . $validated['reading'] . ') oxirgi tasdiqlangan (' . $lastConfirmedReading->reading . ') dan katta bo‘lishi kerak.'
            ])->withInput();
        }

        // **Ko'rsatkichni saqlash**
        $meterReading = MeterReading::create($validated);

        // **Agar avtomatik tasdiqlangan bo‘lsa (`confirmed = true`), invoice yaratish**
        if ($meterReading->confirmed) {
            $this->createInvoice($meterReading);
        }

        return $this->redirectBack($meterReading->waterMeter->customer, $meterReading);
    }


    public function show(MeterReading $meterReading)
    {
        return view('meter_readings.show', compact('meterReading'));
    }

    public function edit(MeterReading $meterReading)
    {
        $meterReading->load('waterMeter.customer');
        $customer = $meterReading->waterMeter->customer;
        if (!$customer) {
            abort(404, 'Ushbu ko\'rsatkichga tegishli mijoz topilmadi.');
        }
        $waterMeters = WaterMeter::with('customer')->get();
        return view('meter_readings.edit', compact('meterReading', 'waterMeters', 'customer'));
    }

    public function update(Request $request, MeterReading $meterReading)
    {
        $validated = $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading' => 'required|numeric|min:0',
            'photo_url' => 'nullable|string',
            'reading_date' => 'required|date',
            'confirmed' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        if ($request->hasFile('photo')) {
            // Eski rasmni o‘chirish
            if ($meterReading->photo) {
                Storage::disk('public')->delete($meterReading->photo);
            }

            // Yangi rasmni yuklash
            $validated['photo'] = $request->file('photo')->store('meter_readings', 'public');
        }

        // **Avval tasdiqlanmaganligini tekshirish**
        $wasUnconfirmed = !$meterReading->confirmed;

        // **Yangilash**
        $meterReading->update($validated);

        // **Agar oldin tasdiqlanmagan bo‘lsa va hozir tasdiqlansa, invoice yaratish**
        if ($wasUnconfirmed && $meterReading->confirmed) {
            $this->createInvoice($meterReading);
        }

        return $this->redirectBack($meterReading->waterMeter->customer, $meterReading);
    }

    public function destroy(MeterReading $meterReading)
    {
        if ($meterReading->photo) {
            Storage::disk('public')->delete($meterReading->photo);
        }

        $meterReading->delete();
        return redirect()->route('meter_readings.index')->with('success', 'Hisoblagich o‘qilishi muvaffaqiyatli o‘chirildi!');
    }

    public function confirm($id)
    {
        $meterReading = MeterReading::findOrFail($id);

        // Agar allaqachon tasdiqlangan bo‘lsa, hech narsa qilmasin
        if ($meterReading->confirmed) {
            return back()->with('info', 'Ko‘rsatkich allaqachon tasdiqlangan.');
        }

        // **Tasdiqlash**
        $meterReading->update(['confirmed' => true]);

        // **Tasdiqlangandan keyin invoice yaratish**
        $this->createInvoice($meterReading);

        return $this->redirectBack($meterReading->waterMeter->customer, $meterReading);
    }

    private function createInvoice(MeterReading $meterReading)
    {
        $customer = $meterReading->waterMeter->customer;
        $tariff = Tariff::where('company_id', $customer->company_id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($tariff) {
            // **Oxirgi tasdiqlangan ko‘rsatkichni olish**
            $previousConfirmedReading = MeterReading::where('water_meter_id', $meterReading->water_meter_id)
                ->where('confirmed', true)
                ->where('id', '<', $meterReading->id) // Faqat undan oldingi o'qishlarni olish
                ->orderBy('id', 'desc') // Eng oxirgi tasdiqlanganini olish
                ->first();

            if ($previousConfirmedReading) {
                // **Suv iste'moli farqini hisoblash (yangi - oxirgi tasdiqlangan)**
                $consumption = $meterReading->reading - $previousConfirmedReading->reading;

                // **Manfiy qiymat oldini olish (odatda bu xato bo‘lmasligi kerak)**
                if ($consumption > 0) {
                    $amount_due = $consumption * $tariff->price_per_m3;

                    // **Yangi invoice yaratish**
                    Invoice::create([
                        'customer_id'    => $customer->id,
                        'tariff_id'      => $tariff->id,
                        'billing_period' => now()->format('Y-m'),
                        'amount_due'     => $amount_due,
                        'due_date'       => now()->endOfMonth(),
                        'status'         => 'pending',
                    ]);
                }
            }
        }
    }

    private function redirectBack($customer, $meterReading)
    {
        $previousUrl = url()->previous();

        if (strpos($previousUrl, route('customers.show', $customer->id)) !== false) {
            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Hisoblagich o'qilishi muvaffaqiyatli qo'shildi!');
        } elseif (strpos($previousUrl, route('meter_readings.create')) !== false) {
            return redirect()->route('meter_readings.index')
                ->with('success', 'Hisoblagich o'qilishi muvaffaqiyatli qo'shildi!');
        } elseif (strpos($previousUrl, route('water_meters.show', $meterReading->water_meter_id)) !== false) {
            return redirect()->route('water_meters.show', $meterReading->water_meter_id)
                ->with('success', 'Hisoblagich o'qilishi muvaffaqiyatli qo'shildi!');
        } else {
            return redirect()->route('meter_readings.index')
                ->with('success', 'Hisoblagich o'qilishi muvaffaqiyatli qo'shildi!');
        }
    }

    /**
     * Ko'rsatkichlarni import qilish formasi
     */
    public function showImportForm()
    {
        // Faqat admin va company_owner kirishi mumkin
        if (!auth()->user()->hasAnyRole(['admin', 'company_owner'])) {
            return redirect()->route('meter_readings.index')
                ->with('error', 'Sizda import sahifasiga kirish uchun ruxsat yo\'q.');
        }

        return view('meter_readings.import');
    }

    /**
     * Ko'rsatkichlarni Excel'dan import qilish
     */
    public function handleImport(Request $request)
    {
        $user = auth()->user();

        // Ruxsat tekshiruvi
        if (!$user->hasAnyRole(['admin', 'company_owner'])) {
            return redirect()->route('meter_readings.index')
                ->with('error', 'Sizda import qilish uchun ruxsat yo\'q.');
        }

        // Validatsiya
        $maxFileSize = config('water_meter.import_max_file_size', 10) * 1024; // KB ga o'tkazish
        $request->validate([
            'excel_file' => "required|file|mimes:xlsx,xls,csv|max:{$maxFileSize}"
        ]);

        $file = $request->file('excel_file');

        try {
            // Import log yaratish
            $importLog = ImportLog::create([
                'import_type' => 'meter_readings',
                'file_name' => $file->getClientOriginalName(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'status' => 'processing',
            ]);

            // Excel faylni o'qish va import qilish
            $import = new MeterReadingsImport($importLog);
            Excel::import($import, $file);

            $results = $import->getResults();

            // Natijani qaytarish
            if ($results['success_count'] > 0 && $results['failed_count'] === 0) {
                // Hammasi muvaffaqiyatli
                return redirect()->route('meter_readings.import.form')
                    ->with('success', "{$results['success_count']} ta ko'rsatkich muvaffaqiyatli import qilindi!");
            } elseif ($results['success_count'] > 0 && $results['failed_count'] > 0) {
                // Qisman muvaffaqiyatli
                return redirect()->route('meter_readings.import.form')
                    ->with('warning', "{$results['success_count']} ta ko'rsatkich import qilindi, {$results['failed_count']} ta qatorda xatolik bor.")
                    ->with('import_log_id', $importLog->id);
            } else {
                // Hech narsa import qilinmadi
                return redirect()->route('meter_readings.import.form')
                    ->with('error', 'Hech qanday ko\'rsatkich import qilinmadi. Barcha qatorlarda xatolik bor.')
                    ->with('import_log_id', $importLog->id);
            }

        } catch (\Exception $e) {
            Log::error('Meter readings import error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['file_error' => 'Import qilishda xatolik: ' . $e->getMessage()])
                ->withInput();
        }
    }


}
