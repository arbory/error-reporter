<?php
return [
    'reporter'  => [
        'remote_reporting_url' => null,
        'api_key'              => null
    ],
    'sanitizer' => [
        'sensitive_string_identifiers' => [
            'XSRF-TOKEN',
            config('session.cookie')
        ]
    ]
];