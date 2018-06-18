<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Services\Services;

$er = '';
$dbg = 'aa';
$entity_name = $_GET['entity_name'];
try {
  $model = (function ($entity_name) {
    $S = Services::singleton();
    $da = $S->da();

    $sql = 'select * from entities where entity_name = :entity_name;';
    $vals = ['entity_name' => $entity_name];
    $entity = $da->findOne($da->getTableByTableName('entities'), $sql, $vals);

    $sql = 'select * from fields where entity_id = :entity_id;';
    $vals = ['entity_id' => $entity['id']];
    $fields = $da->findAll($da->getTableByTableName('fields'), $sql, $vals);

    return [
      "entity" => $entity,
      "fields" => $fields
    ];
  })($_GET['entity_name']);
} catch (Exception $e) {
  $er = print_r($e, TRUE);
}
?>
<html>

<head>
  <script src="../../../js/lib/node_modules/axios/dist/axios.js"></script>
  <script src="../../../js/trax/trax.js"></script>
</head>

<body>
  <div>
    <div>
      <h1>New Entity</h1>
    </div>
    <div><a href="menus.php">menus</a></div>
    <form name="formA">

      <div><button type="button" id="b2">Put date to table name</button></div>
      <div><div id="div2"><span class="xxxentityName"></span></div></div>
      <div><button type="button" class="add-button">Add field</button></div>
      <div><button type="button" id="okBtn">OK</button><button type="button">Cancel</button><button type="button">Clear</button></div>
      <div><button type="button" id="apiTestBtn">API Test</button></div>

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
              <th>Id</th>
              <th>Name</th>
              <th>Type</th>
            </tr>
          </thead>
          <tbody class="fields">
            <tr>
              <td>
                <span class="id"></span>
              </td>
              <td>
                <input type="text" class="field_name">
              </td>
              <td>
                <span class="field_type"></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </form>
    <div>
      <pre>
<?= $er ?>
<?= print_r($model, TRUE); ?>
      </pre>
    </div>
  </div>
  <script>
    var model = <?= json_encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>;
    
    var xo = new Trax.Xobject({
      // "entity": {
      //   "id": 0,
      //   "entity_name": ""
      // },
      "fields": [
        {
          "id": 0,
          "entity_id": 0,
          "field_name": "",
          "field_type": ""
        }
      ]
    });

    xo._each("fields", function (xitem) {
      xitem._transmit("id");
      xitem._bind("field_name");
      xitem._transmit("field_type");
    });

    xo.fields = model.fields;

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
    
    document.getElementById("apiTestBtn").addEventListener("click", function (event) {
      console.log("apiTestBtn clicked", null);

// const instance = axios.create({
//   baseURL: 'https://some-domain.com/api/',
//   timeout: 1000,
//   headers: {'X-Custom-Header': 'foobar'}
// });

// axios.post('/wagaya/api/test.php', 
axios.post('new.php', 
  xo
// {
//     firstName: 'Fred',
//     lastName: 'Flintstone'
//   }
)
  .then(function (response) {
    console.log(response);
  })
  .catch(function (error) {
    console.log(error);
  });


// axios.get('/wagaya/api/test.php')
//   .then(function(response) {
//     console.log(response.data);
//     console.log(response.status);
//     console.log(response.statusText);
//     console.log(response.headers);
//     console.log(response.config);
//   });      
    });


  </script>

</body>

</html>
