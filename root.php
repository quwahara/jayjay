<?php
require_once 'init.php';

(function () {
  echo 'hi';
  $storesDrop = DDL::dropTable(new Stores());
  $storesDDL = DDL::createTable(new Stores());

  try {
    $pdo = new PDO('mysql:host=localhost;dbname=wagaya;charset=utf8mb4','php','password',
  array(PDO::ATTR_EMULATE_PREPARES => false));
    
    $stmt = $pdo->prepare($storesDrop);
    $stmt->execute();
    $stmt = $pdo->prepare($storesDDL);
    $stmt->execute();
       
    echo 'conn';
  } catch (PDOException $e) {
   exit('データベース接続失敗。'.$e->getMessage());
  }
  
  
})();


?>
