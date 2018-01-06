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

  function insert($name, $id, $field, $value) {
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

  function update($name, $id, $field, $value) {
    $st = $this->pdo()->prepare("
UPDATE stores
SET value = :value
WHERE name = :name
AND id = :id
AND field = :field
;");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    $st->bindParam(':field', $field, PDO::PARAM_STR);
    $st->bindParam(':value', $value, PDO::PARAM_STR);
    return $st->execute();
  }

  function save($name, $id, $field, $value) {
    if ($this->existsByNameAndIdAndField($name, $id, $field)) {
      return $this->update($name, $id, $field, $value);
    } else {
      return $this->insert($name, $id, $field, $value);
    }
  }

  function saveFields($name, $id, $fields) {
    $result = FALSE;
    foreach ($fields as $field => $value) {
      $result = $this->save($name, $id, $field, $value);
      if (!$result) break;
    };
    return $result;
  }

  function deleteByNameAndIdAndField($name, $id, $field) {
    $st = $this->pdo()->prepare("
DELETE FROM stores
WHERE name = :name
AND id = :id
AND field = :field
");
echo ">> $name $id $field";
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    $st->bindParam(':field', $field, PDO::PARAM_STR);
    return $st->execute();
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

  function findAllByName($name, $fetch_style = PDO::FETCH_ASSOC) {
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

  function findAllByNameAndId($name, $id, $fetch_style = PDO::FETCH_ASSOC) {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
WHERE name = :name
AND id = :id
ORDER BY field
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    $st->execute();
    return $st->fetchAll($fetch_style);
  }

  function findByNameAndIdAndField($name, $id, $field, $fetch_style = PDO::FETCH_ASSOC) {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
WHERE name = :name
AND id = :id
AND field = :field
");
    if (!$st) throw new Exception(json_encode($this->pdo()->errorInfo()));
    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    $st->bindParam(':field', $field, PDO::PARAM_STR);
    $st->execute();
    return $st->fetchAll($fetch_style);
  }

  function existsByNameAndIdAndField($name, $id, $field, $fetch_style = PDO::FETCH_ASSOC) {
    return 0 !== count($this->findByNameAndIdAndField($name, $id, $field, $fetch_style));
  }
  
}

?>
