<?php
require_once 'vendor/autoload.php';

use Entities\Stores;
use Entities\Users;
use Services\DDLService;
use Services\EntityDesc;
use Services\EntityOperation;

(function () {





    echo 'hix';
    $storesDrop = DDLService::dropTable(new Stores());
    $storesDDL = DDLService::createTable(new Stores());

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=wagaya;charset=utf8mb4', 'php', 'password',
            array(PDO::ATTR_EMULATE_PREPARES => false));

        $stmt = $pdo->prepare($storesDrop);
        $stmt->execute();
        $stmt = $pdo->prepare($storesDDL);
        $stmt->execute();

        echo 'conn';
    } catch (PDOException $e) {
        exit('データベース接続失敗。' . $e->getMessage());
    }

    $usersDrop = DDLService::dropTable(new Users());
    $usersDDL = DDLService::createTable(new Users());

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=wagaya;charset=utf8mb4', 'php', 'password',
            array(PDO::ATTR_EMULATE_PREPARES => false));

        $stmt = $pdo->prepare($usersDrop);
        $stmt->execute();
        $stmt = $pdo->prepare($usersDDL);
        $stmt->execute();

        $user = new Users;
        $userOpe = (new EntityOperation())->init($pdo, $user);
        var_dump($userOpe);
        $user->name = "name";
        $user->password = "password";
        $userOpe->create($user);

        echo 'conn';
    } catch (PDOException $e) {
        exit('データベース接続失敗。' . $e->getMessage());
    }

})();
