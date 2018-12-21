<?php
return [
    'debug' => [
        'enabled' => true,
        'debug_variable_name' => '_dbg',
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
        'redirect_path' => 'errors/forbidden.php'
    ],
    'internal_server_error' => [
        'redirect_path' => 'errors/internal-server-error.php'
    ],

];
?>
