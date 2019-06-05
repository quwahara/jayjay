<?php (require __DIR__ . '/../jj/JJ.php')([
    // Giving permission to access without logged in
    'access' => 'public',
    'structs' => ['user'],
    'get' => function () {
        ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="data:,">
        <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
        <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
        <link rel="stylesheet" type="text/css" href="css/global.css">
        <script src="js/lib/node_modules/axios/dist/axios.js"></script>
        <script src="js/booq/olbi.js"></script>
        <script src="js/lib/global.js"></script>
        <title>Login</title>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Login</h1>
            </div>
            <div class="contents">
                <form method="post" class="user">
                    <div class="row">
                        <div class="col-4">
                        </div>
                        <div class="col-4">
                            <div class="row">
                                <label for="name">Username</label>
                                <input class="col-12" type="text" name="name">
                            </div>
                            <div class="row">
                                <label for="password">Password</label>
                                <input class="col-12" type="password" name="password">
                            </div>
                            <div class="row">
                                <button class="col-12" type="submit" id="loginBtn">Login</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <script>
            window.onload = function() {
                Global.snackbar("#snackbar");
                var olbi = new Olbi(<?= $this->structsAsJSON() ?>);

                olbi
                    .message.toText()
                    .user
                    .name.withValue()
                    .password.withValue();

                Olbi.query("form").on("submit", function(event) {
                    event.preventDefault();
                    Global.snackbar.close();
                    olbi.data.status = "";
                    axios.post("index.php", olbi.data)
                        .then(function(response) {
                            console.log(response.data);
                            if (response.data.status === "#login-succeeded") {
                                window.location.href = window.location.href.replace("/index.php", "/home.php");
                            }
                            olbi.data = response.data;
                            olbi.data.message = Global.getMsg(olbi.data.status);
                            if ("" !== olbi.data.message) {
                                Global.snackbar.messageDiv.classList.add("warning");
                                Global.snackbar.maximize();
                            }
                        })
                        .catch(Global.catcher(olbi.data));
                });
            };
        </script>
        <div id="snackbar"></div>
    </body>

    </html>
<?php

},
'post application/json' => function () {
    $user = $this->dao('user')->attFindOneBy(['name' => $this->data['user']['name']]);
    if ($user && password_verify($this->data['user']['password'], $user['password'])) {
        $this->login(['user_id' => $user['name']]);
        $this->data['status'] = '#login-succeeded';
    } else {
        $this->data['status'] = '#login-failed';
    }
}
]);
?>