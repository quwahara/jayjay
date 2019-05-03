<?php (require __DIR__ . '/../jj/JJ.php')([
    'init' => function () {
        $p = $this->part();
        if (is_null($p->findRoot())) {
            $p->addRoot();
        }
    },
    'structs' => [
        'command' => '',
        'upload' => '',
        'download' => '',
        'initialize' => '',
        'results' => [
            'part' => 0,
            'property' => 0,
            'item' => 0,
        ],
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
        <title>Part admin</title>
        <script>
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Part admin</h1>
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
                            <input name="file" type="file" accept="application/json,.json">
                        </div>
                        <script>
                        </script>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button name="upload" type="button">Upload</button>
                        </div>
                        <script>
                            structs.upload.on("click", function() {
                                Global.modal.showMessage({
                                    body: "アップロードを開始してもよろしいですか",
                                    ok: {
                                        onclick: function() {
                                            var data = new FormData();
                                            data.append('command', 'upload');
                                            data.append('file', document.querySelector("[name='file']").files[0]);
                                            axios.post("part-admin.php", data)
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
                            <button name="initialize" type="button">Initialize</button>
                        </div>
                        <script>
                            structs.initialize.on("click", function() {
                                Global.modal.showMessage({
                                    body: "すべてのデータが削除されます。本当によろしいですか",
                                    ok: {
                                        onclick: function() {
                                            structs.data.command = "initialize";
                                            axios.post("part-admin.php", structs.data)
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
'post multipart/form-data' => function () {

    $this->doResponseJson = true;

    $fileError = $_FILES['file']['error'];

    if ($fileError !== UPLOAD_ERR_OK) {
        $this->data['status'] = 'NG';
        return;
    }

    $contents = file_get_contents($_FILES['file']['tmp_name']);
    if ($contents === FALSE) {
        $this->data['status'] = 'NG';
        return;
    }

    $dump = json_decode($contents, true);

    $this->data['results'] = $this->part()->load($dump);
},
'post application/x-www-form-urlencoded' => function () {
    $this->downloadJsonData = $this->part()->dump();
    $this->downloadJsonFilename = 'part_dump.json';
},
'post application/json' => function () {
    $p = $this->part();

    if ($this->data['command'] === 'initialize') {
        $p->deleteAll();
        $p->addRoot();
    }
    $this->refreshData();
    $this->data['status'] = 'OK';
},
]);
?>