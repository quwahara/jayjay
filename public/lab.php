<?php (require __DIR__ . '/../jj/JJ.php')([
    'models' => [
        'models[]' => [
            'name' => '',
            'createTableDDL' => ''
        ],
        'command' => [
            'command' => '',
            'args' => [
                ''
            ]
        ],
    ],
    'get' => function (\JJ\JJ $jj) {
        $models = [];
        foreach ($jj->dbdec_['tables'] as $table) {
            $models[] = [
                'tableName' => $table['tableName'],
                'createTableDDL' => $jj->dao($table['tableName'])->createTableDDL(),
                'dropTableDDL' => $jj->dao($table['tableName'])->dropTableDDL(),
            ];
        }
        $jj->data['io']['models'] = $models;
        $jj->data['io']['command'] = [
            'command' => '',
            'args' => []
        ];
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/brx/brx.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Lab</h1>
        </div>

        <div class="belt bg-mono-09">
            <div>&nbsp;</div>
        </div>

        <div class="contents">
            <div class="models">
                <div class="model">
                        <h3 class="tableName"></h3>
                        <div>
                            <form>
                                <input type="hidden" name="command" value="create">
                                <input type="hidden" name="tableName">
                                <button type="button">Create</button>
                            </form>
                            <form>
                                <input type="hidden" name="command" value="drop">
                                <input type="hidden" name="tableName">
                                <button type="button">Drop</button>
                            </form>
                        </div>
                        <pre class="createTableDDL">
                        </pre>
                        <pre class="dropTableDDL">
                        </pre>
                </div>
            </div>
        </div>
        <div id="snackbar"></div>
    </div>
    <script>
    window.onload = function() {

        var data = <?= $jj->dataAsJSON() ?>;
        var brx = new Brx({
            message: "",
            io: data.models
        });
        
        Global.snackbar("#snackbar");
        brx._.focus("message").text();

        brx.io._each("models", function (item) {
            item._
                .focus("tableName").text()
                .queryByName().attr("value")
                .query("button")
                .on("click", function (event) {
                    Global.snackbar.close();
                    brx.io.status = "";
                    axios.post("lab.php", new FormData(Brx.goUpParentByTagName(event.target, "form")))
                    .then(function (response) {
                        console.log(response.data);
                        brx.message = response.data.message;
                        if ("" !== brx.message) {
                            Global.snackbar.messageDiv.classList.add("warning");
                            Global.snackbar.maximize();
                        }

                        // if (response.data.io.status === "#login-succeeded") {
                        //     window.location.href = window.location.href.replace("/index.php", "/home.php");
                        // }
                        // brx.io = response.data.io;
                    })
                    .catch(Global.catcher(brx.io));
                })
                .focus("createTableDDL").text()
                .focus("dropTableDDL").text()
                ;
        });

        brx.io = data.io;
    };
    </script>
</body>
</html>
<?php

},
'post multipart/form-data' => function (\JJ\JJ $jj) {

    $command = $_POST['command'];
    $tableName = $_POST['tableName'];

    if (!in_array($command, ['create', 'drop'], true)) {
        $jj->data['message'] = 'Not supported command.';
        $jj->responseJsonThenExit();
    }

    $table = $jj->dao($tableName);
    if (is_null($table)) {
        $jj->data['message'] = 'Not defined table name.';
        $jj->responseJsonThenExit();
    }

    try {
        if ($command === 'create') {
            $ddl = $table->createTableDDL();
            $table->execute($ddl);
            $jj->data['message'] = 'OK';
            $jj->responseJsonThenExit();
        } else if ($command === 'drop') {
            $ddl = $table->dropTableDDL();
            $table->execute($ddl);
            $jj->data['message'] = 'OK';
            $jj->responseJsonThenExit();
        }
    } catch (\Throwable $th) {
        $jj->data['message'] = 'NG';
        $jj->data['detail'] = print_r($th, true);
        $jj->responseJsonThenExit();
    }

    $jj->data['message'] = 'NOP';
    $jj->responseJsonThenExit();
},
]);
?>
