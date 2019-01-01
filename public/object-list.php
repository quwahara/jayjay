<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'objects[]',
        'commands' => [
            'search' => ''
        ]
    ],
    'get' => function (\JJ\JJ $jj) {
        $jj->data['objects'] = $jj->dao('objects')->attFindAllBy([]);
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
    <title>Object list</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Object list</h1>
        </div>

        <div class="belt bg-mono-09">
            <div><a href="home.php">Home</a></div>
        </div>

        <div class="contents">
            <div class="row">
                <a href="object.php">New</a>
            </div>

            <form method="post">
                <div class="row">
                    <table>
                        <thead>
                            <tr>
                                <th class="">id</th>
                                <th class="">type</th>
                                <th class="">i</th>
                                <th class="">name</th>
                                <th class="">value</th>
                            </tr>
                        </thead>
                        <tbody class="objects">
                            <tr>
                                <td><a class="id"></a></td>
                                <td class="type"></td>
                                <td class="i"></td>
                                <td class="name"></td>
                                <td class="value"></td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </form>
        </div>
        <div id="snackbar"></div>
    </div>
    <script>
    window.onload = function() {
        Global.snackbar("#snackbar");

        var booq = new Booq(<?= $jj->structsAsJSON() ?>)
        .objects.each(function () {
            this
            .id.toText()
            .id.toHref("object.php?id=:id")
            .type.toText()
            .i.toText()
            .name.toText()
            .value.toText()
            ;
        })
        .setData(<?= $jj->dataAsJSON() ?>)
        ;
    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function (\JJ\JJ $jj) {

    // $object = $jj->data['object'];
    // $objectDao = $jj->dao('object');
    // $doUpdate = false;

    // if (array_key_exists('id', $object) && intval($object['id']) > 0) {
    //     $doUpdate = null !== $objectDao->attFindOneById($object['id']);
    // }

    // if ($doUpdate) {
    //     $objectDao->attUpdateById($object);
    // } else {
    //     unset($object['id']);
    //     $jj->data['object'] = $objectDao->attFindOneById($objectDao->attInsert($object));
    // }
    // $jj->data['status'] = 'OK';
}
]);
?>
