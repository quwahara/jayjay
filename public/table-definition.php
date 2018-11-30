<?php (require __DIR__ . '/../jj/JJ.php')([
    'models' => [
        'models[]' => [
            'name' => '',
            'createTable' => ''
        ]
    ],
    'get' => function (\JJ\JJ $jj) {
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/brx/brx.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Table definition</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Table definition</h1>
        </div>

        <div class="belt bg-mono-09">
            <div>&nbsp;</div>
        </div>

        <div class="contents">
            TBD
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
