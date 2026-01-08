<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportLogController extends Controller
{
    /**
     * Import loglarini ko'rsatish
     */
    public function index()
    {
        $user = Auth::user();

        // AJAX so'rovini tekshirish
        if (request()->ajax()) {
            $query = ImportLog::with(['user', 'company'])
                ->select('import_logs.*');

            // Admin bo'lmasa, faqat o'z kompaniyasini ko'rishi mumkin
            if (!$user->hasRole('admin') && $user->company_id) {
                $query->where('company_id', $user->company_id);
            }

            // Filtrlar
            $importType = request('import_type');
            $status = request('status');
            $companyId = request('company_id');

            if ($importType) {
                $query->where('import_type', $importType);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($user->hasRole('admin') && $companyId) {
                $query->where('company_id', $companyId);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('import_type_display', function (ImportLog $log) {
                    $types = [
                        'customers' => 'Mijozlar',
                        'meter_readings' => 'Ko\'rsatkichlar',
                    ];
                    return $types[$log->import_type] ?? $log->import_type;
                })
                ->addColumn('user_name', function (ImportLog $log) {
                    return $log->user ? e($log->user->name) : '-';
                })
                ->addColumn('company_name', function (ImportLog $log) {
                    return $log->company ? e($log->company->name) : '-';
                })
                ->addColumn('status_badge', function (ImportLog $log) {
                    $badges = [
                        'processing' => '<span class="badge bg-blue">Jarayonda</span>',
                        'completed' => '<span class="badge bg-green">Tugallandi</span>',
                        'completed_with_errors' => '<span class="badge bg-yellow">Xatoliklar bilan</span>',
                        'failed' => '<span class="badge bg-red">Muvaffaqiyatsiz</span>',
                    ];
                    return $badges[$log->status] ?? '<span class="badge bg-gray">' . e($log->status) . '</span>';
                })
                ->addColumn('summary', function (ImportLog $log) {
                    $total = $log->total_rows;
                    $success = $log->success_count;
                    $failed = $log->failed_count;
                    return "Jami: {$total} | Muvaffaqiyatli: {$success} | Xato: {$failed}";
                })
                ->addColumn('created_at_formatted', function (ImportLog $log) {
                    return $log->created_at ? $log->created_at->format('d.m.Y H:i') : '-';
                })
                ->addColumn('actions', function (ImportLog $log) {
                    $showUrl = route('import_logs.show', $log->id);
                    return '<a href="' . $showUrl . '" class="btn btn-info btn-sm">Batafsil</a>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        // Oddiy GET so'rov
        $importLogsCount = ImportLog::when(!$user->hasRole('admin'), function ($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })->count();

        return view('import_logs.index', compact('importLogsCount'));
    }

    /**
     * Bitta import logni batafsil ko'rsatish
     */
    public function show(ImportLog $importLog)
    {
        $user = Auth::user();

        // Ruxsat tekshiruvi: Admin va company_owner o'z kompaniyasining loglarini ko'rishi mumkin
        if (!$user->hasRole('admin') && $importLog->company_id != $user->company_id) {
            abort(403, 'Sizda bu logni ko\'rish uchun ruxsat yo\'q.');
        }

        $importLog->load(['user', 'company']);

        return view('import_logs.show', compact('importLog'));
    }

    /**
     * Xatoliklarni Excel qilib yuklash
     */
    public function exportErrors(ImportLog $importLog)
    {
        $user = Auth::user();

        // Ruxsat tekshiruvi
        if (!$user->hasRole('admin') && $importLog->company_id != $user->company_id) {
            abort(403, 'Sizda bu logni ko\'rish uchun ruxsat yo\'q.');
        }

        // Agar xatolar bo'lmasa
        if (empty($importLog->errors) || $importLog->failed_count == 0) {
            return redirect()->back()->with('error', 'Bu importda xatolar yo\'q.');
        }

        // Excel yaratish
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Sarlavhalar
        $sheet->setCellValue('A1', 'Qator');
        $sheet->setCellValue('B1', 'Xatolar');
        $sheet->setCellValue('C1', 'Ma\'lumotlar');

        // Sarlavhalarni bold qilish
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Ma'lumotlarni yozish
        $row = 2;
        foreach ($importLog->errors as $error) {
            $sheet->setCellValue('A' . $row, $error['row'] ?? '-');
            $sheet->setCellValue('B' . $row, $error['errors'] ?? 'Noma\'lum xato');

            // Ma'lumotlarni JSON formatida yozish
            if (isset($error['data']) && is_array($error['data'])) {
                $dataStr = '';
                foreach ($error['data'] as $key => $value) {
                    $dataStr .= $key . ': ' . ($value ?? '-') . "\n";
                }
                $sheet->setCellValue('C' . $row, trim($dataStr));
            } else {
                $sheet->setCellValue('C' . $row, '-');
            }

            $row++;
        }

        // Ustunlar kengligini avtomatik sozlash
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(60);

        // Fayl nomini yaratish
        $fileName = 'import_errors_' . $importLog->id . '_' . date('Y-m-d_His') . '.xlsx';

        // Excel faylni yuklash
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
