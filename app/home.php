<html>

<head>
</head>

<body>
<?php
require __DIR__ . '/../vendor/autoload.php';

use Services\Services;

try {
  $model = (function () {
    $S = Services::singleton();
    return null;
  })();
} catch (Exception $e) {
  print_r($e);
  return;
}

?>
  <div>
  This is home.
  </div>
    <a href="entity/design/menus.php">Design Entity</a>
  <div>
  </div>
</body>

</html>
