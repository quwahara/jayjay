<?php (require __DIR__ . '/j/JJ.php')([
    'models' => ['entities', 'fields'],
    'get' => function (\J\JJ $jj) {

    $entitiesDAO = $jj->daos['entities'];
    $entity = $entitiesDAO->findOneBy($entitiesDAO->attachTypes(['id' => $_GET['id']]));
    
    $fieldsDAO = $jj->daos['fields'];
    $fields = $fieldsDAO->findAllBy($fieldsDAO->attachTypes(['entity_id' => $entity['id']]));


    
    $jj->responseJson();


        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/trax/trax.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
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
        <div class="message"></div>
        <div class="text-right"><button id="statusColseBtn" type="button" class="link">&times; Close</button></div>
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
    </div>
    <script>
    window.onload = function() {
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
xo.notice._toClass("status");
xo.notice._toHtml("message");

xo.model.entity._bind("entity_name", {
  "validations": [vs.lengthMinMax({min: 2, max: 6})],
});
xo.model._each("fields", function (xitem) {
  xitem._toText("id");
  xitem._bind("field_name", {
    "validations": [vs.empty],
  });
  xitem._bind("field_type", {
    "validations": [vs.empty],
  });
});

console.log(model);

xo.model = model;

document.getElementById("statusColseBtn").addEventListener("click", function (event) {
  console.log("statusColseBtn", new Date().toISOString());
  xo.notice.status = "";
});

document.getElementById("statusBtn").addEventListener("click", function (event) {
  console.log(new Date().toISOString());
  xo.notice.status = xo.notice.status ? "" : "error";
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
            xo.notice = response.data.notice;
          })
          .catch(function (error) {
            xo.notice.status = "error";
            xo.notice.message = "The update failed.";
            if (error.response) {
              console.log(error.response);
            } else if (error.request) {
              console.log(error.request);
            } else {
              console.log('Error', error.message);
            }
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
</body>
</html>
<?php

},
'post application/json' => function (\J\JJ $jj) {
    // $inputUser = $jj->readJson()['models']['user'];

    // $usersDAO = $jj->daos['users'];
    // $user = $usersDAO->findOneBy($usersDAO->attachTypes(['name' => $inputUser['name']]));

    // if ($user && password_verify($inputUser['password'], $user['password'])) {
    //     $jj->data['notice']['status'] = 'success';
    // } else {
    //     $jj->data['notice']['status'] = 'fail';
    // }

    // $jj->responseJson();
}
]);
?>
