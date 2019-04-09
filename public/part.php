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
            'violations[]' => '',
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
        'register' => ''
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
        <div>
            <div class="belt context">
                <h1 class="title">Part</h1>
            </div>

            <div class="belt bg-mono-09">
                <a href="home.php">Home</a>
                <a href="part-global.php">Part global</a>
                <span class="part_set property">
                    <a class="id none">Parent object</a>
                </span>
                <span class="part_set item">
                    <a class="id none">Parent array</a>
                </span>
            </div>

            <div>
                <?php $this->echoBy("path2"); ?>
            </div>

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

                    <div>
                        <div class="row context parent">
                            <div class="type none part_set property">
                                <label for="name">Name</label>
                                <input type="text" name="name">
                            </div>
                        </div>
                        <div class="row part_set part">
                            <div class="type is_string none">
                                <label for="value_string">String value</label>
                                <input type="text" name="value_string">
                            </div>
                        </div>
                        <div class="row part_set part">
                            <div class="type is_number none">
                                <label for="value_number">Number value</label>
                                <input type="text" name="value_number">
                            </div>
                        </div>
                        <div class="row id_copy_from id_copy_from_available">
                            <div class="type is_copy_from none">
                                <label for="id_copy_from">Copy from id</label>
                                <input type="text" name="id_copy_from">
                            </div>
                        </div>
                        <div class="row part_set property new">
                            <div class="id none">
                                <div><a class="object-new-prop">New property</a></div>
                                <div><a class="object-props">Properties</a></div>
                            </div>
                        </div>
                        <div class="row part_set item new">
                            <div class="id none">
                                <div><a class="array-new-item">New item</a></div>
                                <div><a class="array-items">Items</a></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="label"></div>
                            <button type="button" name="register">Register</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {

                var booq;
                (booq = new Booq(<?= $this->structsAsJSON() ?>))

                .path_snippet.callFunctionWithThis(pathSnippetBroker)
                    .end // of path_snippet

                    .context
                    .title.toText()
                    .parent
                    .type.eq("object").thenUntitoggle("none")

                    .end // of parent
                    .end // of context

                    .part_set

                    .property
                    .id.isTruthy().thenUntitoggle("none")
                    .id.toHref("part-object.php?id=:parent_id")
                    .parent_id.toText()
                    .name.toText()
                    .end // of property

                    .item
                    .id.isTruthy().thenUntitoggle("none")
                    .id.toHref("part-array.php?id=:parent_id")
                    .parent_id.toText()
                    .i.toText()
                    .end // of item

                    .part.extent(".modify")
                    .id.isTruthy().thenUntitoggle("none")
                    .id.linkExtra(".caption").toText()
                    .type.toText()
                    .end // of part

                    .part.extent(".new")
                    .id.isFalsy().thenUntitoggle("none")
                    .type.withValue()
                    .end // of part

                    .part
                    .type.linkExtra(".is_string").eq("string").thenUntitoggle("none")
                    .type.linkExtra(".is_number").eq("number").thenUntitoggle("none")
                    .type.linkExtra(".is_copy_from").eq("number").thenUntitoggle("none")
                    .value_string.withValue()
                    .value_number.withValue()
                    .end // of part

                    .property
                    .name.withValue()
                    .end // of property

                    .property.extent(".new")
                    .id.isTruthy().thenUntitoggle("none")
                    .end // of property

                    .item.extent(".new")
                    .id.isTruthy().thenUntitoggle("none")
                    .end // of item

                    .end // of part_set
                    //
                    .register.on("click", function() {
                        Global.snackbar.close();
                        booq.data.status = "";
                        axios.post("part.php", booq.data)
                            .then(function(response) {
                                console.log(response.data);
                                // booq.data = response.data;
                                booq
                                    .setData(response.data)
                                    .update();
                                // booq.data.message = response.data.message;
                                // if ("" !== booq.data.message) {
                                //     Global.snackbar.messageDiv.classList.add("warning");
                                //     Global.snackbar.maximize();
                                // }
                            })
                            .catch(Global.snackbarByCatchFunction());
                    })

                    .setData(<?= $this->dataAsJSON() ?>);
            };
        </script>
    </body>

    </html>
<?php

},
'post application/json' => function () {

    $data = &$this->data;
    $ctx = &$this->data['context'];
    $parent = &$ctx['parent'];
    $part_set = &$data['part_set'];

    if ($part_set['part']['id'] > 0) {
        //
        // Modify
        //
        if ($parent['id'] > 0) {
            // property
            if ($parent['type'] === 'object') {
                $new_part_object = $this->part()->setProperty($part_set['property'], $part_set['part']);
                $part_set['part']['id'] = $new_part_object['child_id'];
            } else if ($parent['type'] === 'array') {
                // item
                $new_part_array = $this->part()->setItem($part_set['item'], $part_set['part']);
                $part_set['part']['id'] = $new_part_array['child_id'];
            } else {
                $ctx['message'] = 'Illegal type for parent.';
            }
        } else {
            // global
            $new_part = $this->part()->setPart($part_set['part']);
            $part_set['part']['id'] = $new_part['id'];
        }
    } else {
        //
        // New
        //
        if ($parent['id'] > 0) {
            // property
            if ($parent['type'] === 'object') {
                $new_part_object = $this->part()->addNewProperty($parent['id'], $part_set['property']['name'], $part_set['part']['type'], $part_set['part']['value_string'], $part_set['part']['value_number']);
                $part_set['part']['id'] = $new_part_object['child_id'];
            } else if ($parent['type'] === 'array') {
                // item
                $new_part_array = $this->part()->addNewItem($parent['id'],  $part_set['part']['type'], $part_set['part']['value_string'], $part_set['part']['value_number']);
                $part_set['part']['id'] = $new_part_array['child_id'];
            } else {
                $ctx['message'] = 'Illegal type for parent.';
            }
        } else {
            // global
            $part = $part_set['part'];
            $new_part = $this->part()->addPart($part['type'], $part['value_string'], $part['value_number']);
            $part_set['part']['id'] = $new_part['id'];
        }
    }

    $this->refreshData($part_set['part']['id'], $part_set['property']['id'] > 0 ? $part_set['property']['parent_id'] : $part_set['item']['parent_id']);
    $this->data['status'] = 'OK';
}
]);
?>