<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Services\Services;


if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
  $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
  $media_type = $content_type[0];
  
  if ($media_type == 'application/json') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $S = Services::singleton();
      $da = $S->da();

      $da->pdo->beginTransaction();
      $q = '';
      $entityV = '';
      try {
        $payload = json_decode(file_get_contents('php://input'), true);
        $entityT = $da->getTableByTableName('entities');
        $entityV = $payload['model']['entity'];
        $entityW = 'id = :id__p1__';
        $entityWP = ['id__p1__' => $payload['model']['entity']['id']];

        $q = $da->update2($entityT, $entityV, $entityW);
        $da->update($entityT, $entityV, $entityW, $entityWP);

        // $entityV = ['entity_name' => $payload['entityName']];
        // $da->insert($entityT, $entityV);
  
        // $sql = 'select * from entities where entity_name = :entity_name;';
        // $entityRec = $da->findOne($entityT, $sql, $entityV);
  
        // $entityId = $entityRec['id'];
        // $fieldT = $da->getTableByTableName('fields');
        // $fieldSql = 'select * from fields where entity_id = :entity_id and field_name = :field_name;';
        // $fieldRecs = [];
        // foreach ($payload['fields'] as $field) {
        //   $fieldV = [
        //     'entity_id' => $entityId,
        //     'field_name' => $field['fieldName'],
        //     'field_type' => $field['type'],
        //   ];
        //   $da->insert($fieldT, $fieldV);
        //   $fieldPrm = [
        //     'entity_id' => $entityId,
        //     'field_name' => $field['fieldName'],
        //   ];
        //   $fieldRec = $da->findOne($fieldT, $fieldSql, $fieldPrm);
        //   $fieldRecs []= $fieldRec;
        // }
        
        $da->pdo->commit();
  
        // $entityRec['fields'] = $fieldRecs;
        // $payload = $entityRec;
  
      } catch (Exception $e) {
        
        $da->pdo->rollBack();
  
        $payload = [
          // 'exception' => $e
          'q' => $q,
          'vs' => $entityV,
          'exception' => print_r($e, TRUE)
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
$dbg = '';
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
      <div><button type="button" id="JSONTestBtn">JSON Test</button></div>
      <div><button type="button" id="updateBtn">Update Test</button></div>

      <div>Entity name</div>
      <div>
        <input type="text" class="entity_name">
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
                <select class="field_type">
                  <option>Text</option>
                  <option>Number</option>
                </select>
                <span class="xfield_type"></span>
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
      "model": {
        "entity": {
          "id": 0,
          "entity_name": ""
        },
        "fields": [
          {
            "id": 0,
            "entity_id": 0,
            "field_name": "",
            "field_type": ""
          }
        ]
      }
    });

    xo.model.entity._bind("entity_name");
    xo.model._each("fields", function (xitem) {
      xitem._transmit("id");
      xitem._bind("field_name");
      xitem._bind("field_type");
    });
    
    xo.model = model;

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
    
    document.getElementById("JSONTestBtn").addEventListener("click", function (event) {
      console.log("JSONTestBtn clicked", JSON.stringify(xo, null, 2));
    });
    
    document.getElementById("updateBtn").addEventListener("click", function (event) {
      console.log("updateBtn clicked", null);
      axios.post('details.php', xo)
      .then(function (response) {
        console.log(response);
      })
      .catch(function (error) {
        console.log(error);
      });
    });


    document.getElementById("apiTestBtn").addEventListener("click", function (event) {
      console.log("apiTestBtn clicked", null);
      axios.post('new.php', xo)
      .then(function (response) {
        console.log(response);
      })
      .catch(function (error) {
        console.log(error);
      });
    });


  </script>

</body>

</html>
