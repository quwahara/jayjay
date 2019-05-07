<?php (require __DIR__ . '/../jj/JJ.php')([
    'before' => function () {
        //
    },
    'structs' => [
        'name' => '',
        'columns[]' => [
            'name' => '',
            'type' => '',
        ],
        '___id' => 0,
        'register' => '',
        //
    ],
    'refreshData' => function ($id) {
        //
        $data = &$this->data;

        $record = $this->part()->get($id);
        if (is_null($record)) {
            throw new Exception("The id was not found.");
        }
        $data['___id'] = $record['___id'];
        $data['record'] = $record;

        $path = $this->part()->path($id);
        if (empty($path)) {
            throw new Exception("The id was not found.");
        }

        if (count($path) < 3) {
            throw new Exception("The path was invlid.");
        }

        if ($path[2]['name'] !== 'schema') {
            throw new Exception("The path was invlid.");
        }

        $name = $path[3]['name'];
        $data['name'] = $name;

        $columns = $this->part()->query("/system/schema/{$name}/descriptions/columns");
        $data['columns'] = $columns;

        $recordStructure = [];
        foreach ($columns as $column) {
            $recordStructure[$column['name']] = '';
        }

        $this->initStructs([
            'record' => $recordStructure,
        ]);
    },
    'get' => function () {
        //
        $id = $this->getRequest('id');
        $this->refreshData($id);
        ?>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="data:,">
        <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
        <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
        <link rel="stylesheet" type="text/css" href="css/global.css">
        <script src="js/lib/node_modules/axios/dist/axios.js"></script>
        <script src="js/booq/booq.js"></script>
        <script src="js/lib/global.js"></script>
        <title>Data</title>
        <script>
            Booq.configure({
                traceStructure: true,
                traceQualify: true,
                traceSetData: true,
            });
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1><span class="name"></span> <span>data</span></h1>
            </div>
            <script>
                structs.extent(".head").name.toText().end;
            </script>

            <div class="belt neck">
                <a href="home.php">Home</a>
                <a href="schema.php">Schema</a>
                <a class="name">Data</a>
                <script>
                    structs.extent(".neck").name.toHref("data.php?name=:name").end;
                </script>
            </div>

            <div class="contents">
                <form method="post">
                    <div class="ident">
                        <div class="row">
                            <div class="col-2">
                                <label>Id</label>
                                <div class="___id"></div>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.extent(".ident").___id.toText().end;
                    </script>

                    <div class="record">
                        <div class="row">
                            <div class="col-2">
                                <label></label>
                                <input class="input" type="text">
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.record.each(function(element, name, value) {
                            this.nameToClass();
                            this.linkExtra(" label").nameToText();
                            this.linkExtra(" label").nameToAttr("for");
                            this.linkExtra(" input").nameToAttr("name");
                            this.linkExtra(" input").withValue();
                        });
                    </script>

                    <div class="row">
                        <div class="col-12">
                            <button name="register" type="button">Register</button>
                        </div>
                    </div>
                    <script>
                        structs.register.on("click", function(event) {
                            //
                            Global.modal.create({
                                    body: "保存します。よろしいですか",
                                    ok: {
                                        onclick: function() {
                                            console.log(structs.data);
                                            axios.post("detail.php", structs.data)
                                                .then(function(response) {
                                                    console.log(response.data);
                                                    structs.setData(response.data);
                                                    // structs.data = response.data;
                                                    // structs.data.message = response.data.message;
                                                    // if ("" !== structs.data.message) {
                                                    //     Global.snackbar.messageDiv.classList.add("warning");
                                                    //     Global.snackbar.maximize();
                                                    // }
                                                })
                                                .catch(Global.catcher(structs.data));
                                        }
                                    }
                                })
                                .open();
                        });
                    </script>

                </form>
            </div>
        </div>
        <script>
            window.onload = function() {
                structs.setData(<?= $this->dataAsJson() ?>);
            };
        </script>
    </body>

    </html>
<?php

},
'post application/json' => function () {

    $data = &$this->data;

    $this->part()->setPrimitiveValueToProperty($data['___id'], 'username', $data['record']['username']);
    $this->part()->setPrimitiveValueToProperty($data['___id'], 'password', $data['record']['password']);

    $this->refreshData($data['___id']);

    $this->data['status'] = 'OK';
}
]);
?>