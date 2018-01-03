<?php
namespace Services;
require_once 'vendor/autoload.php';

use \ReflectionClass;
use Services\Services;



class DDLService {
  
  static function dropTable($entity, $enableIfExists = FALSE) {
    $el = PHP_EOL;
    $r = new ReflectionClass($entity);
    $s = '';
    $s .= 'DROP TABLE';
    if ($enableIfExists) {
      $s .= ' IF EXISTS';
    }
    $s .= ' ' . strtolower($r->getShortName());
    $s .= ';' . $el;
    return $s;
  }

  static function createTable($entity, $enableIfNotExists = FALSE) {

    $el = PHP_EOL;
    $r = new ReflectionClass($entity);
    
    $pnames = [];
    foreach ($r->getProperties() as $p) {
      $pnames[] = $p->name;
    }
    
    $s = '';
    $s .= 'CREATE TABLE';
    if ($enableIfNotExists) {
      $s .= ' IF NOT EXISTS';
    }
    $s .= ' ' . strtolower($r->getShortName()) . '(' . $el;
    $defs = [];
    if (in_array('__indexes', $pnames)) {
      foreach ($entity->{'__indexes'} as $index) {
        $defs[] = 'INDEX (' . implode($index['colmuns'], ',') . ')';
      } 
    }
    if (in_array('__uniques', $pnames)) {
      foreach ($entity->{'__uniques'} as $index) {
        $defs[] = 'UNIQUE (' . implode($index['colmuns'], ',') . ')';
      } 
    }
    
    foreach ($pnames as $pname) {
      if ((strpos($pname, '__') === 0)) continue;
      $v = $entity->{$pname};
      $def = $pname
        . ' ' . $v['type']
        . (array_key_exists('isNull', $v) && $v['isNull'] === FALSE ? ' NOT NULL' : '')
        . (array_key_exists('default', $v) ? (" DEFAULT " . $v['default']) : '')
        . (array_key_exists('isAutoIncremnt', $v) && $v['isAutoIncremnt'] === TRUE ? ' AUTO_INCREMENT' : '')
        . (array_key_exists('isUnique', $v) && $v['isUnique'] === TRUE ? ' UNIQUE' : '')
        ;
      $defs[] = $def;
    }
    
    $s .= implode($defs, ',' . $el);
    $s .= $el . ');' . $el;

    return $s;
  }
  
}

?>
