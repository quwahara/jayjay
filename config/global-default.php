<?php
return [
    'debug' => [
        'enabled' => true,
        'debug_variable_name' => '_dbg',
    ],
    'xsrf' => [
        'session_variable_name' => '_xsrf',
        'cookie_name' => 'XSRF-TOKEN',
        'hidden_name' => '_xsrf',
        'header_name' => 'X-XSRF-TOKEN',
    ],
];
?>
