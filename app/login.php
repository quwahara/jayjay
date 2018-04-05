<html>

<head>
</head>

<body>
<?php
require __DIR__ . '/../vendor/autoload.php';

use Services\Services;
use Services\URIService;

try {
  $model = (function () {
    $S = Services::singleton();
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
      $name = $_POST['name'];
      $password = $_POST['password'];

      $userOpe = $S->db()->loadOperation('Entities\Users');
      $user = $userOpe->newInstance();
      $userOpe->setPropertiesFrom($user, $_POST);

      $found = $userOpe->findOneByPrimaryKey($_POST['name']);

      if ($found) {
        if ($found['password'] === $password) {
          URIService::redirectByFilenameThenExit('home.php');
        }
      }

    }
  })();
} catch (Exception $e) {
  print_r($e);
  return;
}

?>
  <div>
    <form name="theForm" method="post">
      <div>
        <input type="text" name="name">
      </div>
      <div>
        <input type="text" name="password">
      </div>
      <div>
        <button type="submit">Login</button>
      </div>
    </form>
  </div>
  <script>
    window.onload = function() {
    };
  </script>
</body>

</html>
