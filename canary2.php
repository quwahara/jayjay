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
        $name = $_POST['addName'];
        $id = $_POST['addId'];
        $field = $_POST['addField'];
        $value = $_POST['addValue'];
        $S->db()->insertStore($name, $id, $field, $value);
      }
    }
    return [
      'entities' => $S->store()->findAll()
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
      <div><label for="addName">Name</label><input type="text" name="addName"></div>
      <div><label for="addId">Id</label><input type="text" name="addId"></div>
      <div><label for="addField">Field</label><input type="text" name="addField"></div>
      <div><label for="addValue">Value</label><input type="text" name="addValue"></div>
      <button type="button" r-on:click="save()">Save</button>
      <table>
        <tbody>
          <tr r-for="entities">
            <td r-text="$item.name"></td>
            <td r-text="$item.id"></td>
            <td r-text="$item.field"></td>
            <td r-text="$item.value"></td>
            <td><button type="button" r-attr:data-id="$item.id" r-on:click="edit($item)">Edit</button></td>
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
          edit: function($item) {
            var f = document.theForm;
            f.addName.value = $item.name;
            f.addId.value = $item.id;
            f.addField.value = $item.field;
            f.addValue.value = $item.value;
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
