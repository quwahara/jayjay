<?php (require __DIR__ . '/../jj/JJ.php')([
    'init' => function () {
        $this->initStructsBy('path');
    },
    'structs' => [
        'context' => [
            'id' => 0,
            'parent' => [
                'id' => 0,
                'type' => '',
            ],
        ],
        'partxs[]' => [
            'parts',
            'part_properties',
        ],
        'add' => [
            'part',
            'part_property',
            'id_copy_from' => '',
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'attrs' => [
        'add' => [
            'part',
            'part_property',
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

        if (is_null($id)) {
            $id = $this->part()->rootId;
        }

        $ctx = &$this->data['context'];
        $ctx['id'] = $id;

        $this->data['partxs'] = $this->dao('parts', ['part_properties'])->attFetchAll(
            'select p.* '
                . ', r.parent_id '
                . ', r.child_id '
                . ', r.name '
                . 'from parts p '
                . ' inner join part_properties r '
                . '     on p.id = r.child_id '
                . 'where r.parent_id = :id '
                . 'order by '
                . ' r.name '
                . ' ',
            ['id' => $ctx['id']]
        );

        if ($part_property = $this->dao('part_property')->attFindOneBy(['child_id' => $ctx['id']])) {
            $ctx['parent'] = [
                'id' => $part_property['parent_id'],
                'type' => 'object',
            ];
        }

        if ($part_item = $this->dao('part_item')->attFindOneBy(['child_id' => $ctx['id']])) {
            $ctx['parent'] = [
                'id' => $part_item['parent_id'],
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
        $this->refreshData($this->getRequest('id'));
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
        <title>Part object</title>
    </head>

    <body>
        <script>
            var attrs = new Booq(<?= $this->attrsAsJSON() ?>);
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>

        <div>
            <div class="belt head">
                <h1>Part object</h1>
            </div>

            <div class="belt neck context parent">
                <a href="home.php">Home</a>

                <span class="object">
                    <a class="type none id object">Parent object</a>
                </span>
                <script>
                    structs.context.parent.extent(".belt .object")
                        .type.eq("object").thenUntitoggle("none")
                        .id.linkExtra(".part").toHref("part.php?parent_id=:id")
                        .id.linkExtra(".object").toHref("part-object.php?id=:id")
                        .endParent;
                </script>

                <span class="array">
                    <a class="type none id array">Parent array</a>
                </span>
                <script>
                    structs.context.parent.extent(".belt .array")
                        .type.eq("array").thenUntitoggle("none")
                        .id.linkExtra(".part").toHref("part.php?parent_id=:id")
                        .id.linkExtra(".array").toHref("part-array.php?id=:id")
                        .endParent;
                </script>
            </div>

            <div class="contents">
                <div>
                    <?php $this->echoBy("path"); ?>
                </div>
                <script>
                    structs.path_snippet.callFunctionWithThis(pathSnippetBroker);
                </script>

                <div class="row context caption">
                    <label>Id</label>
                    <span class="id"></span>
                </div>
                <script>
                    structs.context.extent(".caption").id.toText().endContext;
                </script>

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
                        <script>
                            structs.partxs.each(function(element) {
                                this
                                    .id.linkExtra(".command").toAttr("data-id")
                                    .id.linkExtra(".command").on("click", function(event) {
                                        Global.modal.create({
                                                body: "id:" + event.target.dataset.id + " を削除してもよろしいですか",
                                                ok: {
                                                    onclick: function() {
                                                        console.log(structs.data);
                                                        structs.data.status = "";
                                                        structs.data.commands.command = "delete";
                                                        structs.data.commands.delete_id = event.target.dataset.id;
                                                        axios.post("part-object.php", structs.data)
                                                            .then(function(response) {
                                                                console.log(response.data);
                                                                structs.data = response.data;
                                                                // structs.data.message = response.data.message;
                                                                // if ("" !== structs.data.message) {
                                                                //     Global.snackbar.messageDiv.classList.add("warning");
                                                                //     Global.snackbar.maximize();
                                                                // }
                                                            })
                                                            .catch(Global.catcher(structs.data));
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
                            });
                        </script>

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
                                            <option value="copy_from">Copy from</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="type none h5v" name="value_string" type="text">
                                        <input class="type none h5v" name="value_number" type="number">
                                        <input class="type none h5v" name="id_copy_from" type="text">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <script>
                            attrs.add
                                .name.linkPreferred("down_and_name").toAttrs()
                                .value_string.linkPreferred("down_and_name").toAttrs()
                                .value_number.linkPreferred("down_and_name").toAttrs()
                                .setStructureAsData();

                            structs.add
                                .name.withValue()
                                .type.withValue()
                                .type.linkExtra("[name='value_string']").eq("string").thenUntitoggle("none")
                                .type.linkExtra("[name='value_number']").eq("number").thenUntitoggle("none")
                                .type.linkExtra("[name='id_copy_from']").eq("copy_from").thenUntitoggle("none")
                                .value_string.withValue()
                                .value_number.withValue()
                                .id_copy_from.withValue()
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
                                                    structs.data.status = "";
                                                    structs.data.commands.command = "add";
                                                    axios.post("part-object.php", structs.data)
                                                        .then(function(response) {
                                                            console.log(response.data);
                                                            structs.data = response.data;

                                                            if (!Global.snackbarByViolations(structs.data.context.violations)) return;
                                                        })
                                                        .catch(Global.snackbarByCatchFunction());
                                                }
                                            }
                                        })
                                        .open();
                                })
                        </script>
                    </div>
                </form>
                <div class="row context object-child">
                    <a class="id">Add a property</a>
                </div>
                <script>
                    structs.context.extent(".object-child")
                        .id.toHref("part.php?parent_id=:id")
                        .endContext;
                </script>

            </div>
        </div>
        <script>
            window.onload = function() {
                structs.setData(<?= $this->dataAsJSON() ?>);
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
            if ($type === 'copy_from') {
                // copy_from
                $part['id'] = $this->part()->cloneById($parent_id, $propName, $add['id_copy_from']);
            } else {
                $this->part()->addNewProperty($parent_id, $propName, $type, $add['value_string'], $add['value_number']);
            }
            $add['name'] = '';
            $add['type'] = '';
            $add['value_string'] = '';
            $add['value_number'] = '';
            $add['id_copy_from'] = '';
        }
        $data['context']['violations'] = $violations;
    }
    $this->refreshData($data['context']['id']);
    $data['status'] = 'OK';
}
]);
?>