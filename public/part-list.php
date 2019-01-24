<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'context' => [
            'parent_type' => '',
            'parent_id' => 0,
        ],
        'partxs[]' => [
            'parts',
            'part_objects',
            'part_arrays',
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'methods' => [
        'refreshData' => function () {

            $this->data['context']['parent_type'] = $this->getRequest('parent_type', '');
            $this->data['context']['parent_id'] = $this->getRequest('parent_id', 0);
            $ctx = &$this->data['context'];

            if ($ctx['parent_type'] === 'array') {
                $this->data['partxs'] = $this->dao('parts', ['part_arrays'])->attFetchAll(
                    'select p.*  '
                        . ', a.parent_id '
                        . ', \'' . $ctx['parent_type'] . '\' as parent_type '
                        . ', a.child_id '
                        . ', a.i '
                        . ', null as name '
                        . 'from parts p '
                        . ' inner join part_arrays a '
                        . '     on p.id = a.child_id '
                        . 'where a.parent_id = :parent_id '
                        . ' ',
                    ['parent_id' => $ctx['parent_id']]
                );
            } else if ($ctx['parent_type'] === 'object') {
                $this->data['partxs'] = $this->dao('parts', ['part_objects'])->attFetchAll(
                    'select p.* '
                        . ', o.parent_id '
                        . ', \'' . $ctx['parent_type'] . '\' as parent_type '
                        . ', o.child_id '
                        . ', null as i '
                        . ', o.name '
                        . 'from parts p '
                        . ' inner join part_objects o '
                        . '     on p.id = o.child_id '
                        . 'where o.parent_id = :parent_id '
                        . ' ',
                    ['parent_id' => $ctx['parent_id']]
                );
            } else {
                $this->data['partxs'] = $this->dao('parts')->attFetchAll(
                    'select p.* '
                        . ', null as parent_id '
                        . ', null as parent_type '
                        . ', p.id as child_id '
                        . ', null as i '
                        . ', null as name '
                        . 'from parts p '
                        . ' left outer join part_arrays a '
                        . '     on p.id = a.child_id '
                        . ' left outer join part_objects o '
                        . '     on p.id = o.child_id '
                        . 'where a.child_id is null '
                        . 'and o.child_id is null '
                        . ' ',
                    []
                );
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
    <title>Part list</title>
</head>
<body>
    <div>
        <div class="belt">
            <h1>Part list</h1>
        </div>

        <div class="belt bg-mono-09">
            <div><a href="home.php">Home</a></div>
        </div>

        <div class="contents">
            <div class="row">
                <a class="new_child" href="part.php">New</a>
            </div>

            <form method="post">
                <div class="row">
                    <table>
                        <thead>
                            <tr>
                                <th class="">&times;</th>
                                <th class="">name</th>
                                <th class="">i</th>
                                <th class="">id</th>
                                <th class="">type</th>
                                <th class="">value</th>
                            </tr>
                        </thead>
                        <tbody class="partxs">
                            <tr>
                                <td><button type="button" class="delete">&times;</button></td>
                                <td><a class="name"></a></td>
                                <td><a class="i"></a></td>
                                <td><a class="id"></a></td>
                                <td class="type"></td>
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

        var booq;
        (booq = new Booq(<?= $this->structsAsJSON() ?>))
        .context
        .toHref(function (value) {
            return "part.php?parent_type=" + value.parent_type + "&parent_id= " + value.parent_id;
        })
        .end

        .partxs.each(function (element) {
            this
            .link(".id").toHref(function (value) {
                if (value.type === "string" || value.type === "number") {
                    return "part.php"
                    + "?id=" + value.id
                    + "&parent_type=" + (value.parent_type || "")
                    + "&parent_id=" + (value.parent_id || "")
                    ;
                } else {
                    return "part-list.php"
                    + "?parent_type=" + value.type
                    + "&parent_id=" + value.id
                    ;
                }
            })
            .name.toText()
            .i.toText()
            .id.toText()
            // .id.toHref("part.php?id=:id")
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
                                axios.post("part-list.php", booq.data)
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
        $this->dao('part')->attDeleteById($delete_id);
        $context = $this->data['context'];
        if ($context['parent_type'] === 'array') {
            $this->dao('part_arrays')->attDeleteBy([
                'parent_id' => $context['parent_id'],
                'child_id' => $delete_id
            ]);
        }
    }
    $this->refreshData();

    $this->data['status'] = 'OK';
}
]);
?>