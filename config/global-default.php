<?php
return [
    'debug' => [
        'enabled' => false,
        'debug_variable_name' => '_dbg',
    ],
    // global structs parameters
    'structs' => [
        'status' => '',
        'message' => '',
        'context' => [
            'violations[]' => [
                'name' => '',
                'type' => '',
                'value' => '',
                'violation' => '',
                'params[]' => [
                    'name' => '',
                    'value' => '',
                ],
            ],
        ],
    ],
    // global attrs parameters
    'attrs' => [],
    // global data parameters
    'data' => [],
    'db' => [
        'attributes' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ],
    ],
    'xsrf' => [
        'session_variable_name' => '_xsrf',
        'cookie_name' => 'XSRF-TOKEN',
        'hidden_name' => '_xsrf',
        'header_name' => 'X-XSRF-TOKEN',
    ],
    'login' => [
        'loggedin_variable_name' => 'loggedin',
        'redirect_path' => 'index.php',
        'redirect_server_vars_name' => 'redirect_server_vars',
    ],
    'access_denied' => [
        'redirect_path' => '-e-forbidden.php'
    ],
    'internal_server_error' => [
        'redirect_path' => '-e-internal-server-error.php'
    ],
    'requires' => [],
    'part' => [
        'root_id' => 1,
    ],
];
