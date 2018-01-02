<?php
namespace Services;
require_once 'vendor/autoload.php';

use \PDO;

class DBService {
  
  public $dsn_;
  public $username_;
  public $password_;
  public $options_;
  public $pdo_;
  
  function init($dsn, $username, $password, $options) {
    $this->dsn_ = $dsn;
    $this->username_ = $username;
    $this->password_ = $password;
    $this->options_ = $options;
    return $this;
  }
  
  function pdo() {
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
  
  
  function find($name, $id) {
    
    
    
  }
}

?>
