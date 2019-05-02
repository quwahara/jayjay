<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        //
        'name' => '',
        'descriptions' => [
            'columns[]' => [
                '___id' => '',
                'name' => '',
                'type' => '',
            ],
        ],
    ],
    'refreshData' => function ($id) {
        //
        $data = &$this->data;
        $property = $this->part()->findProperty($id);
        $data['name'] = $property['name'];

        $table = $this->part()->get($id);

        if (is_null($table)) {
            throw new Exception("The table was not found. Table:{$property['name']}");
        }

        if (!array_key_exists('descriptions', $table)) {
            throw new Exception("The table record was invalid. Table:{$property['name']}");
        }

        $data['descriptions'] = $table['descriptions'];
    },
    'get' => function () {
        //
        $id = $this->getRequestAsInt('id', 0);
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
        <title>Descriptions</title>
        <script>
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1><span class="name"></span> <span>descriptions</span></h1>
            </div>
            <script>
                structs.name.toText();
            </script>

            <div class="belt neck">
                <a href="home.php">Home</a>
                <a href="schema.php">Schema</a>
            </div>

            <div class="contents">
                <form method="post">
                    <h2>Columns</h2>
                    <div class="descriptions columns">
                        <div class="row">
                            <div class="col-2 ___id"></div>
                            <div class="col-2 name"></div>
                            <div class="col-2 type"></div>
                        </div>
                    </div>
                    <script>
                        structs.descriptions.columns.each(function(element) {
                            this
                                .___id.toText()
                                .name.toText()
                                .type.toText();
                        });
                    </script>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {
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