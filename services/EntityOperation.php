<?php
namespace Services;

require_once 'vendor/autoload.php';

use \Exception;
use \PDO;

class EntityOperation
{
  public $desc;

  public function init($pdo, $entity)
  {
    $this->pdo = $pdo;
    $this->desc = EntityDesc::load($entity);
    return $this;
  }

  public function newInstance()
  {
    $desc = $this->desc;
    $ref = $desc->ref;
    $inst = $ref->newInstanceWithoutConstructor();
    foreach ($desc->cols as $col) {
      $ptype = $desc->defs[$col]['__param_type'];
      if ($ptype === PDO::PARAM_INT) {
        $ref->getProperty($col)->setValue($inst, 0);
      } else if ($ptype === PDO::PARAM_STR) {
        $ref->getProperty($col)->setValue($inst, "");
      } else {
        $ref->getProperty($col)->setValue($inst, "");
      }
    }
    return $inst;
  }

  public function create($obj)
  {
    $q = "";
    $q .= "INSERT INTO ";
    $q .= $this->desc->name;
    $q .= " (";
    $q .= implode(", ", $this->desc->cols);
    $q .= " ) VALUES (";
    $q .= ":" . implode(", :", $this->desc->cols);
    $q .= ");";

    $st = $this->pdo->prepare($q);
    if (!$st) {
      throw new Exception(json_encode($this->pdo()->errorInfo()));
    }

    $desc = $this->desc;
    $ref = $desc->ref;
    foreach ($desc->cols as $col) {
      $st->bindValue(':' . $col, $ref->getProperty($col)->getValue($obj), $desc->defs[$col]['__param_type']);
    }

    return $st->execute();
  }

}
