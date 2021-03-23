<?php

// the app must have a whitelisted token
return [
    'applications' => [
        [
            'token' => 'aaa',
            'name' => 'A-team',
        ],
        [
            'token' => 'bbb',
            'name' => 'B-tise',
        ],
        
    ],
// the computer must have a whitelisted IP
    'computers' => [
        [
            'ip' => '111.111.111.111',
            'name' => '1forAll',
        ],
        [
            'ip' => '222.222.222.222',
            'name' => '2 tout tout vous saurez tout',
        ],
        [
            'ip' => '::1',
            'name' => 'localhost',
        ],
        
    ],

    
];