<html>

<head>
  <script src="./js/trax/trax.js"></script>
</head>

<body>
<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Services\Services;

try {
  $model = (function () {
    $S = Services::singleton();
    return null;
  })();
} catch (Exception $e) {
  print_r($e);
  return;
}

?>
  <div>
    <div>
      <h1>New Entity</h1>
    </div>
    <form>
      <div>Entity name</div>
      <div><input type="text"></div>

      <div>Field name</div>
      <div>
        <input type="text">
        <select>
        <option>Text</option>
        <option>Multi line text</option>
        <option>Dropdown</option>
        <option>Seigle Checkbox</option>
        <option>Multi checkboxes</option>
        <option>Radio button</option>
        </select>
      </div>

      <div><button type="button">Add field</button></div>

      <div><button type="button">OK</button><button type="button">Cancel</button><button type="button">Clear</button></div>

    </form>
  </div>
</body>

</html>
