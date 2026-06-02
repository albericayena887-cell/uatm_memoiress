<?php
declare(strict_types=1);

return [
    'app_name' => 'UATM GASA',
    'app_url' => getenv('APP_URL') ?: 'http://uatm-memoiress.gt.tc',
    'db_host' => getenv('DB_HOST') ?: 'sql111.infinityfree.com',
    'db_name' => getenv('DB_NAME') ?: 'if0_42076718_memoiress',
    'db_user' => getenv('DB_USER') ?: 'if0_42076718',
    'db_pass' => getenv('DB_PASS') ?: 'rpjWq7G0JNg',
    'db_charset' => 'utf8mb4',
    'mail_from' => getenv('MAIL_FROM') ?: 'noreply@uatm.edu',
    'mail_from_name' => 'UATM GASA',
    'upload_max_size' => 10 * 1024 * 1024,
    'allowed_pdf' => ['application/pdf'],
    'allowed_word' => [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
    'allowed_images' => ['image/jpeg', 'image/png', 'image/webp'],
];