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
        $schema = null;
        if ($globals && count($globals) >= 1) {
            $path = "#{$globals[0]['id']}/system/schema";
            $schema = $this->part()->query($path);
        }
        $tables = [];
        if ($schema) {
            $tables = $this->part()->findAllPropertiesOrderByName($schema['part']['id']);
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
        <script src="js/booq/booq.js"></script>
        <script src="js/lib/global.js"></script>
        <title>Schema</title>
        <script>
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
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
                            <div class="col-12 name"></div>
                        </div>
                    </div>
                    <script>
                        structs.tables.each(function(element) {
                            this.name.toText();
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