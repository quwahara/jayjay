<?php (require __DIR__ . '/../jj/JJ.php')([
    // Giving permission to access without logged in
    'access' => 'public',
    'models' => [],
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
        <title>Internal server error</title>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Internal server error</h1>
            </div>

            <div class="belt neck">
                <div>&nbsp;</div>
            </div>

            <div class="contents">
                <p>Internal server error</p>
            </div>

        </div>
        <script>
            window.onload = function() {};
        </script>
        <div id="snackbar"></div>
    </body>

    </html>
<?php

},
]);
?>