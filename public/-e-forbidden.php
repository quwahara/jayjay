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
        <meta http-equiv="refresh" content="5;URL=<?= $this->config_['login']['redirect_path'] ?>">
        <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
        <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
        <link rel="stylesheet" type="text/css" href="css/global.css">
        <script src="js/lib/node_modules/axios/dist/axios.js"></script>
        <script src="js/booq/olbi.js"></script>
        <script src="js/lib/global.js"></script>
        <title>Forbidden</title>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Forbidden</h1>
            </div>

            <div class="belt neck">
                <div>&nbsp;</div>
            </div>

            <div class="contents">
                <p>Your access was forbedden.</p>
                <p>This page will jump to <a href="<?= $this->config_['login']['redirect_path'] ?>">Index page</a> in 5 seconds.</p>
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