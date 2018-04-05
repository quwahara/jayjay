<html>

<head>
  <script src="./js/trax/trax.js"></script>
</head>

<body>
<?php
require __DIR__ . '/../../vendor/autoload.php';

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
  This is entity/design.
  </div>
    <a href="app/entity/design.php">New</a>
  <div>
  </div>
</body>

</html>
