<?php
namespace Services;

require_once 'vendor/autoload.php';

use Services\EntityOperation;
use \Exception;
use \PDO;

class DBService
{

  public $dsn_;
  public $username_;
  public $password_;
  public $options_;
  public $pdo_;

  public function init($dsn, $username, $password, $options)
  {
    $this->dsn_ = $dsn;
    $this->username_ = $username;
    $this->password_ = $password;
    $this->options_ = $options;
    return $this;
  }

  public function pdo()
  {
    if (!$this->pdo_) {
      $this->pdo_ = new PDO(
        $this->dsn_,
        $this->username_,
        $this->password_,
        $this->options_
      );
    }
    return $this->pdo_;
  }

  public function loadOperation($entity)
  {
    return (new EntityOperation())->init($this->pdo(), $entity);
  }

  public function insertStore($name, $id, $field, $value)
  {
    $st = $this->pdo()->prepare("
INSERT INTO stores (name, id, field, value) VALUES (:name, :id, :field, :value);
");
    if (!$st) {
      throw new Exception(json_encode($this->pdo()->errorInfo()));
    }

    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    $st->bindParam(':field', $field, PDO::PARAM_STR);
    $st->bindParam(':value', $value, PDO::PARAM_STR);
    return $st->execute();
  }

  public function deleteStoreById($name, $id)
  {
    $st = $this->pdo()->prepare("
DELETE FROM stores
WHERE name = :name
AND id = :id
");
    if (!$st) {
      throw new Exception(json_encode($this->pdo()->errorInfo()));
    }

    $st->bindParam(':name', $name, PDO::PARAM_STR);
    $st->bindParam(':id', $id, PDO::PARAM_STR);
    return $st->execute();
  }

  public function findAllEntities($fetch_style = PDO::FETCH_ASSOC)
  {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
WHERE name = 'entity'
AND field = 'name'
ORDER BY id
");
    if (!$st) {
      throw new Exception(json_encode($this->pdo()->errorInfo()));
    }

    $st->execute();
    return $st->fetchAll($fetch_style);
  }

  public function findEntityById($fetch_style = PDO::FETCH_ASSOC)
  {
    $st = $this->pdo()->prepare("
SELECT * FROM stores
WHERE name = 'entity'
AND field = 'name'
ORDER BY id
");
    if (!$st) {
      throw new Exception(json_encode($this->pdo()->errorInfo()));
    }

    $st->execute();
    return $st->fetchAll($fetch_style);
  }
}
