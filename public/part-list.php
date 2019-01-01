<?php (require __DIR__ . '/../jj/JJ.php')([
    'structs' => [
        'parts[]',
        'commands' => [
            'command' => '',
            'delete_id' => 0,
        ]
    ],
    'get' => function (\JJ\JJ $jj) {
        $jj->data['parts'] = $jj->dao('parts')->attFindAllBy([]);
        $jj->data['commands'] = [
            'command' => '',
            'delete_id' => 0,
        ];
        ?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="js/lib/node_modules/normalize.css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/fontawesome-free-5.5.0-web/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/global.css">
    <script src="js/lib/node_modules/axios/dist/axios.js"></script>
    <script src="js/brx/booq.js"></script>
    <script src="js/lib/global.js"></script>
    <?= '<style>' . $jj->css()->style . '</style>' ?>
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
                                <th class="">id</th>
                                <th class="">type</th>
                                <th class="">value</th>
                            </tr>
                        </thead>
                        <tbody class="parts">
                            <tr>
                                <td><button type="button" class="delete">&times;</button></td>
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
        (booq = new Booq(<?= $jj->structsAsJSON() ?>))
        .parts.each(function (element) {
            this
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
        .setData(<?= $jj->dataAsJSON() ?>)
        ;
    };
    </script>
</body>
</html>
<?php

},
'post application/json' => function (\JJ\JJ $jj) {
    $data = $jj->data;
    $command = $jj->data['commands']['command'];
    if ($command === 'delete') {
        $jj->dao('part')->attDeleteById($jj->data['commands']['delete_id']);
    }
    $jj->data['parts'] = $jj->dao('parts')->attFindAllBy([]);
    $jj->data['status'] = 'OK';
}
]);
?>
