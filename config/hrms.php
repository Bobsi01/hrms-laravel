<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Display Name
    |--------------------------------------------------------------------------
    */
    'app_name' => env('APP_NAME', 'HRMS'),

    /*
    |--------------------------------------------------------------------------
    | Timezone & Display Formats
    |--------------------------------------------------------------------------
    */
    'timezone' => env('APP_TIMEZONE', 'Asia/Manila'),
    'display_time_format' => 'M d, Y h:i A',
    'display_time_format_with_seconds' => 'M d, Y h:i:s A',
    'display_date_format' => 'M d, Y',

    /*
    |--------------------------------------------------------------------------
    | Superadmin Configuration
    |--------------------------------------------------------------------------
    */
    'superadmin' => [
        'user_id' => (int) env('SUPERADMIN_USER_ID', 0),
        'email' => env('SUPERADMIN_EMAIL', ''),
        'default_password' => env('SUPERADMIN_DEFAULT_PASSWORD', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Info (for PDFs & branding)
    |--------------------------------------------------------------------------
    */
    'company' => [
        'name' => env('COMPANY_NAME', 'HRMS'),
        'address' => env('COMPANY_ADDRESS', ''),
        'logo' => env('COMPANY_LOGO', 'resources/logo.jpg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Leave Default Entitlements
    |--------------------------------------------------------------------------
    */
    'leave_entitlements' => [
        'sick' => 10,
        'vacation' => 12,
        'emergency' => 5,
        'unpaid' => 0,
        'other' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload_dir' => env('UPLOAD_DIR', 'uploads'),
    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx',
        'csv', 'txt', 'zip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Timeouts (seconds)
    |--------------------------------------------------------------------------
    */
    'session' => [
        'idle_timeout' => 10800,     // 3 hours
        'absolute_timeout' => 86400, // 24 hours
        'rotation_interval' => 300,  // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Remember Me Token Lifetime (seconds)
    |--------------------------------------------------------------------------
    */
    'remember_me_lifetime' => 2592000, // 30 days

];
