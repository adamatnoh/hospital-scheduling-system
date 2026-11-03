<?php

return [

    'name' => 'HSS | Hospital Scheduling System',

    'hospital' => 'Hospital Besar Hajjah Khalsom',

    /*
    | One-click sign-in buttons for the seeded admin, scheduler and doctor
    | accounts on the login page. On by default locally; set DEMO_LOGIN=true
    | to show them anywhere else, such as the public demo deployment.
    */
    'demo_login' => (bool) env('DEMO_LOGIN', env('APP_ENV', 'production') === 'local'),

    /*
    | Shift hours in the app timezone, used for the timed events in each
    | doctor's calendar feed. A shift that ends at or before its start time
    | (Night) finishes the next morning. On-call and ward duties are all-day.
    */
    'shift_hours' => [
        'Morning' => ['07:00', '14:00'],
        'Evening' => ['14:00', '21:00'],
        'Night' => ['21:00', '07:00'],
    ],

    /*
    | Malaysian states used when a department decides which public holidays
    | count. National holidays apply to every department as well.
    */
    'states' => [
        'National',
        'Johor',
        'Kedah',
        'Kelantan',
        'Melaka',
        'Negeri Sembilan',
        'Pahang',
        'Penang',
        'Perak',
        'Perlis',
        'Sabah',
        'Sarawak',
        'Selangor',
        'Terengganu',
        'Kuala Lumpur',
        'Labuan',
        'Putrajaya',
    ],

];
