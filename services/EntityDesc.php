<?php
namespace Services;

require_once 'vendor/autoload.php';

use \PDO;
use \ReflectionClass;

class EntityDesc
{
  public static $cache;
  public $ref;
  public $ctor;
  public $cols;
  public $defs;
  public $name;

  public static function load($entity)
  {
    if (is_null(self::$cache)) {
      self::$cache = [];
    }

    $r = new ReflectionClass($entity);

    if (array_key_exists($r->getName(), self::$cache)) {
      return self::$cache[$r->getName()];
    } else {
      $inst = (new EntityDesc())->init($r);
      self::$cache[$r->getName()] = $inst;
      return $inst;
    }
  }

  public function init($ref)
  {
    $this->ref = $ref;
    $this->ctor = $ref->getConstructor();
    $this->name = strtolower($ref->getShortName());
    $this->cols = [];

    $pnames = [];
    foreach ($ref->getProperties() as $p) {
      $pnames[] = $p->name;
    }

    $inst = $ref->newInstanceWithoutConstructor();

    foreach ($pnames as $pname) {
      if ((strpos($pname, '__') === 0)) {
        continue;
      }

      $this->cols[] = $pname;
      $def = $ref->getProperty($pname)->getValue($inst);
      $this->defs[$pname] = $def;
      $this->defs[$pname]['__param_type'] = \Services\EntityDesc::parsePDOParamType($def['type']);
    }

    return $this;
  }

  public static function parsePDOParamType($type)
  {
    if (\preg_match('/^((VAR)?CHAR|(TINY|MEDIUM|LONG)?TEXT)/i', $type)) {
      return PDO::PARAM_STR;
    } else if (\preg_match('/^(((TINY|MEDIUM|LONG|BIG)?INT(EGER)?|(DEC(IMAL)?|NUMERIC|FIXED))/i', $type)) {
      return PDO::PARAM_INT;
    } else {
      return PDO::PARAM_STR;
    }
  }

}
