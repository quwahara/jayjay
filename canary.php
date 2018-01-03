<?php
require 'vendor/autoload.php';

use Entities\Stores;
use Services\Services;

try {
  $model = (function() {
    $S = Services::singleton();
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
      if ($_POST['command'] === 'delete') {
        $name = "entity";
        $id = $_POST['deleteId'];
        $S->db()->deleteStoreById($name, $id);
      } else if ($_POST['command'] === 'add') {
        $name = "entity";
        $id = $_POST['addId'];
        $field = "name";
        $value = $_POST['addId'];
        $S->db()->insertStore($name, $id, $field, $value);
      }
    }
    return [
      'entities' => $S->db()->findAllEntities()
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
      <input type="text" name="addId">
      <button type="button" r-on:click="save()">Save</button>
      <table>
        <tbody>
          <tr r-for="entities">
            <td r-text="$item.value"></td>
            <td><button type="button" r-attr:data-id="$item.id" r-on:click="delete_($elm)">Del</button></td>
          </tr>
        </tbody>
      </table>
      <input type="hidden" name="command" value="">
      <input type="hidden" name="deleteId" value="">
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
