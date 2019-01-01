<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'parent' => [
            'parent_type' => '',
            'parent_id' => 0
        ],
        'part',
        'commands' => [
            'register' => ''
        ],
        'views' => [
            'typeSelectable' => false,
            'arrayOperatable' => false,
        ]
    ],
    'get' => function (\JJ\JJ $jj) {

        $jj->data['parent']['parent_type'] = $jj->getRequest('parent_type', '');
        $jj->data['parent']['parent_id'] = $jj->getRequest('parent_id', 0);

        $jj->data['part']['id'] = $jj->getRequest('id', 0);
        $jj->data['part']['type'] = 'string';
        if (($id = $jj->data['part']['id']) > 0) {
            if ($part = $jj->dao('part')->attFindOneById($id)) {
                $jj->data['part'] = $part;
            }
        }

        $jj->data['views'] = new stdClass();

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
    <title>Part</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Part</h1>
        </div>

        <div class="belt bg-mono-09">
            <div><a href="home.php">Home</a><a href="part-list.php">List</a></div>
        </div>

        <div class="contents">
            <form method="post">
                <input type="hidden" name="parent_type">
                <input type="hidden" name="parent_id">
                <input type="hidden" name="id">
                <div class="row type-select none">
                    <label for="type">Type</label>
                    <select name="type" disabled>
                        <option value="string">String</option>
                        <option value="number">Number</option>
                        <option value="object">Object</option>
                        <option value="array">Array</option>
                    </select>
                </div>
                <div class="row type-label none">
                    <label>Type</label>
                    <span class="type"></span>
                    <input type="hidden" name="type" disabled>
                </div>
                <div class="row value">
                    <label for="value">Value</label>
                    <input type="text" name="value">
                </div>
                <div class="row array-operations none">
                    <div><a class="array-new-item">New item</a></div>
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

        var booq;
        (booq = new Booq(<?= $jj->structsAsJSON() ?>))
        .commands
        .register.on("click", function (event) {

            Global.snackbar.close();
                booq.data.status = "";
                axios.post("part.php", booq.data)
                .then(function (response) {
                    console.log(response.data);
                    booq.data = response.data;
                    // booq.data.message = response.data.message;
                    // if ("" !== booq.data.message) {
                    //     Global.snackbar.messageDiv.classList.add("warning");
                    //     Global.snackbar.maximize();
                    // }
                })
                .catch(Global.catcher(booq.data));
        })
        .end

        .parent
        .parent_type.withValue()
        .parent_id.withValue()
        .end

        .part
        .id.withValue()
        .id.link(".array-new-item").toHref("part.php?parent_type=array&parent_id=:id")
        .type.withValue()
        .type.toText()
        .type.on("change", function() { booq.update(); })
        .value.withValue()
        .end

        .views.setUpdate(function (data) {
            data.typeSelectable = !booq.data.part.id;
            data.arrayOperatable = booq.data.part.id && booq.data.part.type === "array";
        })
        .typeSelectable.link(".type-select").antitogglesClass("none")
        .typeSelectable.link(".type-select select").antitogglesAttr("disabled", "")
        .typeSelectable.link(".type-label").togglesClass("none")
        .typeSelectable.link(".type-label input").togglesAttr("disabled", "")
        .arrayOperatable.link(".array-operations").antitogglesClass("none")
        .end
        .setData(<?= $jj->dataAsJSON() ?>)
        .update()
        ;

    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function (\JJ\JJ $jj) {

    $part = $jj->data['part'];
    $partDao = $jj->dao('part');
    $doUpdatePart = false;

    if (array_key_exists('id', $part) && intval($part['id']) > 0) {
        $doUpdatePart = null !== $partDao->attFindOneById($part['id']);
    }

    if ($doUpdatePart) {
        $partDao->attUpdateById($part);
    } else {
        unset($part['id']);
        $part = $partDao->attFindOneById($partDao->attInsert($part));
    }

    $parent = $jj->data['parent'];
    if ($parent['parent_type'] === 'array') {
        $partArrayDao = $jj->dao('part_array');
        $partArray = $partArrayDao->attFindOneBy([
            'parent_id' => $parent['parent_id'],
            'child_id' => $part['id']
        ]);
        if (is_null($partArray)) {
            $maxI = $partArrayDao->attFetchOne(
                'select max(i) as i_max from part_arrays '
                    . 'where parent_id = :parent_id ',
                ['parent_id' => $parent['parent_id']]
            )['i_max'];

            $i = is_null($maxI) ? 0 : ($maxI + 1);

            $partArrayDao->attInsert([
                'parent_id' => $parent['parent_id'],
                'child_id' => $part['id'],
                'i' => $i,
            ]);
        }
    }

    $jj->data['part'] = $part;
    $jj->data['status'] = 'OK';
}
]);
?>
