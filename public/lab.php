<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'object',
        'commands' => [
            'register' => ''
        ]
    ],
    'get' => function () {
        $this->data['object']['type'] = 'variable';
        $this->data['object']['name'] = 'apple';
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
        <title>Lab</title>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1>Lab</h1>
            </div>

            <div class="belt neck">
                <div><a href="home.php">Home</a></div>
            </div>

            <div class="contents">
                <form method="post">
                    <div class="row">
                        <label for="type">Type</label>
                        <select name="type">
                            <option value="variable">Variable</option>
                            <option value="string">String</option>
                        </select>
                    </div>
                    <div class="row">
                        <label for="name">Name</label>
                        <input type="text" name="name">
                    </div>
                    <div class="row value">
                        <label for="value">Value</label>
                        <input type="value" name="value">
                    </div>
                    <div class="row">
                        <div class="label"></div>
                        <button type="button" name="register">Register</button>
                    </div>
                </form>
            </div>
            <div id="snackbar"></div>
        </div>
        <script>
            window.onload = function() {
                Global.snackbar("#snackbar");

                var booq = new Booq(<?= $this->structsAsJSON() ?>)
                    .commands
                    .register.on("click", function(event) {

                        Global.snackbar.close();
                        booq.status = "";
                        axios.post("lab.php", booq.data)
                            .then(function(response) {
                                console.log(response.data);
                                // booq.data.message = response.data.message;
                                // if ("" !== booq.data.message) {
                                //     Global.snackbar.messageDiv.classList.add("warning");
                                //     Global.snackbar.maximize();
                                // }
                            })
                            .catch(Global.catcher(booq));
                    })
                    .end

                    .object
                    .type.withValue()
                    .type.on("change", function(event) {
                        // if(event.target.value === "variable") {
                        //     Booq.q("[name='value']").addClass("hidden");
                        // }
                        this.update();
                    })
                    .name.withValue()
                    .setUpdate(function() {
                        var x = this;
                        Booq.q(".value").toggleClassByFlag("hidden", this.data.type !== "variable");
                        // this.type.toggleClassByFlag("hidden", true);
                    })
                    .update()
                    .end;


                booq.data = <?= $this->dataAsJSON() ?>;
            };
        </script>
    </body>

    </html>
<?php

},
'post application/json' => function () {

    $object = $this->data['object'];
    $objectDao = $this->dao('object');
    $doUpdate = false;

    if (array_key_exists('id', $object) && intval($object['id']) > 0) {
        $doUpdate = null !== $objectDao->attFindOneById($object['id']);
    }

    if ($doUpdate) {
        $objectDao->attUpdateById($object);
    } else {
        unset($object['id']);
        $this->data['object'] = $objectDao->attFindOneById($objectDao->attInsert($object));
    }
    $this->data['status'] = 'OK';
}
]);
?>