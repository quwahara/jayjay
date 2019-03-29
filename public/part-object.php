<?php (require __DIR__ . '/../jj/JJ.php')([
    'init' => function () {
        $this->initStructsBy('path2');
    },
    'structs' => [
        'context' => [
            'id' => 0,
            'parent' => [
                'id' => 0,
                'type' => '',
            ],
            'violations[]' => '',
        ],
        'partxs[]' => [
            'parts',
            'part_objects',
        ],
        'add' => [
            'part',
            'part_object',
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'attrs' => [
        'add' => [
            'part',
            'part_object',
            // 'value_string' => [
            //     'minlength' => 3,
            //     'maxlength' => 5,
            //     // 'minlength' => 0,
            //     // 'maxlength' => 1000,
            // ],
            // 'value_number' => [
            //     'min' => -9223372036854775808,
            //     'max' => 9223372036854775807,
            // ],
        ]
    ],
    'refreshData' => function ($id) {

        $ctx = &$this->data['context'];
        $ctx['id'] = $id;

        $this->data['partxs'] = $this->dao('parts', ['part_objects'])->attFetchAll(
            'select p.* '
                . ', o.parent_id '
                . ', o.child_id '
                . ', o.name '
                . 'from parts p '
                . ' inner join part_objects o '
                . '     on p.id = o.child_id '
                . 'where o.parent_id = :id '
                . 'order by '
                . ' o.name '
                . ' ',
            ['id' => $ctx['id']]
        );

        if ($part_object = $this->dao('part_object')->attFindOneBy(['child_id' => $ctx['id']])) {
            $ctx['parent'] = [
                'id' => $part_object['parent_id'],
                'type' => 'object',
            ];
        }

        if ($part_array = $this->dao('part_array')->attFindOneBy(['child_id' => $ctx['id']])) {
            $ctx['parent'] = [
                'id' => $part_array['parent_id'],
                'type' => 'array',
            ];
        }

        $this->data['path_snippet'] = ['paths' => $this->part()->path($ctx['id'])];

        $this->data['add']['type'] = 'string';
        $this->data['commands'] = [
            'command' => '',
            'delete_id' => 0,
        ];
        return $this;
    },
    'get' => function () {
        $this->refreshData($this->getRequest('id', 0));
        ?>
<html>

<head>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/booq/booq.js"></script>
    <script src="js/lib/global.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Part object</title>
</head>

<body>
    <div>
        <div class="belt">
            <h1>Part object</h1>
        </div>

        <div class="belt bg-mono-09 context">
            <a href="home.php">Home</a>
            <a href="part-global.php">Part global</a>
            <span class="parent">
                <a class="type object id none">Parent object</a>
                <a class="type array id none">Parent array</a>
            </span>
        </div>

        <div>
            <?php $this->echoBy("path2"); ?>
        </div>

        <div class="contents">
            <div class="row context">
                <label>Id</label>
                <span class="id caption"></span>
            </div>

            <form method="post">
                <div class="row">
                    <table>
                        <thead>
                            <tr>
                                <th class="">&times;</th>
                                <th class="">name</th>
                                <th class="">id</th>
                                <th class="">type</th>
                                <th class="">value</th>
                            </tr>
                        </thead>
                        <tbody class="partxs">
                            <tr>
                                <td><button type="button" class="id command">&times;</button></td>
                                <td><a class="name"></a></td>
                                <td><a class="id caption"></a></td>
                                <td class="type"></td>
                                <td><span class="value_string"></span><span class="value_number"></span></td>
                            </tr>
                        </tbody>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th class="">+</th>
                                <th class="">name</th>
                                <th class="">type</th>
                                <th class="">
                                    <span class="add_value_string add_value_string_available none">string value</span>
                                    <span class="add_value_number add_value_number_available none">number value</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="add">
                                <td><button type="button" name="add">+</button></td>
                                <td><input name="name" class="h5v" type="text"></td>
                                <td>
                                    <select class="type" name="type">
                                        <option value="string">String</option>
                                        <option value="number">Number</option>
                                        <option value="object">Object</option>
                                        <option value="array">Array</option>
                                    </select>
                                </td>
                                <td>
                                    <input name="value_string" class="type none h5v" type="text">
                                    <input name="value_number" class="type none h5v" type="number">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="row context">
                <a class="id add_property">Add a property</a>
                <span class="parent">
                </span>
            </div>
        </div>
        <div id="snackbar"></div>
    </div>
    <script>
        window.onload = function() {
            var attrs;
            (attrs = new Booq(<?= $this->attrsAsJSON() ?>))
            .add
                .name.linkPreferred3("name").toAttrs()
                .value_string.linkPreferred3("name").toAttrs()
                .value_number.linkPreferred3("name").toAttrs()
                .setStructureAsData();

            var booq;
            (booq = new Booq(<?= $this->structsAsJSON() ?>))
            .context
                .id.linkExtra(".caption").toText()
                .id.linkExtra(".add_property").toHref("part.php?parent_type=object&parent_id=:id")
                .parent
                .id.linkExtra(".object").toHref("part-object.php?id=:parent_id")
                .id.linkExtra(".array").toHref("part-array.php?id=:parent_id")
                .type.linkExtra(".object").eq("object").thenUntitoggle("none")
                .type.linkExtra(".array").eq("array").thenUntitoggle("none")
                .end // of parent
                .end // of context
                //
                .path_snippet.callFunctionWithThis(pathSnippetBroker)
                .end // of path_snippet

                .partxs.each(function(element) {
                    this
                        .id.linkExtra(".command").toAttr("data-id")
                        .id.linkExtra(".command").on("click", function(event) {
                            Global.modal.create({
                                    body: "id:" + event.target.dataset.id + " を削除してもよろしいですか",
                                    ok: {
                                        onclick: function() {
                                            console.log(booq.data);
                                            booq.data.status = "";
                                            booq.data.commands.command = "delete";
                                            booq.data.commands.delete_id = event.target.dataset.id;
                                            axios.post("part-object.php", booq.data)
                                                .then(function(response) {
                                                    console.log(response.data);
                                                    booq.data = response.data;
                                                    // booq.data.message = response.data.message;
                                                    // if ("" !== booq.data.message) {
                                                    //     Global.snackbar.messageDiv.classList.add("warning");
                                                    //     Global.snackbar.maximize();
                                                    // }
                                                })
                                                .catch(Global.catcher(booq.data));
                                        }
                                    }
                                })
                                .open();
                        })
                        .linkExtra(" a.id").toHref(function(value) {
                            if (value.type === "string" || value.type === "number") {
                                return "part.php" +
                                    "?id=" + value.id;
                            } else if (value.type === "array") {
                                return "part-array.php" +
                                    "?id=" + value.id;
                            } else if (value.type === "object") {
                                return "part-object.php" +
                                    "?id=" + value.id;
                            } else {
                                //
                            }
                        })
                        .name.toText()
                        .id.linkExtra(".caption").toText()
                        .type.toText()
                        .value_string.toText()
                        .value_number.toText();
                })
                .add
                .name.withValue()
                .type.withValue()
                .type.linkExtra("[name='value_string']").eq("string").thenUntitoggle("none")
                .type.linkExtra("[name='value_number']").eq("number").thenUntitoggle("none")
                .value_string.withValue()
                .value_number.withValue()
                .on("click", function(event) {

                    if (!Global.snackbarByVlidity(
                            this.name.selector("name") + ", " +
                            this.value_string.selector("name") + ", " +
                            this.value_number.selector("name")
                        )) return;

                    Global.modal.create({
                            body: "追加してもよろしいですか",
                            ok: {
                                onclick: function() {
                                    Global.snackbar.close();
                                    booq.data.status = "";
                                    booq.data.commands.command = "add";
                                    axios.post("part-object.php", booq.data)
                                        .then(function(response) {
                                            console.log(response.data);
                                            booq.data = response.data;

                                            if (!Global.snackbarByViolations(booq.data.context.violations)) return;

                                            // booq.data.message = response.data.message;
                                            // if ("" !== booq.data.message) {
                                            //     Global.snackbar.messageDiv.classList.add("warning");
                                            //     Global.snackbar.maximize();
                                            // }
                                        })
                                        .catch(Global.snackbarByCatchFunction());
                                }
                            }
                        })
                        .open();
                })
                .end
                .setData(<?= $this->dataAsJSON() ?>);
        };
    </script>
</body>

</html>
<?php

},
'post application/json' => function () {
    $attrs = &$this->attrs;
    $data = &$this->data;
    $command = $data['commands']['command'];
    if ($command === 'delete') {
        $delete_id = $data['commands']['delete_id'];
        $this->part()->delete($delete_id);
    } else if ($command === 'add') {
        $add = &$data['add'];
        $type = $add['type'];
        if ($type === 'string' || $type === 'number') {
            $fieldName = "value_{$type}";
            $propValue = $add[$fieldName];
            $violations = $this->validate($fieldName, $type, $propValue, $attrs['add'][$fieldName]);
        } else {
            $violations = [];
        }
        $parent_id = $data['context']['id'];
        $propName = $add['name'];
        if (0 === count($violations)) {
            if (!is_null($this->part()->findPropertyByParentIdAndName($parent_id, $propName))) {
                $violations[] = [
                    'name' => 'name',
                    'type' => '',
                    'value' => $propName,
                    'violation' => 'duplication',
                ];
            }
        }
        if (0 === count($violations)) {
            $this->part()->addNewProperty($parent_id, $propName, $type, $add['value_string'], $add['value_number']);
            $add['name'] = '';
            $add['type'] = '';
            $add['value_string'] = '';
            $add['value_number'] = '';
        }
        $data['context']['violations'] = $violations;
    }
    $this->refreshData($data['context']['id']);
    $data['status'] = 'OK';
}
]);
?> 