<?php
return [
    'debug' => [
        'enabled' => true,
        'debug_variable_name' => '_dbg',
    ],
    // global structs parameters
    'structs' => [
        'status' => '',
        'message' => '',
        'context' => [],
    ],
    // global attrs parameters
    'attrs' => [],
    // global data parameters
    'data' => [
        'status' => '',
        'message' => '',
        'context' => [],
    ],
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
        'redirect_path' => 'forbidden.php'
    ],
    'internal_server_error' => [
        'redirect_path' => 'internal-server-error.php'
    ],
    'requires' => [],
];
 