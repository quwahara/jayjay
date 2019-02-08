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
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'methods' => [
        'refreshData' => function () {

            $ctx = &$this->data['context'];
            $ctx['id'] = $this->getRequest('id', 0);

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

            $this->data['commands'] = [
                'command' => '',
                'delete_id' => 0,
            ];
            return $this;
        },
    ],
    'get' => function () {
        $this->refreshData();
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
        Global.snackbar("#snackbar");

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
            Booq.q(element).q("button").on("click", (function (self) {
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
        .setData(<?= $this->dataAsJSON() ?>)
        ;
    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function () {
    $data = $this->data;
    $command = $this->data['commands']['command'];
    if ($command === 'delete') {
        $delete_id = $this->data['commands']['delete_id'];
        $this->part()->delete($delete_id);
    }
    $this->refreshData();

    $this->data['status'] = 'OK';
}
]);
?>
