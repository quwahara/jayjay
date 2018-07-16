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
            $entityV = '';
            try {
                $payload = json_decode(file_get_contents('php://input'), true);
                $model = $payload['model'];
                $entityT = $da->getTableByTableName('entities');
                $entityV = $model['entity'];

                $da->updateById($da->attachTableNameAndTypes($entityT, $entityV));

                $fieldsT = $da->getTableByTableName('fields');
                $fieldsVs = $model['fields'];
                foreach ($fieldsVs as $fieldsV) {
                  $da->updateById($da->attachTableNameAndTypes($fieldsT, $fieldsV));
                }

                $da->pdo->commit();

            } catch (Exception $e) {

                $da->pdo->rollBack();

                $payload = [
                    'exception' => print_r($e, true),
                ];
            }
        } else {
            $payload = [
              'message' => 'Not supported request method.',
              'request_method' => $_SERVER['REQUEST_METHOD'],
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
            "fields" => $fields,
        ];
    })($_GET['id']);
} catch (Exception $e) {
    $er = print_r($e, true);
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

    <div class="belt">
      <h1>Details</h1>
    </div>

    <div class="belt bg-mono-09">
      <a href="menus.php">menus</a>
    </div>

    <div class="belt status">
      <div class="message">
      Info Info Info Info Info Info Info Info <br>
        Info Info Info Info Info Info Info Info <br>
        Info Info Info Info Info Info Info Info <br>
      </div>
    </div>

    <div class="belt info">
      <div>
      Info Info Info Info Info Info Info Info <br>
      Info Info Info Info Info Info Info Info <br>
      Info Info Info Info Info Info Info Info <br>
      </div>
    </div>

    <div class="belt success">
      <div>
      Success Success Success Success Success Success Success Success <br>
      Success Success Success Success Success Success Success Success <br>
      Success Success Success Success Success Success Success Success <br>
      </div>
    </div>

    <div class="belt warning">
      <div>
      Warning Warning Warning Warning Warning Warning Warning Warning <br>
      Warning Warning Warning Warning Warning Warning Warning Warning <br>
      Warning Warning Warning Warning Warning Warning Warning Warning <br>
      </div>
    </div>

    <div class="belt error">
      <div>
      Error Error Error Error Error Error Error Error <br>
      Error Error Error Error Error Error Error Error <br>
      Error Error Error Error Error Error Error Error <br>
      </div>
    </div>

    <div class="belt bl-mono-06">
      <button type="button" id="okBtn2">OK</button>
      <button type="button">Cancel</button>
      <button type="button">Clear</button>
      <button type="button" id="statusBtn">Status Test</button>
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
          <span class="msg _entity_name" show-on="length-min-max"><!-- #length-min-max { "min": 2, "max": 6 } --></span>
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
                    <select class="field_type" set-class-on="error warn">
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
    </div>
  </div>
  <script>
    window.onload = function () {
      Global.putMsgs();

      var model = <?=json_encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)?>;

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
        },
        "notice": {
          "status": "",
          "message": "",
        }
      });

      var vs = Trax.validations;

      xo.notice._showOn("status");
      xo.notice._transmitToClass("status");
      xo.notice._transmitToHtml("message");

      xo.model.entity._bind("entity_name", {
        "validations": [vs.lengthMinMax({min: 2, max: 6})],
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

      console.log(model);

      xo.model = model;

      document.getElementById("statusBtn").addEventListener("click", function (event) {
        console.log(new Date().toISOString());
        xo.notice.status = xo.notice.status ? "" : "success";
        xo.notice.message = !xo.notice.status ? "" : "message message message";
      });

      document.getElementById("okBtn2").addEventListener("click", function (event) {
        console.log(new Date().toISOString());
        if (vs.isOk(xo._validate())) {
          console.log("ok");

          Global.modal.create({
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


        } else {
          console.log("ng");
        }
      });

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

    };
  </script>
  </div>
</body>

</html>
