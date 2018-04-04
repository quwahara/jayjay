<?php
namespace Services;

require_once 'vendor/autoload.php';

use \Exception;
use \PDO;

class EntityOperation
{
  public $pdo;
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

  public function setPropertiesFrom($dst, $src)
  {
    $desc = $this->desc;
    $ref = $desc->ref;
    foreach ($desc->cols as $col) {
      if (!array_key_exists($col, $src)) {
        continue;
      }
      $ref->getProperty($col)->setValue($dst, $src[$col]);
    }
    return $dst;
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

  public function findOneByPrimaryKey($value, $fetch_style = PDO::FETCH_ASSOC)
  {
    $pk = $this->desc->primaryKey;
    $q = "";
    $q .= "SELECT * FROM ";
    $q .= $this->desc->name;
    $q .= " WHERE ";
    $q .= $pk;
    $q .= " = :";
    $q .= $pk;
    $q .= ";";

    $st = $this->pdo->prepare($q);
    $st->bindValue(':' . $pk, $value);

    if (!$st) {
      throw new Exception(json_encode($this->pdo()->errorInfo()));
    }

    $st->execute();
    $result = $st->fetchAll($fetch_style);
    if ($result) {
      return $result[0];
    } else {
      return null;
    }

  }

}
