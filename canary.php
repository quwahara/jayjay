
<?php
require 'vendor/autoload.php';

use Entities\Stores;
use Services\Services;

$S = Services::singleton();

$model = (function() {

  $S = Services::singleton();

  return [
    'entities' => $S->entities()
  ];
})();


?>
<html>

<head>
  <script src="./js/radio.js"></script>
</head>

<body>
  <div id="root">
    <div>
      <pre>
      <?= $S->ddl()->dropTable(new Stores(), TRUE); ?>
      <?= $S->ddl()->createTable(new Stores()); ?>
      </pre>
    </div>
    <form>
      <table>
        <tbody>
          <tr>
            <td>
              <input type="text">
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
  <script>
    window.onload = function() {
      var model = <?= json_encode($model) ?>;
      
      var radio = new Radio({
        root: document.getElementById("root"),
        //                actives: {
        //                    users: []
        //                },
        //                phase: {
        //                    activated: function() {
        //                        console.log("activated");
        //                        console.log(this);
        //                        var self = this;
        //
        //                        app.repo.users.byEnabled(true)
        //                            .then(function(response) {
        //                                console.log(response);
        //                                console.log(self);
        //
        //                                var users = response.data;
        //                                users.forEach(function(user) {
        //                                    user.detailHref = "detail.html?username=" + user.username;
        //                                });
        //                                self.users = users;;
        //                            })
        //                            .catch(function(error) {
        //                                console.log(error);
        //                            });
        //                    }
        //                },
        methods: {}
      });
    };

  </script>

</body>

</html>
