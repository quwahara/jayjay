<?php
return [
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=wagaya;charset=utf8mb4',
        'username' => 'php',
        'password' => 'password',
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => false
        ],
    ],
    'css' => [
        'baseFontSize' => 10.0
    ],
    'init' => [
        // drop_create, create, none
        'store' => 'none'
    ],
    'requires' => [
        'path' => '/snippets/path.php',
        'path2' => '/snippets/path2.php',
    ],
];
