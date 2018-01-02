<?php
require_once __DIR__ . "/../vendor/autoload.php";

namespace Controllers;




$model = (function() {
  
  return [
    
  ];
})();


?>
<html>

<head>
  <script src="./js/radio.js"></script>
</head>

<body>
  <div id="root">
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
