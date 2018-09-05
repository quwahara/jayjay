<?php
require __DIR__ . '/../vendor/autoload.php';

use Services\Services;
use Services\SH;
use Services\URIService;

try {
    switch (session_status()) {
        case PHP_SESSION_DISABLED:
            $sst = 'none';
            $ssstart = session_start();
            break;
        case PHP_SESSION_NONE:
            $sst = 'none';
            $ssstart = session_start();
            break;
        case PHP_SESSION_ACTIVE:
            $sst = 'avtive';
            session_reset();
            break;
        default:
            throw new Exception('Session was disabled.');
            break;
    }


    session_start();
    if (session_status() === PHP_SESSION_ACTIVE && $_SESSION['name']) {
        $name = $_SESSION['name'];
    } else {
        $name = 'N/A';
    }

    $model = (function () {
        $S = Services::singleton();
        return null;
    })();
} catch (Exception $e) {
    print_r($e);
    return;
}

?>
<html>

<head>
</head>

<body>
  <div>
  This is home.
  </div>
    <a href="entity/design/menus.php">Design Entity</a>
  <div>
    <div><?= $name ?></div>
    <div>id[<?= session_id() ?>]</div>
    <div><?= session_status() ?></div>
    <div><?= var_export($name, true) ?></div>
    <div><?= var_export($_SESSION, true) ?></div>
    <div><?= $_SESSION['x'] ?></div>
  </div>
</body>

</html>
