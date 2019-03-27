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
        'add_part' => [
            'part',
            'part_object',
            'add_value_available' => false,

            'add_name' => '',
            'add_value_string' => '',
            'add_value_number' => '',
            'add_value_string_available' => false,
            'add_value_number_available' => false,
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'attrs' => [
        'part',
        'part_object',
        'add_value_string' => [
            'minlength' => 3,
            'maxlength' => 5,
            // 'minlength' => 0,
            // 'maxlength' => 1000,
        ],
        'add_value_number' => [
            'min' => -9223372036854775808,
            'max' => 9223372036854775807,
        ],

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

        $ctx['path'] = $this->part()->path($ctx['id']);
        $this->data['path_snippet'] = ['paths' => $this->part()->path($ctx['id'])];

        $this->data['add_part']['type'] = 'string';
        $this->data['add_part']['add_value_string_available'] = 'string' === $this->data['add_part']['type'];
        $this->data['add_part']['add_value_number_available'] = 'number' === $this->data['add_part']['type'];

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
    <script src="js/booq/booq-experimental.js"></script>
    <script src="js/lib/global.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Part object (Experimental)</title>
</head>

<body>
    <div>
        <div class="belt">
            <h1>Part object (Experimental)</h1>
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
            <?php  ?>
            <?php $this->echoBy("path2"); ?>
        </div>

        <div class="contents">
            <div class="row context">
                <label>Id</label>
                <span class="id"></span>
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
                                <td><button type="button" class="delete">&times;</button></td>
                                <td><a class="name"></a></td>
                                <td><a class="id"></a></td>
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
                            <tr>
                                <td><button type="button" class="add">+</button></td>
                                <td><input name="add_name" class="h5v" type="text"></td>
                                <td>
                                    <select class="add_type" name="type">
                                        <option value="string">String</option>
                                        <option value="number">Number</option>
                                        <option value="object">Object</option>
                                        <option value="array">Array</option>
                                    </select>
                                </td>
                                <td>
                                    <input name="add_value_string" class="add_value_string_available none h5v" type="text">
                                    <input name="add_value_number" class="add_value_number_available none h5v" type="number">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="row">
                <a class="add_property">Add a property</a>
            </div>
        </div>
        <div id="snackbar"></div>
    </div>
    <script>
        window.onload = function() {
            var attrs;
            (attrs = new Booq(<?= $this->attrsAsJSON() ?>))
            //>>
            // .name.link(".add_name").toAttrs()
            // .value.link(".add_value").toAttrs()
            // .add_value_string.linkByName().toAttrs()
            // .add_value_number.linkByName().toAttrs()
            .setStructureAsData();


            var booq;
            (booq = new Booq(<?= $this->structsAsJSON() ?>))
            .context
                .id.toText()
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

                //.context
                // .link(".add_property").toHref("part.php?parent_type=object&parent_id=:id")

                .partxs.each(function(element) {
                    this
                        .linkExtra(" .id").toHref(function(value) {
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
                        .id.toText()
                        .type.toText()
                        .value_string.toText()
                        .value_number.toText();
                    Booq.q(element).q("button.delete").on("click", (function(self) {
                        return function(event) {
                            Global.modal.create({
                                    body: "id:" + self.data.id + " を削除してもよろしいですか",
                                    ok: {
                                        onclick: function() {
                                            console.log(self.data);
                                            booq.data.status = "";
                                            booq.data.commands.command = "delete";
                                            booq.data.commands.delete_id = self.data.id;
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

                        };
                    })(this));
                })
                // .add_part
                // // .name.link("input.add_name").withValue()
                // .add_name.withValue()
                // .type.link("select.add_type").withValue()
                // .type.onReceive(function(value, data) {
                //     data.add_value_string_available = value === "string";
                //     data.add_value_number_available = value === "number";
                // })
                // .add_value_string_available.antitogglesClass("none")
                // .add_value_number_available.antitogglesClass("none")
                // // .value.link("input.add_value").withValue()
                // .add_value_string.withValue()
                // .add_value_number.withValue()
                // .end
                .setData(<?= $this->dataAsJSON() ?>);

            Booq.q("button.add").on("click", function(event) {

                if (!Global.snackbarByVlidity("input.add_name")) return;

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
            });
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
        $add_part = &$data['add_part'];
        //>> Under construction
        $type = $add_part['type'];
        if ($type === 'string' || $type === 'number') {
            $fieldName = "add_value_{$type}";
            $propValue = $add_part[$fieldName];
            $violations = $this->validate($fieldName, $type, $propValue, $attrs[$fieldName]);
        } else {
            $violations = [];
        }
        $parent_id = $data['context']['id'];
        $propName = $add_part['add_name'];
        if (0 === count($violations)) {
            if (!is_null($this->part()->findPropertyByParentIdAndName($parent_id, $propName))) {
                $violations[] = [
                    'name' => 'add_name',
                    'type' => '',
                    'value' => $propName,
                    'violation' => 'duplication',
                ];
            }
        }
        if (0 === count($violations)) {
            $this->part()->addNewProperty($parent_id, $propName, $type, $add_part['add_value_string'], $add_part['add_value_number']);
            $add_part['add_name'] = '';
            $add_part['type'] = '';
            $add_part['add_value_string'] = '';
            $add_part['add_value_number'] = '';
        }
        $data['context']['violations'] = $violations;
    }
    $this->refreshData($data['context']['id']);
    $data['status'] = 'OK';
}
]);
?> 