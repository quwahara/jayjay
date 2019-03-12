<span class="path-snippet">
    <span>
        <span class="">/</span>
        <a class="id"></a>
        <a class="id name"></a>
    </span>
</span>
<script>
    var brokerPath;
    if (typeof brokerPath === "undefined") {
        brokerPath = function(path) {
            path.link(".path-snippet").each(function(element) {
                this
                    .link(".id").toHref(function(value) {
                        if (value.sub_type === "global") {
                            return "part-global.php";
                        } else if (value.sub_type === "property") {
                            return "part-object.php" + "?id=" + value.id;
                        } else if (value.type === "array") {
                            return "part-array.php" + "?id=" + value.id;
                        } else {
                            //
                        }
                    })
                    .id.toText()
                    .name.toText();
            });
        }
    }
</script> 