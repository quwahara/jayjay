<?php (require __DIR__ . '/j/JJ.php')([
    'models' => ['users'],
    'get' => function (\J\JJ $jj) {
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/brx/brx.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Login</h1>
        </div>

        <div class="belt bg-mono-09">
            <a href="menus.php">menus</a>
        </div>

        <div class="belt hide status">
            <div class="message"></div>
            <div class="text-right"><button id="statusColseBtn" type="button" class="link">&times; Close</button></div>
        </div>

        <div class="belt bl-mono-06">
            <button type="button">OK</button>
        </div>

        <div class="contents">
            <form name="theForm" method="post">
            <div class="row">
                <label for="name">Username</label>
                <input type="text" name="name" id="name" class="name">
            </div>
            <div class="row">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="password">
            </div>
            <div class="row">
                <div class="label"></div>
                <button type="button" id="loginBtn">Login</button>
            </div>
            </form>
        </div>
    </div>
    <script>
    window.onload = function() {
        var data = <?= $jj->dataJSON ?>;

        var vs = Brx.validations;
        var tx = new Brx({
            "models": {
                "user": {
                    "name": "",
                    "password": ""
                },
            },
            "notice": {
                "status": "",
                "message": "",
            },
        });

        tx.models.user._bind("name", {
            "validations": [vs.lengthMinMax({min: 1, max: 6})],
        });
        tx.models.user._bind("password", {
            "validations": [vs.lengthMinMax({min: 1, max: 100})],
        });

        Brx.on("click", "#loginBtn", function (event) {
            console.log(">>> loginBtn on click", tx.models.user);
            axios.post('login.php', tx)
            .then(function (response) {
                console.log(response.data);
                // console.log(response);
                // xo.models = response.data.models;
                if (response.data.notice.status === "success") {
                    window.location.href = window.location.href.replace("/login.php", "/home.php");
                }
            })
            .catch(function (error) {
                console.log(error);
            });

        });
    };
    </script>
</body>
</html>
<?php
    },
    'post application/json' => function (\J\JJ $jj)
    {
        $inputUser = $jj->readJson()['models']['user'];

        $usersDAO = $jj->dao('users');
        $user = $usersDAO->findOneBy($usersDAO->attachTypes(['name' => $inputUser['name']]));

        if ($user && password_verify($inputUser['password'], $user['password'])) {
            $jj->data['notice']['status'] = 'success';
        } else {
            $jj->data['notice']['status'] = 'fail';
        }

        $jj->responseJson();
    }]);
?>
