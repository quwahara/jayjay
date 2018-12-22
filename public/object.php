<?php (require __DIR__ . '/../jj/JJ.php')([
    'models' => [
        'object'
    ],
    'get' => function (\JJ\JJ $jj) {
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/brx/booq.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Lab</h1>
        </div>

        <div class="belt bg-mono-09">
            <div><a href="home.php">Home</a></div>
        </div>

        <div class="contents">
        </div>
        <div id="snackbar"></div>
    </div>
    <script>
    window.onload = function() {
        Global.snackbar("#snackbar");

        var data = <?= $jj->dataAsJSON() ?>;
        var booq = new Booq({
            message: "",
            io: data.models
        });
    };
    </script>
</body>
</html>
<?php

},
'post multipart/form-data' => function (\JJ\JJ $jj) {

},
]);
?>
