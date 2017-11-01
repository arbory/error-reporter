<?php
return [
    'reporter' => [
        'enabled'            => true,
        'api_url'            => env('ERROR_REPORTER_API_URL'),
        'api_key'            => env('ERROR_REPORTER_API_KEY'),
        'report_stack_trace' => true
    ],

    'sanitizer' => [

        'sensitive_string_identifiers' => [
            'XSRF-TOKEN',
            config('session.cookie')
        ],

        'sensitive_key_patterns' => [
            '(\S*)password(\S*).*'
        ],

        'removed_value_notification' => 'value_removed_by_error_reporter'
    ]
];