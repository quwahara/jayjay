<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
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
                                            document.form1.submit();
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
]);
?>