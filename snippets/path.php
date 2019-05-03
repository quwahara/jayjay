<?php
return [
    'structs' => [
        'path_snippet' => [
            'paths[]' => [
                'part',
                'sub_type' => '',
                'part_property',
                'part_item',
            ]
        ],
    ],
    'echo' => function () {
        ?>
    <span class="">
        <span class="path_snippet paths">
            <span>
                <span class="">/</span>
                <a class="id" title=""></a>
            </span>
        </span>
    </span>
    <script>
        var pathSnippetBroker;
        if (typeof pathSnippetBroker === "undefined") {
            pathSnippetBroker = function(path_snippet) {
                path_snippet.paths.each(function(element, index) {
                    this
                        .linkExtra(" .id").to(function(src, value) {
                            var text = "";
                            if (value.sub_type === "root") {
                                text = "(root)";
                            } else if (value.sub_type === "property") {
                                text = value.name;
                            } else if (value.sub_type === "item") {
                                text = "[" + value.i + "]";
                            } else {
                                text = "(" + value.type + ")";
                            }

                            // "this" is issued element
                            this.textContent = text;
                        })
                        .linkExtra(" .id").toHref(function(value) {
                            if (value.type === "object") {
                                return "part-object.php" + "?id=" + value.id;
                            } else if (value.type === "array") {
                                return "part-array.php" + "?id=" + value.id;
                            } else {
                                return "part.php" + "?id=" + value.id;
                            }
                        })
                        .id.toAttr("title")
                        ;
                });
            }
        }
    </script>
<?php

}
];
?>