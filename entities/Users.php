<?php
namespace Entities;
require_once 'vendor/autoload.php';

class Users {
  public $name = [
    'type' => 'VARCHAR(60) CHARACTER SET latin1',
    'isPrimaryKey' => TRUE,
  ];
  public $password = [
    'type' => 'VARCHAR(1000) CHARACTER SET latin1',
    'isNull' => FALSE,
  ];
}
?>
