<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Services\Services;
use Services\ViewService;

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
try {
  $model = (function ($id) {
    $S = Services::singleton();
    $da = $S->da();

    $sql = 'select * from entities where id = :id;';
    $vals = ['id' => $id];
    $entity = $da->findOne($da->getTableByTableName('entities'), $sql, $vals);

    $sql = 'select * from fields where entity_id = :entity_id;';
    $vals = ['entity_id' => $entity['id']];
    $fields = $da->findAll($da->getTableByTableName('fields'), $sql, $vals);

    return [
      "id" => $id,
      "entity" => $entity,
      "fields" => $fields
    ];
  })($_GET['id']);
} catch (Exception $e) {
  $er = print_r($e, TRUE);
}
?>
<html>

<head>
  <link rel="stylesheet" type="text/css" href="../../../js/lib/node_modules/normalize.css/normalize.css">
  <link rel="stylesheet" type="text/css" href="../../../css/global.css">
  <script src="../../../js/lib/node_modules/axios/dist/axios.js"></script>
  <script src="../../../js/trax/trax.js"></script>
  <script src="../../../js/lib/global.js"></script>
  <?php
  echo '<style>'
  . Services::singleton()->css()->style
  . '</style>';
  ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
  <div>
    
    <div class="belt1">
      <h1>Details</h1>
    </div>
    
    <div class="belt2">
      <a href="menus.php">menus</a>
    </div>
    
    <div class="belt3">
      <button type="button" id="okBtn2">OK</button>
      <button type="button">Cancel</button>
      <button type="button">Clear</button>
    </div>

    <div class="contents">
      <form name="formA">

        <div><button type="button" id="b2">Put date to table name</button></div>
        <div><div id="div2"><span class="xxxentityName"></span></div></div>
        <div><button type="button" class="add-button">Add field</button></div>
        <div><button type="button" id="okBtn">OK</button><button type="button">Cancel</button><button type="button">Clear</button></div>
        <div><button type="button" id="apiTestBtn">API Test</button></div>
        <div><button type="button" id="JSONTestBtn">JSON Test</button></div>
        <div><button type="button" id="updateBtn">Update Test</button></div>
        <div><button type="button" id="modalBtn">Modal Test</button></div>

        <div>
          <lable for="entity_name">entity_name</label>
          <input type="text" class="entity_name" set-class-on="error warn" name="entity_name">
          <span class="msg _entity_name" show-on="empty"><!-- #please-input --></span>
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
                  <div><span class="id"></span></div>
                  <div class="ph"></div>
                </td>
                <td>
                  <div>
                    <input type="text" class="field_name" set-class-on="error warn">
                    <div class="ph"><span class="msg _field_name" show-on="empty"><!-- #please-input --></span></div>
                  </div>
                </td>
                <td>
                  <div>
                    <select class="field_type">
                      <option value=""></option>
                      <option value="Text">Text</option>
                      <option value="Number">Number</option>
                    </select>
                    <div class="ph"><span class="msg _field_type" show-on="empty"><!-- #please-input --></span></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </form>
    </div>
    <div>
      <pre>
<?= $er ?>
<?= print_r($model, TRUE); ?>
      </pre>
    </div>
  </div>
  <script>
    window.onload = function () {
      Global.putMsgs();
    };

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

    var vs = Trax.validations;

    xo.model.entity._bind("entity_name", {
      "validations": [vs.empty],
    });
    xo.model._each("fields", function (xitem) {
      xitem._transmit("id");
      xitem._bind("field_name", {
        "validations": [vs.empty],
      });
      xitem._bind("field_type", {
        "validations": [vs.empty],
      });
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

    document.getElementById("okBtn2").addEventListener("click", function (event) {
      xo._validate();
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

    document.getElementById("modalBtn").addEventListener("click", function (event) {
      console.log("updateBtn clicked", null);
      Global.modal.create({
        // "header": "<h2>Confirmation</h2>",
        // "body": "<p>Are you sure?</p>",
        ok: {
          onclick: function (event) {
            axios.post('details.php', xo)
            .then(function (response) {
              console.log(response);
              xo.model = response.data.model;
            })
            .catch(function (error) {
              console.log(error);
            });
          }
        }
      }).open();
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
  </div>
</body>

</html>
