<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'models[]' => [
            'tableName' => '',
            'createTableDDL' => '',
            'dropTableDDL' => '',
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
        $jj->data['models'] = $models;
        $jj->data['command'] = [
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
    <script src="js/brx/booq.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Table create and drop</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Table create and drop</h1>
        </div>

        <div class="belt bg-mono-09">
            <div><a href="home.php">Home</a></div>
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
        Global.snackbar("#snackbar");

        var booq = new Booq(<?= $jj->structsAsJSON() ?>);

        booq
        .message.toText()
        .models.each(function (elem) {
            this
            .tableName.toText()
            .tableName.withValue()
            .createTableDDL.toText()
            .dropTableDDL.toText()
            ;

            Booq.q(elem).q("button").on("click", function () {
                Global.snackbar.close();
                booq.data.status = "";
                axios.post("table-create-and-drop.php", new FormData(Booq.goUpParentByTagName(event.target, "form")))
                .then(function (response) {
                    console.log(response.data);
                    booq.data.message = response.data.message;
                    if ("" !== booq.data.message) {
                        Global.snackbar.messageDiv.classList.add("warning");
                        Global.snackbar.maximize();
                    }
                })
                .catch(Global.catcher(booq.data));
            })
            ;
        });

        booq.data = <?= $jj->dataAsJSON() ?>;
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
        $jj->data['detail'] = $th;
        $jj->responseJsonThenExit();
    }

    $jj->data['message'] = 'NOP';
    $jj->responseJsonThenExit();
},
]);
?>
