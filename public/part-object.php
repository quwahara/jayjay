<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'context' => [
            'id' => 0,
            'parent_id' => 0,
            'parent_type' => '',
            'parent_part_object' => false,
            'parent_part_array' => false,
        ],
        'partxs[]' => [
            'parts',
            'part_objects',
        ],
        'add_part' => [
            'part',
            'part_object',
            'add_value_available' => false,
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'attrs' => [
        'part',
        'part_object',
    ],
    'methods' => [
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

            $ctx['parent_part_object'] = false;
            if ($part_object = $this->dao('part_object')->attFindOneBy(['child_id' => $ctx['id']])) {
                $ctx['parent_type'] = 'object';
                $ctx['parent_id'] = $part_object['parent_id'];
                $ctx['parent_part_object'] = true;
            }

            $ctx['parent_part_array'] = false;
            if ($part_array = $this->dao('part_array')->attFindOneBy(['child_id' => $ctx['id']])) {
                $ctx['parent_type'] = 'array';
                $ctx['parent_id'] = $part_array['parent_id'];
                $ctx['parent_part_array'] = true;
            }

            $this->data['add_part']['type'] = 'string';

            $this->data['commands'] = [
                'command' => '',
                'delete_id' => 0,
            ];
            return $this;
        },
    ],
    'get' => function () {
        $this->refreshData($this->getRequest('id', 0));
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
    <title>Part object</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Part object</h1>
        </div>

        <div class="belt bg-mono-09">
            <a href="home.php">Home</a>
            <a href="part-global.php">Part global</a>
            <a class="parent_part_object none">Parent object</a>
            <a class="parent_part_array none">Parent array</a>
        </div>

        <div class="contents">
            <div class="row">
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
                                <td class="value"></td>
                            </tr>
                        </tbody>
                    </table>
                    <table>
                        <thead>
                            <tr>
                                <th class="">+</th>
                                <th class="">name</th>
                                <th class="">type</th>
                                <th class="add_value_available none">value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><button type="button" class="add">+</button></td>
                                <td><input class="add_name h5v" type="text"></td>
                                <td>
                                    <select class="add_type" name="type">
                                        <option value="string">String</option>
                                        <option value="number">Number</option>
                                        <option value="object">Object</option>
                                        <option value="array">Array</option>
                                    </select>
                                </td>
                                <td><input class="add_value add_value_available none h5v" type="text"></td>
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
        .name.link(".add_name").toAttrs()
        .value.link(".add_value").toAttrs()
        .setStructureAsData()
        ;


        var booq;
        (booq = new Booq(<?= $this->structsAsJSON() ?>))
        
        .context
        .id.toText()
        .parent_part_object.antitogglesClass("none")
        .link(".parent_part_object").toHref("part-object.php?id=:parent_id")
        .parent_part_array.antitogglesClass("none")
        .link(".parent_part_array").toHref("part-array.php?id=:parent_id")
        .link(".add_property").toHref("part.php?parent_type=object&parent_id=:id")
        .end

        .partxs.each(function (element) {
            this
            .link(".id").toHref(function (value) {
                if (value.type === "string" || value.type === "number") {
                    return "part.php"
                    + "?id=" + value.id
                    ;
                } else if (value.type === "array") {
                    return "part-array.php"
                    + "?id=" + value.id
                    ;
                } else if (value.type === "object") {
                    return "part-object.php"
                    + "?id=" + value.id
                    ;
                } else {
                    //
                }
            })
            .name.toText()
            .id.toText()
            .type.toText()
            .value.toText()
            ;
            Booq.q(element).q("button.delete").on("click", (function (self) {
                return function (event) {
                    Global.modal.create({
                        body: "id:" + self.data.id + " を削除してもよろしいですか",
                        ok: {
                            onclick: function () {
                                console.log(self.data);
                                booq.data.status = "";
                                booq.data.commands.command = "delete";
                                booq.data.commands.delete_id = self.data.id;
                                axios.post("part-object.php", booq.data)
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
                            }
                        }
                    })
                    .open();

                };
            })(this));
        })
        .add_part
        .name.link("input.add_name").withValue()
        .type.link("select.add_type").withValue()
        .type.link("select.add_type").on("change", function() { booq.update(); })
        .setUpdate(function (data) {
            data.add_value_available = data.type === "string" || data.type === "number";
        })
        .add_value_available.antitogglesClass("none")
        .value.link("input.add_value").withValue()
        .end
        .setData(<?= $this->dataAsJSON() ?>)
        .update()
        ;

        Booq.q("button.add").on("click", function (event) {
            
            if(!Global.snackbarByVlidity("input.add_name")) return;

            Global.modal.create({
                body: "追加してもよろしいですか",
                ok: {
                    onclick: function () {
                        booq.data.status = "";
                        booq.data.commands.command = "add";
                        axios.post("part-object.php", booq.data)
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
    $data = &$this->data;
    $command = $data['commands']['command'];
    if ($command === 'delete') {
        $delete_id = $data['commands']['delete_id'];
        $this->part()->delete($delete_id);
    } else if ($command === 'add') {
        $add_part = &$data['add_part'];
        $this->part()->addNewProperty($data['context']['id'], $add_part['name'], $add_part['type'], $add_part['value']);
        $add_part['name'] = '';
        $add_part['type'] = '';
        $add_part['value'] = '';
    }
    $this->refreshData($data['context']['id']);
    $data['status'] = 'OK';
}
]);
?>
