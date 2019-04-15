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
        ],
        'partxs[]' => [
            'parts',
            'part_arrays',
        ],
        'add' => [
            'part',
            'part_array',
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'refreshData' => function ($id) {

        $ctx = &$this->data['context'];
        $ctx['id'] = $id;

        if ($parent_part = $this->part()->findPart($ctx['id'])) {
            $ctx['parent'] = [
                'id' => $parent_part['id'],
                'type' => $parent_part['type'],
            ];
        }

        $this->data['path_snippet'] = ['paths' => $this->part()->path($ctx['id'])];

        $this->data['partxs'] = $this->dao('parts', ['part_arrays'])->attFetchAll(
            'select p.*  '
                . ', a.parent_id '
                . ', a.child_id '
                . ', a.i '
                . 'from parts p '
                . ' inner join part_arrays a '
                . '     on p.id = a.child_id '
                . 'where a.parent_id = :id '
                . 'order by '
                . ' a.i '
                . ' ',
            ['id' => $ctx['id']]
        );

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
        <title>Part array</title>
    </head>

    <body>
        <script>
            var attrs = new Booq(<?= $this->attrsAsJSON() ?>);
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>

        <div>
            <div class="belt">
                <h1>Part array</h1>
            </div>

            <div class="belt bg-mono-09">
                <a href="home.php">Home</a>
                <a href="part-global.php">Part global</a>

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

            <div>
                <?php $this->echoBy("path2"); ?>
            </div>
            <script>
                structs.path_snippet.callFunctionWithThis(pathSnippetBroker);
            </script>

            <div class="contents">
                <div class="row context parent">
                    <label>Id</label>
                    <span class="id caption"></span>
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
                                    <th class="">i</th>
                                    <th class="">id</th>
                                    <th class="">type</th>
                                    <th class="">value</th>
                                </tr>
                            </thead>
                            <tbody class="partxs">
                                <tr>
                                    <td><button type="button" class="id command">&times;</button></td>
                                    <td><a class="i"></a></td>
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
                                                        axios.post("part-array.php", structs.data)
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
                                    .i.toText()
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
                                    <th class="">type</th>
                                    <th class="add_value_available none">value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="add">
                                    <td><button type="button" name="add">+</button></td>
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
                        <script>
                            // attrs.add
                            //     .name.linkPreferred("lower_name").toAttrs()
                            //     .value_string.linkPreferred("lower_name").toAttrs()
                            //     .value_number.linkPreferred("lower_name").toAttrs()
                            //     .setStructureAsData();

                            structs.add
                                .type.withValue()
                                .type.linkExtra("[name='value_string']").eq("string").thenUntitoggle("none")
                                .type.linkExtra("[name='value_number']").eq("number").thenUntitoggle("none")
                                .value_string.withValue()
                                .value_number.withValue()
                                .on("click", function(event) {

                                    if (!Global.snackbarByVlidity(
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
                                                    axios.post("part-array.php", structs.data)
                                                        .then(function(response) {
                                                            console.log(response.data);
                                                            structs.data = response.data;

                                                            if (!Global.snackbarByViolations(structs.data.context.violations)) return;

                                                            // structs.data.message = response.data.message;
                                                            // if ("" !== structs.data.message) {
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
                                .endAdd;
                        </script>
                    </div>
                </form>
                <div class="row array-child context parent">
                    <a class="id">Add an item</a>
                </div>
                <script>
                    structs.extent(".array-child").context.parent
                        .id.toHref("part.php?parent_id=:id");
                    structs.extent("");
                </script>

            </div>
            <div id="snackbar"></div>
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
    $data = &$this->data;
    $ctx = &$data['context'];
    $ctx['violations'] = [];
    $command = $this->data['commands']['command'];
    if ($command === 'delete') {
        $delete_id = $this->data['commands']['delete_id'];
        $this->part()->delete($delete_id);
    } else if ($command === 'add') {
        $add = &$data['add'];
        $this->part()->addNewItem($data['context']['id'], $add['type'], $add['value_string'], $add['value_number']);
        $add['type'] = '';
        $add['value_string'] = '';
        $add['value_number'] = '';
    }
    $this->refreshData($data['context']['id']);

    $this->data['status'] = 'OK';
}
]);
?>