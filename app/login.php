<?php
require __DIR__ . '/../vendor/autoload.php';

use Services\Services;
use Services\SH;
use Services\URIService;

if (SH::isJsonPost()) {
    try {
        $payload = SH::readJson();
        $usersDAO = SH::ss()->dao('users');
        $user = $usersDAO->findOneBy($usersDAO->attachTypes(['name' => $payload['model']['user']['name']]));
        $success = false;
        if ($user) {
            $success = password_verify($payload['model']['user']['password'], $user['password']);
        }

        if ($success) {
            $sst = '';
            $ssstart = 'none';
            switch (session_status()) {
                case PHP_SESSION_NONE:
                    $sst = 'none';
                    $ssstart = session_start();
                    break;
                case PHP_SESSION_ACTIVE:
                    $sst = 'avtive';
                    session_reset();
                    break;
                default:
                    throw new Exception('Session was disabled.');
                    break;
            }
            $_SESSION['x'] =  'x';
            $_SESSION['name'] = $user['name'];
        }

        $payload['notice']= [
            'status' => 'success',
            'message' => 'The update has succeeded.',
            'user' => $user,
            'success' => $success,
            'success' => false,
            'sst' => $sst,
            'ssstat' => $ssstart,
        ];
        http_response_code(200);
    } catch (Exception $e) {
        http_response_code(500);
        $payload = [
            'notice' => [
                'status' => 'error',
                'message' => 'The update failed.',
                'exception' => print_r($e, true),
            ],
        ];
    }
    
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($payload);
    exit();
}

try {
    $model = (function () {
        $S = Services::singleton();
        $da = $S->da();
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $name = $_POST['name'];
            $password = $_POST['password'];

            $usersT = $da->getTableByTableName('users');
            $found = $da->findOne2('select * from users where name = :name', $da->attachTableNameAndTypes($usersT, ['name' => $name]));

            // $userOpe = $S->db()->loadOperation('Entities\Users');
            // $user = $userOpe->newInstance();
            // $userOpe->setPropertiesFrom($user, $_POST);

            // $found = $userOpe->findOneByPrimaryKey($_POST['name']);

            if ($found && $found['password'] === $password) {
                URIService::redirectByFilenameThenExit('home.php');
            } else {
                return [
                    'notice' => [
                        'status' => 'failure',
                    ],
                ];
            }

        } else {
            return [];
        }
    })();
} catch (Exception $e) {
    $model = [
        'notice' => [
            'status' => 'error',
            'message' => 'The update failed.',
            'exception' => print_r($e, true),
        ],
    ];
}

?>
<html>

<head>
  <link rel="stylesheet" type="text/css" href="../js/lib/node_modules/normalize.css/normalize.css">
  <link rel="stylesheet" type="text/css" href="../css/global.css">
  <script src="../js/lib/node_modules/axios/dist/axios.js"></script>
  <script src="../js/trax/trax.js"></script>
  <script src="../js/lib/global.js"></script>
  <?php
    echo '<style>'
        . Services::singleton()->css()->style
        . '</style>';
    ?>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
</head>

<body>

  <div>
    <div class="belt">
      <h1>Login</h1>
    </div>

    <div class="belt bg-mono-09">
      <a href="menus.php">menus</a>
    </div>

    <div class="belt hide status">
      <div class="message"></div>
      <div class="text-right"><button id="statusColseBtn" type="button" class="link">&times; Close</button></div>
    </div>

    <div class="belt bl-mono-06">
      <button type="button">OK</button>
    </div>

    <div class="contents">
      <form name="theForm" method="post">
        <div class="row">
          <label for="name">Username</label>
          <input type="text" name="name" id="name" class="name">
        </div>
        <div class="row">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" class="password">
        </div>
        <div class="row">
          <div class="label"></div>
          <button type="button" id="loginBtn">Login</button>
        </div>
      </form>
    </div>

  </div>
  <script>
    window.onload = function() {
        var model = <?= json_encode($model, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>;

        var vs = Trax.validations;
        var tx = new Trax.Xobject({
            "model": {
                "user": {
                    "name": "",
                    "password": ""
                },
            },
            "notice": {
                "status": "",
                "message": "",
            },
        });

        tx.model.user._bind("name", {
            "validations": [vs.lengthMinMax({min: 1, max: 6})],
        });
        tx.model.user._bind("password", {
            "validations": [vs.lengthMinMax({min: 1, max: 100})],
        });

        Trax.on("click", "#loginBtn", function (event) {
            console.log(">>> loginBtn on click", tx.model.user);
            axios.post('login.php', tx)
            .then(function (response) {
                console.log(response.data);
                // console.log(response);
                // xo.model = response.data.model;
                if (response.data.notice.success) {
                    window.location.href = window.location.href.replace("/login.php", "/home.php");
                }
            })
            .catch(function (error) {
                console.log(error);
            });

        });
    };
  </script>
</body>

</html>
