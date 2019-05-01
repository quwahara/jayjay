<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'command' => '',
        'delete' => '',
        'download' => '',
    ],
    'refreshData' => function () {
        return $this;
    },
    'get' => function () {
        $this->refreshData();
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
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Download Part dump</title>
        <script>
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Download Part dump</h1>
            </div>

            <div class="belt neck">
                <a href="home.php">Home</a>
            </div>

            <div class="contents">
                <form name="form1" method="post">
                    <input name="command" type="hidden">
                    <script>
                        structs.command.withValue();
                    </script>
                    <div class="row">
                        <div class="col-12">
                            <button name="download" type="button">Download</button>
                        </div>
                        <script>
                            structs.download.on("click", function() {
                                Global.modal.showMessage({
                                    body: "ダウンロードを開始してもよろしいですか",
                                    ok: {
                                        onclick: function() {
                                            structs.data.command = "download";
                                            document.form1.submit();
                                        }
                                    }
                                });
                            });
                        </script>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button name="delete" type="button">Delete all part</button>
                        </div>
                        <script>
                            structs.delete.on("click", function() {
                                Global.modal.showMessage({
                                    body: "すべてのデータが削除されます。本当によろしいですか",
                                    ok: {
                                        onclick: function() {
                                            structs.data.command = "delete";
                                            axios.post("part-download.php", structs.data)
                                                .then(function(response) {
                                                    structs.data = response.data;
                                                })
                                                .catch(Global.catcher(structs.data));
                                        }
                                    }
                                });
                            });
                        </script>
                    </div>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {};
        </script>
    </body>

    </html>
<?php

},
'post application/x-www-form-urlencoded' => function () {
    $this->downloadJsonData = $this->part()->dump();
    $this->downloadJsonFilename = 'part_dump.json';
},
'post application/json' => function () {
    if ($this->data['command'] === 'delete') {
        $this->part()->deleteAll();
    }
    $this->refreshData();
    $this->data['status'] = 'OK';
},
]);
?>