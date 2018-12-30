<?php (require __DIR__ . '/../jj/JJ.php')([
    // Giving permission to access without logged in
    'access' => 'public',
    'structs' => ['user'],
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
    <title>Login</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Login</h1>
        </div>

        <div class="belt bg-mono-09">
            <div>&nbsp;</div>
        </div>

        <div class="contents">
            <form method="post">
                <div class="row">
                    <label for="name">Username</label>
                    <input type="text" name="name">
                </div>
                <div class="row">
                    <label for="password">Password</label>
                    <input type="password" name="password">
                </div>
                <div class="row">
                    <div class="label"></div>
                    <button type="submit" id="loginBtn">Login</button>
                </div>
            </form>
        </div>

    </div>
    <script>
    window.onload = function() {
        Global.snackbar("#snackbar");
        var booq = new Booq(<?= $jj->structsAsJSON() ?>);

        booq
        .message.toText()
        .user
        .name.withValue()
        .password.withValue()
        ;

        Booq.q("form").on("submit", function(event) {
            event.preventDefault();
            Global.snackbar.close();
            booq.data.status = "";
            axios.post("index.php", booq.data)
            .then(function (response) {
                console.log(response.data);
                if (response.data.status === "#login-succeeded") {
                    window.location.href = window.location.href.replace("/index.php", "/home.php");
                }
                booq.data = response.data;
                booq.data.message = Global.getMsg(booq.data.status);
                if ("" !== booq.data.message) {
                    Global.snackbar.messageDiv.classList.add("warning");
                    Global.snackbar.maximize();
                }
            })
            .catch(Global.catcher(booq.data));
        });
    };
    </script>
    <div id="snackbar"></div>
</body>
</html>
<?php

},
'post application/json' => function (\JJ\JJ $jj) {
    $user = $jj->dao('user')->attFindOneBy(['name' => $jj->data['user']['name']]);
    if ($user && password_verify($jj->data['user']['password'], $user['password'])) {
        $jj->login(['user_id' => $user['name']]);
        $jj->data['status'] = '#login-succeeded';
    } else {
        $jj->data['status'] = '#login-failed';
    }
}
]);
?>
