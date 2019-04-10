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
        'id_copy_from' => 0,
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
                <span class="context parent object">
                    <a class="type none id object">Parent object</a>
                </span>
                <span class="context parent array">
                    <a class="type none id array">Parent array</a>
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
                        <div class="row context parent object">
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
                        <div class="row part_set part">
                            <div class="type is_copy_from none">
                                <label for="id_copy_from">Copy from id</label>
                                <input type="text" name="id_copy_from">
                            </div>
                        </div>
                        <div class="row context parent object">
                            <div class="type none">
                                <div><a class="id part">New property</a></div>
                                <div><a class="id object">Properties</a></div>
                            </div>
                        </div>
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
                    </div>
                </form>
            </div>
        </div>
        <script>
            window.onload = function() {

                var booq;
                (booq = new Booq(<?= $this->structsAsJSON() ?>))

                .path_snippet.callFunctionWithThis(pathSnippetBroker)
                    .endPath_snippet

                    .context
                    .___.title.toText()

                    .___.parent.extent(".object")
                    .___.___.type.eq("object").thenUntitoggle("none")
                    .___.___.id.linkExtra(".part").toHref("part.php?parent_id=:id")
                    .___.___.id.linkExtra(".object").toHref("part-object.php?id=:id")
                    .___.___.endParent

                    .___.parent.extent(".array")
                    .___.___.type.eq("array").thenUntitoggle("none")
                    .___.___.id.linkExtra(".part").toHref("part.php?parent_id=:id")
                    .___.___.id.linkExtra(".array").toHref("part-array.php?id=:id")
                    .___.___.endParent

                    .___.endContext

                    .part_set

                    .___.property
                    .___.___.id.isTruthy().thenUntitoggle("none")
                    .___.___.id.toHref("part-object.php?id=:parent_id")
                    .___.___.parent_id.toText()
                    .___.___.name.toText()
                    .___.___.endProperty

                    .___.item
                    .___.___.id.isTruthy().thenUntitoggle("none")
                    .___.___.id.toHref("part-array.php?id=:parent_id")
                    .___.___.parent_id.toText()
                    .___.___.i.toText()
                    .___.___.endItem

                    .___.part.extent(".modify")
                    .___.___.id.isTruthy().thenUntitoggle("none")
                    .___.___.id.linkExtra(".caption").toText()
                    .___.___.type.toText()
                    .___.___.endPart

                    .___.part.extent(".new")
                    .___.___.id.isFalsy().thenUntitoggle("none")
                    .___.___.type.withValue()
                    .___.___.endPart

                    .___.part
                    .___.___.type.linkExtra(".is_string").eq("string").thenUntitoggle("none")
                    .___.___.type.linkExtra(".is_number").eq("number").thenUntitoggle("none")
                    .___.___.type.linkExtra(".is_copy_from").eq("copy_from").thenUntitoggle("none")
                    .___.___.value_string.withValue()
                    .___.___.value_number.withValue()
                    .___.___.endPart

                    .___.property
                    .___.___.name.withValue()
                    .___.___.endProperty

                    .___.property.extent(".new")
                    .___.___.id.isTruthy().thenUntitoggle("none")
                    .___.___.endProperty

                    .___.item.extent(".new")
                    .___.___.id.isTruthy().thenUntitoggle("none")
                    .___.___.endItem

                    .___.endPart_set
                    //
                    .id_copy_from.withValue()
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
        if ($part_set['part']['type'] === 'copy_from') {
            // copy_from
            $part_set['part']['id'] = $this->part()->cloneById($parent['id'], $part_set['property']['name'], $data['id_copy_from']);
        } else if ($parent['id'] > 0) {
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

    $this->refreshData($part_set['part']['id'], $parent['id']);
    $this->data['status'] = 'OK';
}
]);
?>