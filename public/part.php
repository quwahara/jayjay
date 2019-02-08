<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'parent' => [
            'parent_type' => '',
            'parent_id' => 0
        ],
        'part',
        'part_object',
        'part_array',
        'commands' => [
            'register' => ''
        ],
        'context' => [
            'id' => 0,
            'parent_type' => '',
            'parent_id' => 0,
            'parent_part_object' => false,
            'parent_part_array' => false,
            'has_parent' => false,

            // 'is_parent_object' => false,
            'is_update' => false,
            'value_available' => false,
            // 'array_operatable' => false,
            // 'object_operatable' => false,
            'register_available' => false,
        ],
        // 'views' => [
        //     'typeSelectable' => false,
        //     'arrayOperatable' => false,
        // ],
    ],
    'get' => function () {

        $ctx = &$this->data['context'];
        $ctx['id'] = $this->getRequestAsInt('id', 0);
        $ctx['parent_type'] = $this->getRequest('parent_type', '');
        $ctx['parent_id'] = $this->getRequestAsInt('parent_id', 0);
        $ctx['parent_part_object'] = false;
        $ctx['parent_part_array'] = false;

        // $ctx['has_parent'] = 0 < $ctx['parent_id'];
        // $ctx['is_parent_object'] = 'object' === $ctx['parent_type'];
        $ctx['value_available'] = true;
        // $ctx['array_operatable'] = false;
        // $ctx['object_operatable'] = false;
        $ctx['register_available'] = false;

        // $this->data['parent']['parent_type'] = $this->getRequest('parent_type', '');
        // $this->data['parent']['parent_id'] = $this->getRequest('parent_id', 0);

        $this->data['part']['id'] = '';
        $this->data['part']['type'] = 'string';

        $ctx['is_update'] = false;
        if ($ctx['id'] > 0) {
            if ($part = $this->dao('part')->attFindOneById($ctx['id'])) {
                $this->data['part'] = $part;
                $ctx['is_update'] = true;

                if ($part_object = $this->dao('part_object')->attFindOneBy(['child_id' => $ctx['id']])) {
                    $this->data['part_object'] = $part_object;
                    $ctx['parent_type'] = 'object';
                    $ctx['parent_id'] = $part_object['parent_id'];
                    $ctx['parent_part_object'] = true;
                    $ctx['has_parent'] = true;

                } else if ($part_array = $this->dao('part_array')->attFindOneBy(['child_id' => $ctx['id']])) {
                    $this->data['part_array'] = $part_array;
                    $ctx['parent_type'] = 'array';
                    $ctx['parent_id'] = $part_array['parent_id'];
                    $ctx['parent_part_array'] = true;
                    $ctx['has_parent'] = true;
                }
            }

        } else if ($ctx['parent_id'] > 0) {
            if ($parent_part = $this->dao('part')->attFindOneBy(['id' => $ctx['parent_id']])) {
                $ctx['parent_type'] = $parent_part['type'];
                $ctx['parent_part_object'] = 'object' === $ctx['parent_type'];
                $ctx['parent_part_array'] = 'array' === $ctx['parent_type'];
                $ctx['has_parent'] = true;
            }
        }

        // $this->data['views'] = new stdClass();
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
    <title>Part</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Part</h1>
        </div>

        <div class="belt bg-mono-09">
            <a href="home.php">Home</a>
            <a href="part-global.php">Part global</a>
            <a class="parent_part_object none">Parent object</a>
            <a class="parent_part_array none">Parent array</a>
        </div>

        <div class="contents">
            <form method="post">
                <input type="hidden" name="parent_type">
                <input type="hidden" name="parent_id">
                <input type="hidden" name="id">
                <div class="row has_parent none">
                    <label>Parent Id</label>
                    <span class="parent_id"></span>
                </div>
                <div class="row has_parent none">
                    <label>Parent Type</label>
                    <span class="parent_type"></span>
                </div>
                <div class="row parent_part_array none">
                    <label>I</label>
                    <span class="array_index"></span>
                </div>
                <div class="row">
                    <label>Id</label>
                    <span class="id"></span>
                </div>
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
                <div class="row name parent_part_object none">
                    <label for="name">Name</label>
                    <input type="text" name="name">
                </div>
                <div class="row value value_available none">
                    <label for="value">Value</label>
                    <input type="text" name="value">
                </div>
                <div class="row array_operatable none">
                    <div><a class="array-new-item">New item</a></div>
                    <div><a class="array-items">Items</a></div>
                </div>
                <div class="row object_operatable none">
                    <div><a class="object-new-prop">New property</a></div>
                    <div><a class="object-props">Properties</a></div>
                </div>
                <div class="row register_available none">
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
        (booq = new Booq(<?= $this->structsAsJSON() ?>))
        .commands
        .register.on("click", function (event) {

            Global.snackbar.close();
                booq.data.status = "";
                axios.post("part.php", booq.data)
                .then(function (response) {
                    console.log(response.data);
                    // booq.data = response.data;
                    booq
                    .setData(response.data)
                    .update()
                    ;
                    // booq.data.message = response.data.message;
                    // if ("" !== booq.data.message) {
                    //     Global.snackbar.messageDiv.classList.add("warning");
                    //     Global.snackbar.maximize();
                    // }
                })
                .catch(Global.catcher(booq.data));
        })
        .end

        // .parent
        // .parent_type.withValue()
        // .also.toText()
        // .parent_id.withValue()
        // .parent_id.toText()
        // .end

        .part
        .id.withValue()
        .id.toText()
        .id.link(".array-new-item").toHref("part.php?parent_type=array&parent_id=:id")
        .id.link(".array-items").toHref("part-list.php?parent_type=array&parent_id=:id")
        .id.link(".object-new-prop").toHref("part.php?parent_type=object&parent_id=:id")
        .id.link(".object-props").toHref("part-list.php?parent_type=object&parent_id=:id")
        .type.withValue()
        .type.toText()
        .type.on("change", function() { booq.update(); })
        .value.withValue()
        .end

        .part_object
        // .child_id.link("input[name='id']").withValue()
        .name.withValue()
        .end
        
        .part_array
        .i.link(".array_index").toText()
        .end
        
        .context.setUpdate(function (data) {
            isPrimitiveType = booq.data.part.type === "string" || booq.data.part.type === "number";
            data.value_available = isPrimitiveType;
            data.array_operatable = data.is_update && booq.data.part.type === "array";
            data.object_operatable = data.is_update && booq.data.part.type === "object";
            data.register_available = !data.is_update || (data.is_update && isPrimitiveType);
        })
        .link(".parent_part_array").toHref("part-array.php?id=:parent_id")
        .parent_type.withValue()
        .parent_type.antitogglesClass("none")
        .also.toText()
        .parent_id.withValue()
        .parent_id.toText()
        .parent_part_object.antitogglesClass("none")
        .link(".parent_part_object").toHref("part-object.php?id=:parent_id")
        .parent_part_array.antitogglesClass("none")
        .link(".parent_part_array").toHref("part-array.php?id=:parent_id")
        // .also.toHref("part-array.php?id=:parent_id")
        .has_parent.antitogglesClass("none")
        // .is_parent_object.antitogglesClass("none")
        .is_update.link(".type-select").togglesClass("none")
        .also.link(".type-select select").togglesAttr("disabled", "")
        .also.link(".type-label").antitogglesClass("none")
        .also.link(".type-label input").antitogglesAttr("disabled", "")
        .parent_part_object.antitogglesClass("none")
        .value_available.antitogglesClass("none")
        // .array_operatable.antitogglesClass("none")
        // .object_operatable.antitogglesClass("none")
        .register_available.antitogglesClass("none")
        .end

        // .views.setUpdate(function (data) {
        //     data.typeSelectable = !booq.data.part.id;
        //     data.arrayOperatable = booq.data.part.id && booq.data.part.type === "array";
        // })
        // .typeSelectable.link(".type-select").antitogglesClass("none")
        // .typeSelectable.link(".type-select select").antitogglesAttr("disabled", "")
        // .typeSelectable.link(".type-label").togglesClass("none")
        // .typeSelectable.link(".type-label input").togglesAttr("disabled", "")
        // .arrayOperatable.link(".array-operations").antitogglesClass("none")
        // .end
        .setData(<?= $this->dataAsJSON() ?>)
        .update()
        ;

    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function () {

    $part = $this->data['part'];
    $partDao = $this->dao('part');
    $doUpdatePart = false;

    if (array_key_exists('id', $part) && intval($part['id']) > 0) {
        $doUpdatePart = null !== $partDao->attFindOneById($part['id']);
    }

    if ($doUpdatePart) {
        $partDao->attUpdateById($part);
    } else {
        unset($part['id']);
        $part = $partDao->attFindOneById($partDao->attInsert($part));

        $ctx = &$this->data['context'];
        if ($ctx['parent_part_array']) {
            $partArrayDao = $this->dao('part_array');
            $partArray = $partArrayDao->attFindOneBy([
                'parent_id' => $ctx['parent_id'],
                'child_id' => $part['id']
            ]);
            if (is_null($partArray)) {
                $maxI = $partArrayDao->attFetchOne(
                    'select max(i) as i_max from part_arrays '
                        . 'where parent_id = :parent_id ',
                    ['parent_id' => $ctx['parent_id']]
                )['i_max'];

                $i = is_null($maxI) ? 0 : ($maxI + 1);

                $partArrayDao->attInsert([
                    'parent_id' => $ctx['parent_id'],
                    'child_id' => $part['id'],
                    'i' => $i,
                ]);
            }
        } else if ($ctx['parent_part_object']) {
            $partObjectDao = $this->dao('part_object');
            $partObject = $partObjectDao->attFindOneBy([
                'parent_id' => $ctx['parent_id'],
                'child_id' => $part['id']
            ]);
            if (is_null($partObject)) {
                $partObjectDao->attInsert([
                    'parent_id' => $ctx['parent_id'],
                    'child_id' => $part['id'],
                    'name' => $this->data['part_object']['name'],
                ]);
            }
        }
    }

    $this->data['part'] = $part;
    $ctx['is_update'] = true;

    $this->data['status'] = 'OK';
}
]);
?>
