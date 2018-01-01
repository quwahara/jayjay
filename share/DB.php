<?php
class DDL {
  static function dropTable($entity) {
    print('<pre>');
    $el = PHP_EOL;
    $r = new ReflectionClass($entity);
    var_dump($r);
    
    $pnames = [];
    foreach ($r->getProperties() as $p) {
      $pnames[] = $p->name;
    }
    $s = '';
    if (in_array('__dropTable', $pnames)) {
      $s .= 'DROP TABLE';
      foreach ($entity->{'__dropTable'} as $key => $value) {
        if ($key === 'enableIfExists' && $value === TRUE) {
          $s .= ' IF EXISTS';
        }
      } 
      $s .= ' ' . strtolower($r->name);
      $s .= ';' . $el;
    }
    var_dump($s);
    print('</pre>');
    return $s;
  }
  static function createTable($entity) {
    print('<pre>');
    $el = PHP_EOL;
    $r = new ReflectionClass($entity);
    var_dump($r);
    
    $pnames = [];
    foreach ($r->getProperties() as $p) {
      $pnames[] = $p->name;
    }
    
    $s = '';
    $s .= 'CREATE TABLE ' . strtolower($r->name) . '(' . $el;
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
      var_dump($v, array_key_exists('xdefault', $v));
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
    
    var_dump($s);
    print('</pre>');
      
    return $s;
  }
}
?>