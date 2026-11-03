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
