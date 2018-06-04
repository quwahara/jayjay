<?php
namespace Services;
require_once __DIR__ . '/../vendor/autoload.php';

use \PDO;
use Entities\Stores;
use Services\DAService;
use Services\DBService;
use Services\DDLService;
use Services\EntityService;
use Services\StoreService;

class Services {
  
  public static $singleton_;
  
  public $config_;
  public $dbdec_;
  public $da_;
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
    $this->config_ = require __DIR__ . '/../config.php';
    $this->dbdec_ = require __DIR__ . '/../dbdec.php';
    $this->initStore();
    return $this;
  }
  
  function initDbdec() {
    $el = PHP_EOL;
    foreach ($this->dbdec_['tables'] as $table) {

      $d = '';
      $d .= 'DROP TABLE ' . $table['tableName'] . ';';
      $this->db()->pdo()->prepare($d)->execute();

      $d = '';
      $d .= 'CREATE TABLE ' . $table['tableName'] . '(';
      $cnm = '';
      foreach ($table['columns'] as $column) {
        $d .= $cnm . $el . $column['fieldName'] . ' ' . $column['definition'];
        $cnm = ',';
      }
      foreach ($table['index_definitions'] as $idx_def) {
        $d .= $cnm . $el . $idx_def;
        $cnm = ',';
      }
      $d .= $el . ');';
      $this->db()->pdo()->prepare($d)->execute();
    }
    return $d;
    // return $this;
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
  
  function da() {
    if (!$this->da_) {
      $this->da_ = (new DAService())
        ->init($this->db()->pdo(), $this->dbdec_);
    }
    return $this->da_;
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
