<?php
namespace Services;
require_once 'vendor/autoload.php';

use Services\Services;

class EntityService {
  
  public $entities_;
  
  function eintities() {
    
    
    
    $S = Services::singleton();
    $entities = $S->db()->find('entities', '%');

    
    
    
    if (!$this->entities_) {
      $this->entities_ = new 
    }
    return $tihs->entities_;
  }
}

?>
