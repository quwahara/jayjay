<?php (require __DIR__ . '/../../jj/JJ.php')([
    // Giving permission to access without logged in
    'access' => 'public',
    'models' => [],
    'get' => function () {
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="../css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="../css/global.css">
    <script src="../js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="../js/brx/brx.js"></script>
    <script src="../js/lib/global.js"></script>
    <?= '<style>' . $this->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Internal server error</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Internal server error</h1>
        </div>

        <div class="belt bg-mono-09">
            <div>&nbsp;</div>
        </div>

        <div class="contents">
            <p>Internal server error</p>
        </div>

    </div>
    <script>
    window.onload = function() {
    };
    </script>
    <div id="snackbar"></div>
</body>
</html>
<?php

},
]);
?>
