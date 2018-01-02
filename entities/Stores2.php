<?php
  class Stores2 {
    public $__dropTable = [
      'enableIfExists' => TRUE
    ];
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
      'type' => 'INT UNSIGNED',
      'isNull' => FALSE,
//      'isAutoIncremnt' => TRUE,
//      'isUnique' => TRUE
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