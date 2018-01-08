<?php
require 'vendor/autoload.php';

use Services\Services;

try {
  $model = (function() {
    $S = Services::singleton();
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
      
      $saveName = $_POST['saveName'];
      $saveId = $_POST['saveId'];
      $saveFields = $_POST;
      unset($saveFields['saveId']);
      
      
      $S->store()->saveFields($saveName, $saveId, $saveFields);
    }
    $name = $_GET['name'];
    $id = $_GET['id'];
    return [
      'id' => $id,
      'fields' => $S->store()->findAllByNameAndId($name, $id),
      'allFields' => $S->store()->allFieldsByName($name),
//      'stored' => $S->store()->findAllByName($id),
      'stored' => $S->store()->findAllIntoAssocByNameAndId($id)
    ];
  })();
  
  echo "<pre>";
  var_dump($model);
  echo "</pre>";
  
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
      <h1 r-text="id"></h1>
      <div><label for="saveId">Id</label><input type="text" name="saveId"></div>
      <table>
        <thead>
          <tr>
            <th>Field</th>
            <th>Definition</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr r-for="fields">
            <td r-text="$item.field"></td>
            <td r-text="$item.value"></td>
            <td><input type="text" r-attr:name="$item.field"></td>
          </tr>
        </tbody>
      </table>
      <button type="button" r-on:click="save()">Save</button>
      <input type="hidden" name="saveName" r-attr:value="id">
      <table>
        <tbody>
          <tr r-for="stored">
            <td r-text="$item.id"></td>
            <td r-text="$item.password"></td>
            <td r-text="$item.password_retype"></td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
  <script>
    window.onload = function() {
      "use strict";
      var radio = new Radio({
        root: document.getElementById("root"),
        model: <?= json_encode($model) ?>,
        methods: {
          save: function() {
            var f = document.theForm;
            f.submit();
          }
        }
      });
    };
  </script>
</body>

</html>
