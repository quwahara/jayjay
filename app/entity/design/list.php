<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Services\Services;

if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
  $content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
  $media_type = $content_type[0];
  
  if ($media_type == 'application/json') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $S = Services::singleton();
      $da = Services::singleton()->da();

      try {
        $payload = json_decode(file_get_contents('php://input'), true);
        $entityT = $da->getTableByTableName('entities');
        $entityV = ['entity_name' => $payload['entityName']];

        $sql = 'select * from entities where entity_name like :entity_name;';
        $founds = $da->findAll($entityT, $sql, $entityV);
        $payload = ["founds" => $founds];
  
      } catch (Exception $e) {
        $payload = [
          'exception' => print_r($e, TRUE)
        ];
      }
    } else {
      $payload = [
        "aa" => "11",
        "bb" => "22",
      ];
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($payload);
    exit();
  }
}

$er = '';
$dbg = 'aa';
try {
  $model = (function () {
    $S = Services::singleton();
    // Services::singleton()->initDbdec();
    // $S->da()->createTables();
    // return null;
    $da = $S->da();
    $sql = 'select * from entities where entity_name = :entity_name;';
    $vals = ['entity_name' => 'aaa'];
    $ret = $da->findOne($da->getTableByTableName('entities'), $sql, $vals);
    // $ret = print_r([
    //   'fields',
    //   $da->getTableByTableName('fields'),
    //    $ret,
    //   ], TRUE);
    return $ret;
  })();
} catch (Exception $e) {
  $er = print_r($e, TRUE);
}
?>
<html>

<head>
  <script src="../../../js/lib/node_modules/axios/dist/axios.js"></script>
  <script src="../../../js/trax/trax.js"></script>
</head>

<body>
  <div>
    <div>
      <h1>List Entities</h1>
    </div>
    <div><a href="menus.php">menus</a></div>
    <form name="formA">

      <div><button type="button" id="okBtn">OK</button><button type="button">Cancel</button><button type="button">Clear</button></div>
      <div><button type="button" id="apiTestBtn">API Test</button></div>

      <div>Entity name</div>
      <div>
        <input type="text" class="entityName">
      </div>
      <div>
        <span class="entity"></span>
      </div>
      <div>Fields</div>
      <div>
        <table>
          <thead>
            <tr>
              <th>Name</th>
            </tr>
          </thead>
          <tbody class="founds">
            <tr>
              <td>
                <a href="#">xx</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </form>
    <div>
      <pre>
<?= $er ?>
<?= print_r($model, TRUE); ?>
      </pre>
    </div>
  </div>
  <script>
    var xo;
    xo = new Trax.Xobject({
      entityName: "",
      founds: [{
        entityName: ""
      }]
    });

    xo._bind("entityName");
    xo._bind("founds");

    document.getElementById("okBtn").addEventListener("click", function (event) {
      console.log("okBtn clicked", JSON.stringify(xo, null, 2));
    });
    
    document.getElementById("apiTestBtn").addEventListener("click", function (event) {
      console.log("apiTestBtn clicked", null);

      axios.post('list.php', xo)
        .then(function (response) {
          console.log(response);
        })
        .catch(function (error) {
          console.log(error);
        });

    });


  </script>

</body>

</html>
