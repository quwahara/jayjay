<html>

<head>
  <script src="../../../js/trax/trax.js"></script>
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
    <div>
      <h1>New Entity</h1>
    </div>
    <form>
      <div>Entity name</div>
      <div>
        <input type="text">
      </div>

      <div>Fields</div>
      <div>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
            </tr>
          </thead>
          <tbody>
            <tr class="fields">
              <td>
                <input type="text" class="name">
              </td>
              <td>
                <select class="value">
                  <option>Text</option>
                  <option>Multi line text</option>
                  <option>Dropdown</option>
                  <option>Seigle Checkbox</option>
                  <option>Multi checkboxes</option>
                  <option>Radio button</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>


<!--
        <input type="text">
        <select>
        <option>Text</option>
        <option>Multi line text</option>
        <option>Dropdown</option>
        <option>Seigle Checkbox</option>
        <option>Multi checkboxes</option>
        <option>Radio button</option>
        </select>
-->

      <div><button type="button" class="add-button">Add field</button></div>

      <div><button type="button">OK</button><button type="button">Cancel</button><button type="button">Clear</button></div>

    </form>
  </div>
  <script>
    var t;
    t = new Trax({
      name: "",
      fields: [{
        name: "",
        type: "",
      }],
    });

    // t.tx("apple", "#appleDiv .apple");
    // t.rx("apple", "#appleText2@keyup");
    // t.trx("apple", "#appleText");

    t.tx("fields");

    document.querySelector(".add-button")
    .addEventListener("click", function (event) {
      var field;
      field = t.fields.createItem();
      field.name = "";
      field.type = "Text";
      t.fields.push(field);
    });
  </script>

</body>

</html>
