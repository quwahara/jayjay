<?php (require __DIR__ . '/j/JJ.php')([
    'models' => ['entity', 'fields[]'],
    'methods' => [
        'loadIO' => function (\J\JJ $jj, string $id) {
            $io = &$jj->data['io'];
            $io['entity'] = $jj->dao('entity')->attFindOneBy(['id' => $id]);
            if (isset($io['entity'])) {
                $io['fields'] = $jj->dao('fields')->attFindAllBy(['entity_id' => $id]);
            } else {
                $io['status'] = '#not-found-entity';
                $io['entity'] = $jj->data['models']['entity'];
                $io['fields'] = [];
            }
            return $io;
        }
    ],
    'get' => function (\J\JJ $jj) {
        $jj->methods['loadIO']($jj, $_GET['id']);
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
        <div class="text-right"><button id="statusCloseBtn" type="button" class="link">&times; Close</button></div>
        </div>

        <div class="belt bl-mono-06">
        <button type="button" id="okBtn">OK</button>
        </div>

        <div class="contents">
        <form name="formA">
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

        var vs = Brx.validations;
        var data = <?= $jj->json($jj->data); ?>;

        var b = new Brx({
            message: "",
            io: data["models"]
        });
        b._toText("message");

        b.io._showOn("status");
        b.io._after("status", function (value) {
            b.message = Global.getMsg(value);
        });

        b.io.entity._bind("entity_name", {
            "validations": [vs.lengthMinMax({min: 2, max: 6})],
        });

        b.io._each("fields", function (item) {
            item._toText("id");
            item._bind("field_name", {
                "validations": [vs.empty],
            });
            item._bind("field_type", {
                "validations": [vs.empty],
            });
        });

        b.io = data.io;

        Brx.on("click", "#statusCloseBtn", function (event) {
            b.io.status = "";
        });
        Brx.on("click", "#okBtn", function (event) {
            if (vs.isOk(b.io._validate())) {
                Global.modal.create({
                    ok: {
                        onclick: function (event) {
                            b.io.status = "";
                            axios.post('details.php', b.io)
                            .then(function (response) {
                                b.io = response.data.io;
                            })
                            .catch(function (error) {
                                if (error.response) {
                                    b.io.status = "#http-status-" + error.response.status;
                                } else if (error.request) {
                                    b.io.status = "#error-of-request";
                                } else {
                                    b.io.status = "#error-of-setting-up-requesting";
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
    $jj->beginTransaction();
    try {
        $inputs = $jj->readJson();
        $jj->dao('entity')->attUpdateById($inputs['entity']);
        $jj->methods['loadIO']($jj, $inputs['entity']['id']);
        $jj->data['io']['status'] = '#updated';
        // throw new Exception("test");
        $jj->commit();
        $jj->responseJson();
    } catch (Exception $e) {
        $jj->rollBack();
        $jj->data['io'] = $jj->data['models'];
        $jj->data['io']['status'] = '#error';
        $jj->responseJson(500);
    }
}
]);
?>
