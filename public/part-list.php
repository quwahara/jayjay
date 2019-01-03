<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'context' => [
            'parent_type' => '',
            'parent_id' => 0,
        ],
        'partxs[]' => [
            'parts',
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
            $context = $this->data['context'];

            if ($this->data['context']['parent_type'] === 'array') {
                $this->data['partxs'] = $this->dao('parts', ['part_arrays'])->attFetchAll(
                    'select p.*, a.parent_id, a.child_id, a.i  '
                        . 'from parts p'
                        . ' inner join part_arrays a '
                        . '     on p.id = a.child_id '
                        . 'where a.parent_id = :parent_id '
                        . ' ',
                    ['parent_id' => $context['parent_id']]
                );
            } else {
                $this->data['partxs'] = $this->dao('parts')->attFetchAll(
                    'select p.*, \'\' as i  '
                        . 'from parts p'
                        . ' left outer join part_arrays a '
                        . '     on p.id = a.child_id '
                        . 'where a.child_id is null '
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
    <script src="js/brx/booq.js"></script>
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
                <a href="part.php">New</a>
            </div>

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
                                <td><button type="button" class="delete">&times;</button></td>
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
        .partxs.each(function (element) {
            this
            .i.toText()
            .id.toText()
            .id.toHref("part.php?id=:id")
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
