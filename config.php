<?php
return [
  'db' => [
    'dsn' => 'mysql:host=localhost;dbname=wagaya;charset=utf8mb4',
    'username' => 'php',
    'password' => 'password',
    'options' => [
      PDO::ATTR_EMULATE_PREPARES => false
    ]
  ],
  'init' => [
    'store' => 'drop_create'
  ]
];
?>
