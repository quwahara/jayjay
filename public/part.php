<?php (require __DIR__ . '/../jj/JJ.php')([
    /*

## Conceivable statuses

no parent, no target
no parent, target exists
parent exists, parent is object, no target
parent exists, parent is array, no target
parent exists, parent is object, target exists
parent exists, parent is array, target exists

    */

    'init' => function () {
        $this->initStructsBy('path2');
    },
    'structs' => [
        'context' => [
            'title' => '',
            'id' => 0,
            'parent' => [
                'id' => 0,
                'type' => '',
            ],
            'message' => '',
        ],
        'part_set' => [
            'part' => [
                'part'
            ],
            'property' => [
                'part_object'
            ],
            'item' => [
                'part_array'
            ],
        ],
        'id_copy_from' => 0,
    ],
    'attrs' => [
        'part_set' => [
            'part' => [
                'part',
            ],
            'property' => [
                'part_object'
            ],
        ],
    ],
    'refreshData' => function ($id, $parent_id) {

        $data = &$this->data;
        $ctx = &$this->data['context'];

        $data['path_snippet']['paths'] = [];

        //
        // Modify
        //
        if ($id > 0) {

            $ctx['title'] = 'Modify part';

            $data['path_snippet'] = ['paths' => $this->part()->path($id)];

            $part_set = $this->part()->findPartSet($id);
            if (is_null($part_set['part'])) {
                $ctx['message'] = 'The id was not found.';
                return;
            }

            $data['part_set']['part'] = $part_set['part'];

            if (!is_null($part_set['property'])) {

                $ctx['title'] .= ' for object';

                $ctx['parent'] = [
                    'id' => $part_set['property']['parent_id'],
                    'type' => 'object',
                ];
                $data['part_set']['property'] = $part_set['property'];
            } else if (!is_null($part_set['item'])) {

                $ctx['title'] .= ' for array';

                $ctx['parent'] = [
                    'id' => $part_set['item']['parent_id'],
                    'type' => 'array',
                ];
                $data['part_set']['item'] = $part_set['item'];
            } else {
                $ctx['title'] .= ' for global';
            }
        } else {
            //
            // New
            //
            $ctx['title'] = 'New part';

            if ($parent_id > 0) {
                $data['path_snippet'] = ['paths' => $this->part()->path($parent_id)];
                $parent_part = $this->part()->findPart($parent_id);
                if (is_null($parent_part)) {
                    $ctx['message'] = 'The id was not found.';
                    return;
                }
                $ctx['parent'] = [
                    'id' => $parent_part['id'],
                    'type' => $parent_part['type'],
                ];
                $ctx['title'] .= ' for ' . $parent_part['type'];
            } else {
                $ctx['title'] .= ' for global';
            }

            $data['part_set']['part']['type'] = 'string';
        }
    },
    'get' => function () {
        //
        $part_id = $this->getRequestAsInt('id', 0);
        $parent_id = $this->getRequestAsInt('parent_id', 0);
        $this->refreshData($part_id, $parent_id);
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
        <title><?= $this->data['context']['title'] ?></title>
    </head>

    <body>
        <script>
            var attrs = new Booq(<?= $this->attrsAsJSON() ?>);
            var structs = new Booq(<?= $this->structsAsJSON() ?>);
        </script>

        <div>
            <div class="belt context">
                <h1 class="title">Part</h1>
            </div>
            <script>
                structs.context.title.toText();
            </script>

            <div class="belt bg-mono-09 context parent">
                <a href="home.php">Home</a>
                <a href="part-global.php">Part global</a>

                <span class="object">
                    <a class="type none id object">Parent object</a>
                </span>
                <script>
                    structs.context.parent.extent(".belt .object")
                        .type.eq("object").thenUntitoggle("none")
                        .id.linkExtra(".part").toHref("part.php?parent_id=:id")
                        .id.linkExtra(".object").toHref("part-object.php?id=:id");
                </script>

                <span class="array">
                    <a class="type none id array">Parent array</a>
                </span>
                <script>
                    structs.context.parent.extent(".belt .array")
                        .type.eq("array").thenUntitoggle("none")
                        .id.linkExtra(".part").toHref("part.php?parent_id=:id")
                        .id.linkExtra(".array").toHref("part-array.php?id=:id");
                </script>
            </div>

            <div>
                <?php $this->echoBy("path2"); ?>
            </div>
            <script>
                structs.path_snippet.callFunctionWithThis(pathSnippetBroker);
            </script>

            <div class="contents">
                <form method="post">
                    <div class="part_set property">
                        <!-- when exists property -->
                        <div class="id none">
                            <div class="row">
                                <label>Parent Id</label>
                                <span class="parent_id"></span>
                            </div>
                            <div class="row">
                                <label>Parent Type</label>
                                <span>Object</span>
                            </div>
                            <div class="row">
                                <label>Name</label>
                                <span class="name"></span>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.part_set.property
                            .id.isTruthy().thenUntitoggle("none")
                            .id.toHref("part-object.php?id=:parent_id")
                            .parent_id.toText()
                            .name.toText()
                            .endProperty;
                    </script>

                    <div class="part_set item">
                        <!-- when exists item -->
                        <div class="id none">
                            <div class="row">
                                <label>Parent Id</label>
                                <span class="parent_id"></span>
                            </div>
                            <div class="row">
                                <label>Parent Type</label>
                                <span>Array</span>
                            </div>
                            <div class="row">
                                <label>I</label>
                                <span class="i"></span>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.part_set.item
                            .id.isTruthy().thenUntitoggle("none")
                            .id.toHref("part-array.php?id=:parent_id")
                            .parent_id.toText()
                            .i.toText()
                            .endItem;
                    </script>

                    <div class="part_set part modify">
                        <!-- when exists part -->
                        <div class="id none">
                            <div class="row">
                                <label>Id</label>
                                <span class="id caption"></span>
                            </div>
                            <div class="row">
                                <label>Type</label>
                                <span class="type"></span>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.part_set.part.extent(".modify")
                            .id.isTruthy().thenUntitoggle("none")
                            .id.linkExtra(".caption").toText()
                            .type.toText()
                            .endPart;
                    </script>

                    <div class="part_set part new">
                        <!-- when not exists part -->
                        <div class="id none">
                            <div class="row">
                                <label>Id</label>
                                <span></span>
                            </div>
                            <div class="row">
                                <label>Type</label>
                                <select name="type">
                                    <option value="string">String</option>
                                    <option value="number">Number</option>
                                    <option value="object">Object</option>
                                    <option value="array">Array</option>
                                    <option value="copy_from">Copy from</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <script>
                        structs.part_set.part.extent(".new")
                            .id.isFalsy().thenUntitoggle("none")
                            .type.withValue()
                            .endPart;
                    </script>

                    <div>
                        <div class="row context parent object">
                            <div class="type none part_set property">
                                <label for="name">Name</label>
                                <input type="text" name="name">
                            </div>
                        </div>
                        <script>
                            structs
                                .context
                                .___.parent.extent(".object")
                                .___.___.type.eq("object").thenUntitoggle("none")
                                .___.___.endParent
                                .___.endContext
                                .part_set
                                .___.property
                                .___.___.name.withValue()
                                .___.___.endProperty;
                        </script>

                        <div class="row part_set part">
                            <div class="type is_string none">
                                <label for="value_string">String value</label>
                                <input type="text" name="value_string">
                            </div>
                        </div>
                        <script>
                            attrs.part_set.part
                                .value_string.linkPreferred("lower_name").toAttrs();

                            structs.part_set.part
                                .type.linkExtra(".is_string").eq("string").thenUntitoggle("none")
                                .value_string.withValue();
                        </script>

                        <div class="row part_set part">
                            <div class="type is_number none">
                                <label for="value_number">Number value</label>
                                <input type="text" name="value_number">
                            </div>
                        </div>
                        <script>
                            structs.part_set.part
                                .type.linkExtra(".is_number").eq("number").thenUntitoggle("none")
                                .value_number.withValue();
                        </script>

                        <div class="row part_set part">
                            <div class="type is_copy_from none">
                                <label for="id_copy_from">Copy from id</label>
                                <input type="text" name="id_copy_from">
                            </div>
                        </div>
                        <script>
                            structs
                                .id_copy_from.withValue()
                                .part_set
                                .___.part
                                .___.___.type.linkExtra(".is_copy_from").eq("copy_from").thenUntitoggle("none");
                        </script>

                        <div class="row child part_set part">
                            <div class="id none">
                                <div><a class="id part">New property</a></div>
                                <div><a class="id object">Properties</a></div>
                            </div>
                        </div>
                        <script>
                            structs.extent(".child").part_set.part
                                .id.linkExtra(".none").isTruthy().thenUntitoggle("none")
                                .id.linkExtra(".part").toHref("part.php?parent_id=:id")
                                .id.linkExtra(".object").toHref("part-object.php?id=:id");
                            structs.extent("");
                        </script>

                        <div class="row context parent array">
                            <div class="type none">
                                <div><a class="id part">New item</a></div>
                                <div><a class="id array">Items</a></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="label"></div>
                            <button type="button" name="register">Register</button>
                        </div>
                        <script>
                            structs
                                .linkExtra("button[name='register']").on("click", function() {
                                    Global.snackbar.close();
                                    var structs = this;
                                    structs.data.status = "";

                                    if (!Global.snackbarByVlidity(
                                            structs.part_set.part.value_string.selector("name")
                                        )) return;

                                    Global.modal.create({
                                            body: "追加してもよろしいですか",
                                            ok: {
                                                onclick: function() {
                                                    Global.snackbar.close();
                                                    axios.post("part.php", structs.data)
                                                        .then(function(response) {
                                                            console.log(response.data);
                                                            structs.setData(response.data);
                                                            if (!Global.snackbarByViolations(structs.data.context.violations)) return;
                                                        })
                                                        .catch(Global.snackbarByCatchFunction());
                                                }
                                            }
                                        })
                                        .open();
                                });
                        </script>

                        <script>
                            window.onload = function() {
                                attrs.setStructureAsData();
                                structs.setData(<?= $this->dataAsJSON() ?>);
                            };
                        </script>

                    </div>
                </form>
            </div>
        </div>
    </body>

    </html>
<?php

},
'post application/json' => function () {

    $data = &$this->data;
    $ctx = &$this->data['context'];
    $parent = &$ctx['parent'];
    $part_set = &$data['part_set'];
    $part = &$part_set['part'];
    $type = $part['type'];

    $attrs = &$this->attrs;

    if ($type === 'string' || $type === 'number') {
        $fieldName = "value_{$type}";
        $propValue = $part[$fieldName];
        $ctx['violations'] = $this->validate($fieldName, $type, $propValue, $attrs['part_set']['part'][$fieldName]);
    } else {
        $ctx['violations'] = [];
    }

    if (0 < count($ctx['violations'])) {
        return;
    }

    if ($part['id'] > 0) {
        //
        // Modify
        //
        if ($parent['id'] > 0) {
            // property
            if ($parent['type'] === 'object') {
                $new_part_object = $this->part()->setProperty($part_set['property'], $part);
                $part['id'] = $new_part_object['child_id'];
            } else if ($parent['type'] === 'array') {
                // item
                $new_part_array = $this->part()->setItem($part_set['item'], $part);
                $part['id'] = $new_part_array['child_id'];
            } else {
                $ctx['message'] = 'Illegal type for parent.';
            }
        } else {
            // global
            $new_part = $this->part()->setPart($part);
            $part['id'] = $new_part['id'];
        }
    } else {
        //
        // New
        //
        if ($type === 'copy_from') {
            // copy_from
            $part['id'] = $this->part()->cloneById($parent['id'], $part_set['property']['name'], $data['id_copy_from']);
        } else if ($parent['id'] > 0) {
            // property
            if ($parent['type'] === 'object') {
                $new_part_object = $this->part()->addNewProperty($parent['id'], $part_set['property']['name'], $type, $part['value_string'], $part['value_number']);
                $part['id'] = $new_part_object['child_id'];
            } else if ($parent['type'] === 'array') {
                // item
                $new_part_array = $this->part()->addNewItem($parent['id'],  $type, $part['value_string'], $part['value_number']);
                $part['id'] = $new_part_array['child_id'];
            } else {
                $ctx['message'] = 'Illegal type for parent.';
            }
        } else {
            // global
            $new_part = $this->part()->addPart($type, $part['value_string'], $part['value_number']);
            $part['id'] = $new_part['id'];
        }
    }

    $this->refreshData($part['id'], $parent['id']);
    $this->data['status'] = 'OK';
}
]);
?>