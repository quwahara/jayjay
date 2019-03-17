<span class="path-snippet">
    <a href="part-global.php">(global)</a>
    <span class="path-snippet-iterator">
        <span>
            <span class="">/</span>
            <span class="path-snippet-given"></span>
            <a class="path-snippet-id"></a>
            <span class="path-snippet-type"></span>
            <span class="path-snippet-value"></span>
        </span>
    </span>
</span>
<script>
    var brokerPath;
    if (typeof brokerPath === "undefined") {
        brokerPath = function(path) {
            path.link(".path-snippet-iterator").each(function(element) {
                this
                    .id.link("a.path-snippet-id").toText()
                    .link("a.path-snippet-id, a.path-snippet-label").toHref(function(value) {
                        if (value.type === "object") {
                            return "part-object.php" + "?id=" + value.id;
                        } else if (value.type === "array") {
                            return "part-array.php" + "?id=" + value.id;
                        } else {
                            return "part.php" + "?id=" + value.id;
                        }
                    })
                    .link(".path-snippet-given").to2(function(src, value) {
                        var family = "";
                        if (value.sub_type === "global") {
                            family = "";
                        } else if (value.sub_type === "property") {
                            family = "[" + value.name + "]";
                        } else if (value.sub_type === "item") {
                            family = "[" + value.i + "]";
                        } else {
                            // unexpected
                        }
                        // "this" is issued element
                        this.textContent = family;
                    })
                    .link(".path-snippet-type").to2(function(src, value) {
                        this.textContent = value.type;
                    })
                    .link(".path-snippet-value").to2(function(src, value) {
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