<?php (require __DIR__ . '/../jj/JJ.php')([
    'models' => ['user'],
    'get' => function () {
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/booq/booq.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $this->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Home</h1>
        </div>

        <div class="belt bg-mono-09">
            <div>&nbsp;</div>
        </div>

        <div class="contents">
            <div><a href="logout.php">Log out</a></div>
            <div><a href="table-definition.php">Table definition</a></div>
            <div><a href="part-global.php">Part global</a></div>
            <div><a href="part-list.php">Part list</a></div>
            <div><a href="lab.php">Lab</a></div>
            <div><a href="table-create-and-drop.php">Table create and drop</a></div>
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
