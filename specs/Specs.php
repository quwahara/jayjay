<?php
namespace Specs;
require_once 'vendor/autoload.php';

class Specs {
  
  public static $singleton_;
  
  public $entities = [
    'name' => 'entities',
    'id' => 'entities',
    'fields' => [
      'name'
    ]
  ];
  
  static function singleton() {
    if (!self::$singleton_) {
      self::$singleton_ = (new Specs())->init();
    }
    return self::$singleton_;
  }
  
  function init() {
    return $this;
  }
  
}
?>
