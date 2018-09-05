<?php (require __DIR__ . '/j/JJ.php')([
    'models' => ['context', 'entity', 'fields[]'],
    'methods' => [
        'loadPayloads' => function (\J\JJ $jj, string $id) {
            $payloads = &$jj->data['payloads'];
            $payloads['entity'] = $jj->dao('entity')->attFindOneBy(['id' => $id]);
            if (isset($payloads['entity'])) {
                $payloads['fields'] = $jj->dao('fields')->attFindAllBy(['entity_id' => $id]);
            } else {
                $payloads['context']['status'] = 'not-found-entity';
                $payloads['entity'] = $jj->data['models']['entity'];
                $payloads['fields'] = [];
            }
            return $payloads;
        }
    ],
    'get' => function (\J\JJ $jj) {
        $jj->methods['loadPayloads']($jj, $_GET['id']);
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/brx/brx.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Details</title>
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
            <label for="entity_name">entity_name</label>
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

        var local = new Brx({message: ""});
        local._toText("message");

        var vs = Brx.validations;
        var data = <?= $jj->json($jj->data); ?>;
        var io = new Brx({"payloads": data["models"]});

        io.payloads.context._showOn("status");
        io.payloads.context._after("status", function (value) {
            local.message = Global.getMsg(value);
        });

        io.payloads.entity._bind("entity_name", {
            "validations": [vs.lengthMinMax({min: 2, max: 6})],
        });

        io.payloads._each("fields", function (item) {
            item._toText("id");
            item._bind("field_name", {
                "validations": [vs.empty],
            });
            item._bind("field_type", {
                "validations": [vs.empty],
            });
        });

        io.payloads = data.payloads;

        Brx.on("click", "#okBtn2", function (event) {
            console.log(new Date);
            
            if (vs.isOk(io._validate())) {
                console.log("ok");

                Global.modal.create({
                    ok: {
                        onclick: function (event) {
                            io.payloads.context.status = "";
                            axios.post('details.php', io.payloads)
                            .then(function (response) {
                                io.payloads = response.data.payloads;
                                console.log(response);
                            })
                            .catch(function (error) {
                                io.notice.status = "error";
                                io.notice.message = "The update failed.";
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

    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function (\J\JJ $jj) {
    $inputs = $jj->readJson();
    $jj->dao('entity')->attUpdateById($inputs['entity']);
    $jj->methods['loadPayloads']($jj, $inputs['entity']['id']);
    $jj->data['payloads']['context']['status'] = '#updated';
    $jj->responseJson();
}
]);
?>