<?php
return [
    'reporter' => [
        'enabled'            => true,
        'api_url'            => null,
        'api_key'            => null,
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