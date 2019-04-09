<?php 
return [
    'structs' => [
        'path_snippet' => [
            'paths[]' => [
                'part',
                'sub_type' => '',
                'part_object',
                'part_array',
            ]
        ],
    ],
    'echo' => function () {
        ?>
<span class="">
    <a href="part-global.php">(global)</a>
    <span class="path_snippet paths">
        <span>
            <span class="">/</span>
            <span class="given"></span>
            <a class="id"></a>
            <span class="type"></span>
            <span class="value"></span>
        </span>
    </span>
</span>
<script>
    var pathSnippetBroker;
    if (typeof pathSnippetBroker === "undefined") {
        pathSnippetBroker = function(path_snippet) {
            path_snippet.paths.each(function(element, index) {
                this
                    .id.toText()
                    .linkExtra(" .id").toHref(function(value) {
                        if (value.type === "object") {
                            return "part-object.php" + "?id=" + value.id;
                        } else if (value.type === "array") {
                            return "part-array.php" + "?id=" + value.id;
                        } else {
                            return "part.php" + "?id=" + value.id;
                        }
                    })
                    .linkExtra(" .given").to(function(src, value) {
                        var given = "";
                        if (value.sub_type === "global") {
                            given = "";
                        } else if (value.sub_type === "property") {
                            given = "[" + value.name + "]";
                        } else if (value.sub_type === "item") {
                            given = "[" + value.i + "]";
                        } else {
                            // unexpected
                        }
                        // "this" is issued element
                        this.textContent = given;
                    })
                    .linkExtra(" .type").to(function(src, value) {
                        this.textContent = value.type;
                    })
                    .linkExtra(" .value").to(function(src, value) {
                        var valueValue = "";
                        if (value.type === "object") {
                            valueValue = "";
                        } else if (value.type === "array") {
                            valueValue = "";
                        } else if (value.type === "string") {
                            valueValue = '"' + value.value_string + '"';
                        } else if (value.type === "number") {
                            valueValue = value.value_number;
                        } else {
                            // unexpected
                        }
                        // "this" is issued element
                        this.textContent = valueValue;
                    });
            });
        }
    }
</script>
<?php

}
];
?> 