<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Services\Services;

if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
  $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
  $media_type = $content_type[0];
  
  if ($media_type == 'application/json') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $S = Services::singleton();
      $da = Services::singleton()->da();

      $da->pdo->beginTransaction();
      try {
        $payload = json_decode(file_get_contents('php://input'), true);
        $entityT = $da->getTableByTableName('entities');
        $entityV = ['entity_name' => $payload['entityName']];
        $da->insert($entityT, $entityV);
  
        $sql = 'select * from entities where entity_name = :entity_name;';
        $entityRec = $da->findOne($entityT, $sql, $entityV);
  
        $entityId = $entityRec['id'];
        $fieldT = $da->getTableByTableName('fields');
        $fieldSql = 'select * from fields where entity_id = :entity_id and field_name = :field_name;';
        $fieldRecs = [];
        foreach ($payload['fields'] as $field) {
          $fieldV = [
            'entity_id' => $entityId,
            'field_name' => $field['fieldName'],
            'field_type' => $field['type'],
          ];
          $da->insert($fieldT, $fieldV);
          $fieldPrm = [
            'entity_id' => $entityId,
            'field_name' => $field['fieldName'],
          ];
          $fieldRec = $da->findOne($fieldT, $fieldSql, $fieldPrm);
          $fieldRecs []= $fieldRec;
        }
        
        $da->pdo->commit();
  
        $entityRec['fields'] = $fieldRecs;
        $payload = $entityRec;
  
      } catch (Exception $e) {
        
        $da->pdo->rollBack();
  
        $payload = [
          'exception' => $e
        ];
      }
    } else {
      $payload = [
        "aa" => "11",
        "bb" => "22",
      ];
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($payload);
    exit();
  }
}

$er = '';
$dbg = 'aa';
try {
  $model = (function () {
    $S = Services::singleton();
    // Services::singleton()->initDbdec();
    // $S->da()->createTables();
    // return null;
    $da = $S->da();
    $sql = 'select * from entities where entity_name = :entity_name;';
    $vals = ['entity_name' => 'aaa'];
    $ret = $da->findOne($da->getTableByTableName('entities'), $sql, $vals);
    // $ret = print_r([
    //   'fields',
    //   $da->getTableByTableName('fields'),
    //    $ret,
    //   ], TRUE);
    return $ret;
  })();
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
    <div>
      <pre>
<?= $er ?>
<?= print_r($model, TRUE); ?>
      </pre>
    </div>
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
