<html>

<head>
</head>

<body>
<?php
require __DIR__ . '/../../../vendor/autoload.php';

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
  This is entity/menus.
  </div>
  <div>
    <a href="new.php">New</a>
  </div>
</body>

</html>
