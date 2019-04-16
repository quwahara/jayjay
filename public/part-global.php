<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'partxs[]' => [
            'parts',
            'part_properties',
            'part_items',
        ],
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'refreshData' => function () {

        $this->data['partxs'] = $this->dao('parts')->attFetchAll(
            'select p.* '
                . ', null as parent_id '
                . ', null as parent_type '
                . ', p.id as child_id '
                . ', null as i '
                . ', null as name '
                . 'from parts p '
                . ' left outer join part_items i '
                . '     on p.id = i.child_id '
                . ' left outer join part_properties r '
                . '     on p.id = r.child_id '
                . 'where i.child_id is null '
                . 'and r.child_id is null '
                . ' ',
            []
        );

        $this->data['commands'] = [
            'command' => '',
            'delete_id' => 0,
        ];
        return $this;
    },
    'get' => function () {
        $this->refreshData();
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
        <title>Part global</title>
    </head>

    <body>
        <div>
            <div class="belt">
                <h1>Part global</h1>
            </div>

            <div class="belt bg-mono-09">
                <a href="home.php">Home</a>
                <a href="part-global.php">Part global</a>
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
                                    <th class="">id</th>
                                    <th class="">type</th>
                                    <th class="">string value</th>
                                    <th class="">number value</th>
                                </tr>
                            </thead>
                            <tbody class="partxs">
                                <tr>
                                    <td><button type="button" class="id command">&times;</button></td>
                                    <td><a class="id caption"></a></td>
                                    <td class="type"></td>
                                    <td class="value_string"></td>
                                    <td class="value_number"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {
                var booq;
                (booq = new Booq(<?= $this->structsAsJSON() ?>))

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
                                                axios.post("part-global.php", booq.data)
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
                            .i.toText()
                            .id.linkExtra(".caption").toText()
                            .type.toText()
                            .value_string.toText()
                            .value_number.toText();
                    })
                    .setData(<?= $this->dataAsJSON() ?>);
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