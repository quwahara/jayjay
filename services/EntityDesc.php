<?php
namespace Services;

require_once 'vendor/autoload.php';

use \ReflectionClass;

class EntityDesc
{
  public static $cache;
  public $ref;
  public $cols;
  public $name;

  public static function load($entity)
  {
    if (is_null(self::$cache)) {
      self::$cache = [];
    }

    $r = new ReflectionClass($entity);

    if (array_key_exists($r->getName(), self::$cache)) {
        echo "[found]";
      return self::$cache[$r->getName()];
    } else {
        echo "[not found]";
        
      $inst = (new EntityDesc())->init($r);
      var_dump($inst);
      self::$cache[$r->getName()] = $inst;
      return $inst;
    }
  }

  public function init($ref)
  {
    $this->ref = $ref;
    $this->name = strtolower($ref->getShortName());
    $this->cols = [];

    $pnames = [];
    foreach ($ref->getProperties() as $p) {
      $pnames[] = $p->name;
    }

    foreach ($pnames as $pname) {
      if ((strpos($pname, '__') === 0)) {
        continue;
      }

      $this->cols[] = $pname;
    }

    return $this;
  }

}
