<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        //
        'tables[]' => [
            'part_properties'
        ],
    ],
    'refreshData' => function () {
        //
        $globals = $this->part()->findAllGlobals();
        $schemaId = null;
        if ($globals && count($globals) >= 1) {
            $path = "#{$globals[0]['id']}/system/schema";
            $schemaId = $this->part()->queryId($path);
        }
        $tables = [];
        if ($schemaId) {
            $tables = $this->part()->findAllPropertiesOrderByName($schemaId);
        }
        $this->data['tables'] = $tables;
    },
    'get' => function () {
        //
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
        <script src="js/booq/olbi.js"></script>
        <script src="js/lib/global.js"></script>
        <title>Schema</title>
        <script>
            var structs = new Olbi(<?= $this->structsAsJSON() ?>);
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Schema</h1>
            </div>

            <div class="belt neck">
                <div><a href="home.php">Home</a></div>
            </div>

            <div class="contents">
                <form method="post">
                    <div class="tables">
                        <div class="row">
                            <div class="col-3">
                                <span class="name label"></span>
                            </div>
                            <div class="col-3">
                                <a class="name data">Data</a>
                            </div>
                            <div class="col-3">
                                <a class="child_id descriptions">Descriptions</a>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.tables.each(function(element) {
                            this
                                .name.linkSimplex(".label").toText()
                                .name.linkSimplex(".data").toHref("data.php?name=:name")
                                // .child_id.linkSimplex(".data").toHref("data.php?id=:child_id")
                                .child_id.linkSimplex(".descriptions").toHref("descriptions.php?id=:child_id");
                        });
                    </script>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {
                //
                structs.setData(<?= $this->dataAsJSON() ?>);
            };
        </script>
    </body>

    </html>
<?php

},
'post application/json' => function () {


    $this->data['status'] = 'OK';
}
]);
?>