<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'object',
        'commands' => [
            'register' => ''
        ]
    ],
    'get' => function (\JJ\JJ $jj) {
        if (($id = $jj->getId()) > 0) {
            if ($object = $jj->dao('object')->attFindOneById($id)) {
                $jj->data['object'] = $object;
            }
        }
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
    <title>Object</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Object</h1>
        </div>

        <div class="belt bg-mono-09">
            <div><a href="home.php">Home</a><a href="object-list.php">List</a></div>
        </div>

        <div class="contents">
            <form method="post">
                <input type="hidden" name="id">
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

        // var booq = new Booq(< = $jj->structsAsJSON() ?>)
        (booq = new Booq(<?= $jj->structsAsJSON() ?>))
        .commands
        .register.on("click", function (event) {

            Global.snackbar.close();
                booq.status = "";
                axios.post("object.php", booq.data)
                .then(function (response) {
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
        .id.withValue()
        .type.withValue()
        .type.on("change", booq.update)
        .name.withValue()
        .setUpdate(function () {
            Booq.q(".value").toggleClassByFlag("hidden", this.data.type !== "variable");
        })
        .update()
        .end
        .setData(<?= $jj->dataAsJSON() ?>)
        ;

        // booq.object.type.on("change", booq.update);
    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function (\JJ\JJ $jj) {

    $object = $jj->data['object'];
    $objectDao = $jj->dao('object');
    $doUpdate = false;

    if (array_key_exists('id', $object) && intval($object['id']) > 0) {
        $doUpdate = null !== $objectDao->attFindOneById($object['id']);
    }

    if ($doUpdate) {
        $objectDao->attUpdateById($object);
    } else {
        unset($object['id']);
        $jj->data['object'] = $objectDao->attFindOneById($objectDao->attInsert($object));
    }
    $jj->data['status'] = 'OK';
}
]);
?>
