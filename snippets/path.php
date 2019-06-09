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
                        .linkSimplex(" .id").to(function(element, data) {
                            var text = "";
                            if (data.sub_type === "root") {
                                text = "(root)";
                            } else if (data.sub_type === "property") {
                                text = data.name;
                            } else if (data.sub_type === "item") {
                                text = "[" + data.i + "]";
                            } else {
                                text = "(" + data.type + ")";
                            }

                            element.textContent = text;
                        })
                        .linkSimplex(" .id").toHref(function(data) {
                            if (data.type === "object") {
                                return "part-object.php" + "?id=" + data.id;
                            } else if (data.type === "array") {
                                return "part-array.php" + "?id=" + data.id;
                            } else {
                                return "part.php" + "?id=" + data.id;
                            }
                        })
                        .id.toAttr("title");
                });
            }
        }
    </script>
<?php

}
];
?>