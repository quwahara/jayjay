<?php (require __DIR__ . '/../jj/JJ.php')([
    'before' => function () {
        //
        $id = $this->getRequiredParam('id');

        $nnd = $this->assembly()->getSchemaNameAndDescriptions($id);

        $this->temps['name'] = $nnd['name'];

        $acs = $this->assembly()->toACS($nnd['descriptions']);

        $this->temps = array_merge($this->temps, $acs);


        $this->initAttrs($acs['attrs']);

        $this->initStructs([
            'columns[]' => [
                'name' => '',
                'type' => '',
            ],
            'id' => 0,
            'record' => $acs['struct'],
            'violation_set' => $acs['violation_set_structs'],
            'register' => '',
        ]);

        $this->data['columns'] = $acs['columns'];
        $this->data['violation_set'] = $acs['violation_set_data'];
    },
    'structs' => [
        //
        'head' => [
            'name' => '',
        ],
        'neck' => [
            'to_data' => '',
        ],
    ],
    'refreshData' => function ($id) {
        //
        $data = &$this->data;
        $temps = &$this->temps;

        $data['context']['violation_set'] = [];

        $data['head']['name'] = $temps['name'];
        $data['neck']['to_data'] = $temps['name'];

        $record = $this->part()->get($id);
        if (is_null($record)) {
            throw new Exception("The id was not found.");
        }
        $data['id'] = $record['___id'];
        $data['record'] = $record;
    },
    'get' => function () {
        //
        $id = $this->getRequest('id');
        $this->refreshData($id);
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
        <title>Data</title>
        <script>
            Booq.configure({
                traceStructure: true,
                traceQualify: true,
                traceSetData: true,
            });
            var attrs = new Booq(<?= $this->attrsAsJSON() ?>);
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>
    </head>

    <body>
        <div>
            <div class="belt head">
                <h1><span class="name"></span> <span>data</span></h1>
            </div>
            <script>
                structs.head.name.toText().end;
            </script>

            <div class="belt neck">
                <a href="home.php">Home</a>
                <a href="schema.php">Schema</a>
                <a class="to_data">Data</a>
                <script>
                    structs.neck.to_data.toHref("data.php?name=:to_data").end;
                </script>
            </div>

            <div class="contents">
                <form method="post">
                    <div class="ident">
                        <div class="row">
                            <div class="col-2">
                                <label>Id</label>
                                <div class="id"></div>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.extent(".ident").id.toText().end;
                    </script>

                    <div class="record">
                        <div class="row">
                            <div class="col-2">
                                <label></label>
                                <input class="input h5v" type="text">
                                <div class="violation_set violations">
                                    <div class="">
                                        <span class="message"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.record.each(function(element, nth, name, value) {
                            this.nameToClass().traceQualify();
                            this.linkExtra(" label").nameToText().traceQualify();
                            this.linkExtra(" label").nameToAttr("for");
                            this.linkExtra(" input").nameToAttr("name");
                            this.linkExtra(" input").withValue();
                            this.linkExtra(" .violation_set.violations").nameToClass();

                            structs.violation_set[name].violations
                                .link(this.fullPathSelector("nth_child") + " .violations")
                                .each(function(elm, index, item) {
                                    this.message.toText().traceQualify();
                                }).traceQualify();
                        });
                    </script>

                    <div class="row">
                        <div class="col-12">
                            <button name="register" type="button">Register</button>
                        </div>
                    </div>
                    <script>
                        structs.register.on("click", function(event) {
                            //
                            Global.modal.create({
                                    body: "保存します。よろしいですか",
                                    ok: {
                                        onclick: function() {
                                            console.log(structs.data);
                                            axios.post("detail.php", structs.data)
                                                .then(function(response) {
                                                    console.log(response.data);
                                                    var vset = response.data.violation_set;
                                                    for (var name in vset) {
                                                        var vs = vset[name].violations;
                                                        for (var j = 0; j < vs.length; ++j) {
                                                            var v = vs[j];
                                                            v.message = Global.getMsg("#violation-" + v.violation, v.params);
                                                        }
                                                    }

                                                    structs.violation_set.setData(vset);
                                                })
                                                .catch(Global.catcher(structs.data));
                                        }
                                    }
                                })
                                .open();
                        });
                    </script>

                </form>
            </div>
        </div>
        <script>
            window.onload = function() {
                structs.setData(<?= $this->dataAsJson() ?>);
            };
        </script>
    </body>

    </html>
<?php

},
'post application/json' => function () {

    $data = &$this->data;

    $valid = true;
    foreach ($this->temps['columns'] as $col) {
        $name = $col['name'];
        $violations = $this->validate2($col['type'], $data['record'][$name], $this->attrs[$name]);
        if (0 < count($violations)) {
            $data['violation_set'][$name]['violations'] = $violations;
            $valid = false;
        }
    }

    if ($valid) {
        $this->part()->setPrimitiveValueToProperty($data['id'], 'username', $data['record']['username']);
        $this->part()->setPrimitiveValueToProperty($data['id'], 'password', $data['record']['password']);
        $this->refreshData($data['id']);
    }

    $this->data['status'] = 'OK';
}
]);
?>