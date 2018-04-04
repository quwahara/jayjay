<html>

<head>
  <script src="./js/trax/trax.js"></script>
</head>

<body>
<?php
require 'vendor/autoload.php';

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
</body>

</html>
