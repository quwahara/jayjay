<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        //
    ],
    'refreshData' => function () {
        //
        $x = $this->part()->findAllGlobals();
        if ($x && count($x) >= 1) {
            $path = "#{$x[0]['id']}/system/schema";
        }
        $s = '';
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
        <title>Lab</title>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Lab</h1>
            </div>

            <div class="belt neck">
                <div><a href="home.php">Home</a></div>
            </div>

            <div class="contents">
                <form method="post">
                    <div class="row">
                        <div class="col-12">xxx</div>
                    </div>
                </form>
            </div>
            <div id="snackbar"></div>
        </div>
        <script>
            window.onload = function() {
                //


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