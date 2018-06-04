<?php
namespace Entities;
require_once __DIR__  . '/../vendor/autoload.php';

class Entities {
//  public $__dropTable = [
//    'enableIfExists' => TRUE
//  ];
  public $__uniques = [
    [
      'colmuns' => [ 'name', 'id', 'field' ]
    ]
  ];
  public $name = [
    'type' => 'VARCHAR(60) CHARACTER SET latin1',
    'isNull' => FALSE,
  ];
  public $id = [
    'type' => 'VARCHAR(60) CHARACTER SET latin1',
    'isNull' => FALSE,
  ];
  public $field = [
    'type' => 'VARCHAR(60) CHARACTER SET latin1',
    'isNull' => FALSE,
  ];
  public $value = [
    'type' => 'VARCHAR(1000)',
    'isNull' => FALSE,
    'default' => '""',
  ];
}
?>
