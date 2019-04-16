<?php (require __DIR__ . '/../jj/JJ.php')([
    'access' => 'public',
    'get' => function () {
        $this->logout();
        ?>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/booq/booq.js"></script>
    <script src="js/lib/global.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logout</title>
</head>

<body>
    <div>
        <div class="belt">
            <h1>Logout</h1>
        </div>

        <div class="belt bg-mono-09">
            <div>&nbsp;</div>
        </div>

        <div class="contents">
            <div>Logged out</div>
            <div>Go to <a href="index.php"> Index page</a></div>
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