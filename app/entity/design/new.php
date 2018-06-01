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
    <form name="formA">

      <div><button type="button" id="b2">Put date to table name</button></div>
      <div><div id="div2"><span class="entityName"></span></div></div>
      <div><button type="button" class="add-button">Add field</button></div>
      <div><button type="button" id="okBtn">OK</button><button type="button">Cancel</button><button type="button">Clear</button></div>

      <div>Entity name</div>
      <div>
        <input type="text" class="entityName">
      </div>
      <div>
        <span class="entity"></span>
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
          <tbody class="fields">
            <tr>
              <td>
                <input type="text" class="fieldName">
              </td>
              <td>
                <select class="type">
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

    </form>
  </div>
  <script>
    var xo;
    xo = new Trax.Xobject({
      entityName: "",
      fields: [{
        fieldName: "",
        type: "",
      }]
    });

    xo._bind("entityName");
    xo._bind("entityName", document.getElementById("div2"));
    xo._bind("fields");

    document.querySelector(".add-button")
    .addEventListener("click", function (event) {
      var field;
      console.log("b1-click");
      field = xo.fields.newItem();
      // field.fieldName = new Date().toISOString();
      field.fieldName = "";
      field.type = "Text";
      xo.fields.push(field);
    });

    document.getElementById("b2").addEventListener("click", function (event) {
      console.log("b2-click");
      xo.entityName = new Date().toISOString();
    });

    document.getElementById("okBtn").addEventListener("click", function (event) {
      console.log("okBtn clicked", JSON.stringify(xo, null, 2));
    });

  </script>

</body>

</html>
