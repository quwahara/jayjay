<?php (require __DIR__ . '/../jj/JJ.php')([
    'models' => [
        'models[]' => [
            'name' => '',
            'createTable' => ''
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
            $models []= [
                'name' => $table['tableName'],
                'createTable' => $jj->dao($table['tableName'])->createTable(),
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
                <div>
                    <h3 class="name"></h3>
                    <div><button class="create">Create</button></div>
                    <pre class="createTable">
                    </pre>
                </div>
            </div>
        </div>
        <div id="snackbar"></div>
    </div>
    <script>
    window.onload = function() {
        Global.snackbar("#snackbar");

        var data = <?= $jj->dataAsJSON() ?>;
        var brx = new Brx({
            io: data.models
        });

        brx.io._each("models", function (item) {
            item._toText("name");
            item._toAttr("name", "id", {query: "button.create"});
            Brx.on("click", "#" + item.name, function(event) {
                // alert(event.target.id);

                Global.snackbar.close();
                brx.io.status = "";
                brx.io.command.command = "create";
                brx.io.command.args = [event.target.id];
                axios.post("lab.php", brx.io)
                .then(function (response) {
                    console.log(response.data);
                    // if (response.data.io.status === "#login-succeeded") {
                    //     window.location.href = window.location.href.replace("/index.php", "/home.php");
                    // }
                    // brx.io = response.data.io;
                })
                .catch(Global.catcher(brx.io));


            });
            item._toText("createTable");
        });

        brx.io = data.io;
    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function (\JJ\JJ $jj) {
    //TODO post from browser

    $jj->data['io'] = $jj->readJson();
    // $user = $jj->dao('user')->attFindOneBy(['name' => $jj->data['io']['user']['name']]);
    // if ($user && password_verify($jj->data['io']['user']['password'], $user['password'])) {
    //     $jj->login(['user_id' => $user['name']]);
    //     $jj->data['io']['status'] = '#login-succeeded';
    // } else {
    //     $jj->data['io']['status'] = '#login-failed';
    // }
    $jj->responseJson();
}
]);
?>
