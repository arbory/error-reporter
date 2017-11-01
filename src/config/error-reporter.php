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
        ],
        'removed_value_notification'   => 'value_removed_by_error_reporter'
    ]
];