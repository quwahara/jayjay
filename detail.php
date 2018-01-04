<?php
require 'vendor/autoload.php';

use Entities\Stores;
use Services\Services;

try {
  $model = (function() {
    $S = Services::singleton();
    $name_ = $_GET['name_'];
    return [
      'entity' => $S->store()->findByName($name_)
    ];
  })();
} catch (Exception $e) {
  print_r($e);
  return;
}

?>
<html>

<head>
  <script src="./js/radio.js"></script>
</head>

<body>
  <div id="root">
    <form name="theForm" method="post">
    </form>
  </div>
  <script>
    window.onload = function() {
      var radio = new Radio({
        root: document.getElementById("root"),
        model: <?= json_encode($model) ?>,
        methods: {
          save: function() {
            var f = document.theForm;
            f.command.value = 'add';
            document.theForm.submit();
          },
          delete_: function($elm) {
            var f = document.theForm;
            f.command.value = 'delete';
            f.deleteId.value = $elm.dataset.id;
            f.submit();
          }
        }
      });
    };
  </script>
</body>

</html>
