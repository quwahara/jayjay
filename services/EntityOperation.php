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

    $ref = $this->desc->ref;
    foreach ($this->desc->cols as $col) {
      $st->bindValue(':' . $col, $ref->getProperty($col)->getValue($obj), PDO::PARAM_STR);
    }

    return $st->execute();
  }

}
