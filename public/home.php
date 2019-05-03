<?php (require __DIR__ . '/../jj/JJ.php')([
    'models' => ['user'],
    'get' => function () {
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
        <title>Home</title>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Home</h1>
            </div>
            <div class="contents">
                <div><a href="logout.php">Log out</a></div>
                <div><a href="table-definition.php">Table definition</a></div>
                <div><a href="part-object.php">Root part</a></div>
                <div><a href="part-global.php">Part global</a></div>
                <div><a href="part-admin.php">Part admin</a></div>
                <div><a href="schema.php">Schema</a></div>
                <div><a href="lab.php">Lab</a></div>
                <div><a href="table-create-and-drop.php">Table create and drop</a></div>
                <div><a href="-e-forbidden.php">access_denied</a></div>
                <div><a href="-e-internal-server-error.php">internal_server_error</a></div>
                <div><a href="phpinfo.php">PHP Info</a></div>
            </div>

        </div>
        <script>
            window.onload = function() {

            };
        </script>
    </body>

    </html>
<?php

},
]);
?>