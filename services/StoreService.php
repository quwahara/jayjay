<?php
namespace Services;
require_once 'vendor/autoload.php';

use \Exception;
use \PDO;

class StoreService {
  
  public $db_;
  
  function init($dbService) {
    $this->db_ = $dbService;
    return $this;
  }
  
  function pdo() {
    return $this->db_->pdo();
  }
  
  function insertStore($name, $id, $field, $value) {
    $st = $this->pdo()->prepare("
INSERT INTO stores (name, id, field, value) VALUES (:name, :id, :field, :value);
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    $st->bindParam(':field', $field, PDO::PARAM_STR);
    $st->bindParam(':value', $value, PDO::PARAM_STR);
    return $st->execute();
  }
  
  function deleteStoreById($name, $id) {
    $st = $this->pdo()->prepare("
DELETE FROM stores
WHERE name = :name
AND id = :id
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    return $st->execute();
  }
  
  function findAllEntities($fetch_style = PDO::FETCH_ASSOC) {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
WHERE name = 'entity'
AND field = 'name'
ORDER BY id
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->execute();
    return $st->fetchAll($fetch_style);
  }
  
  function findAll($fetch_style = PDO::FETCH_ASSOC) {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
ORDER BY name, id, field
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->execute();
    return $st->fetchAll($fetch_style);
  }
  
  function findByName($name, $fetch_style = PDO::FETCH_ASSOC) {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
WHERE name = :name
ORDER BY id, field
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->execute();
    return $st->fetchAll($fetch_style);
  }
}

?>
