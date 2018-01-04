<?php
namespace Services;
require_once 'vendor/autoload.php';

use \PDO;
use Entities\Stores;
use Services\DBService;
use Services\DDLService;
use Services\EntityService;
use Services\StoreService;

class Services {
  
  public static $singleton_;
  
  public $config_;
  public $db_;
  public $ddl_;
  public $entity_;
  public $store_;
  
  static function singleton() {
    if (!self::$singleton_) {
      self::$singleton_ = (new Services())->init();
    }
    return self::$singleton_;
  }
  
  function init() {
    if (!$this->config_) {
      $this->config_ = require 'config.php';
    }
    $this->initStore();
    return $this;
  }
  
  function initStore() {
    $cnf = $this->config_['init']['store'];
    $store = new Stores();
    if ($cnf === 'drop_create') {
      $stmt = $this->ddl()->dropTable($store, TRUE);
      $this->db()->pdo()->prepare($stmt)->execute();
    }
    if ($cnf === 'drop_create' || $cnf === 'create') {
      $stmt = $this->ddl()->createTable($store, TRUE);
      $this->db()->pdo()->prepare($stmt)->execute();
    }
    return $this;
  }
  
  function ddl() {
    if (!$this->ddl_) {
      $this->ddl_ = new DDLService();
    }
    return $this->ddl_;
  }
  
  function db() {
    if (!$this->db_) {
      $dbc = $this->config_['db'];
      $this->db_ = (new DBService())
        ->init($dbc['dsn'], $dbc['username'], $dbc['password'], $dbc['options']);
    }
    return $this->db_;
  }
  
  function entities() {
    return [];
//    if (!$this->entity_) {
//      $this->entity_ = new EntityService();
//    }
//    return $this->entity_;
  }

  function store() {
    if (!$this->store_) {
      $this->store_ = (new StoreService())->init($this->db());
    }
    return $this->store_;
  }
  
}
?>
