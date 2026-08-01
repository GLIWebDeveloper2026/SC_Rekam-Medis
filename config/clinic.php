<?php

return [
    'timezone' => env('CLINIC_TIMEZONE', 'Asia/Jakarta'),
    'patient_check_in' => [
        'opens_minutes_before' => (int) env('PATIENT_CHECK_IN_OPENS_MINUTES_BEFORE', 120),
        'closes_minutes_after' => (int) env('PATIENT_CHECK_IN_CLOSES_MINUTES_AFTER', 180),
    ],
];
