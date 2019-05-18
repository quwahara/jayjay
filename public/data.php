<?php (require __DIR__ . '/../jj/JJ.php')([
    'before' => function () {
        // 
    },
    'structs' => [
        'name' => '',
        // 'columns[]' => [
        //     'name' => '',
        //     'type' => '',
        // ],
    ],
    'refreshData' => function ($name) {

        $data = &$this->data;
        $data['name'] = $name;
        $columns = $this->part()->query("/system/schema/{$name}/descriptions/columns");
        // $data['columns'] = $columns;

        $recordStructure = ['___id' => 0];
        foreach ($columns as $column) {
            $recordStructure[$column['name']] = '';
        }

        $this->initStructs([
            'records[]' => $recordStructure,
        ]);

        $data['records'] = $this->part()->query("/system/schema/{$name}/data");
        if (is_null($data['records'])) {
            $data['records'] = [];
        }
    },
    'get' => function () {
        //
        $name = $this->getRequest('name');
        $this->refreshData($name);

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
            var structs = new Booq(<?= $this->structsAsJSON() ?>).traceStructure();
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1><span class="name"></span> <span>data</span></h1>
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
                    <h2>Records</h2>
                    <div class="records">
                        <div class="row">
                            <div class="col-2">
                                <a class="none"></a>
                                <span class="none"></span>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.records.each(function(element, i, value) {
                            console.log(this, element, i, value);

                            this.linkExtra(".row").each(function(element, nth, name, value) {
                                console.log(this, element, name, value);
                                this.nameToClass();
                                if (name === '___id') {
                                    this.linkExtra(" a").valueToText("→");
                                    this.linkExtra(" a").toHref("detail.php?id=:___id");
                                    this.linkExtra(" a").antitogglesClass("none");
                                } else {
                                    this.linkExtra(" span").toText();
                                    this.linkExtra(" span").antitogglesClass("none");
                                }
                            }).traceQualify();
                        }).traceQualify();
                    </script>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {
                structs.setData(<?= $this->dataAsJson() ?>).traceSetData();
            };
        </script>
    </body>

    </html>
<?php

},
'post application/json' => function () {

    $data = &$this->data;


    $this->data['status'] = 'OK';
}
]);
?>