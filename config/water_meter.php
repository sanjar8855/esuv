<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Water Meter Configuration
    |--------------------------------------------------------------------------
    |
    | Suv hisoblagichlari uchun standart sozlamalar
    |
    */

    // Hisoblagich amal qilish muddati (yillarda)
    'default_validity_period' => env('WATER_METER_VALIDITY_PERIOD', 8),

    // Hisoblagich raqami uzunligi (0 bilan to'ldiriladi)
    'meter_number_length' => env('WATER_METER_NUMBER_LENGTH', 7),

    // Hisob raqam uzunligi
    'account_number_length' => env('ACCOUNT_NUMBER_LENGTH', 7),

    // Excel import maksimal fayl hajmi (MB)
    'import_max_file_size' => env('IMPORT_MAX_FILE_SIZE', 10),

    // Hisoblagich ko'rsatkich tasdiqlanishi kerakmi?
    'reading_requires_confirmation' => env('READING_REQUIRES_CONFIRMATION', true),
];
