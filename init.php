<?php
(function () {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  
//  $inc_path = get_include_path();
  $ps = PATH_SEPARATOR;
  $ds = DIRECTORY_SEPARATOR;
  $cd = dirname(__FILE__);
//  $share_path = $cd . $ds . 'share';
//  $entities_path = $cd . $ds . 'entites';
  $new_inc_path = implode([
    get_include_path(),
    $cd . $ds . 'share',
    $cd . $ds . 'entites',
  ],
                          PATH_SEPARATOR);

  if (set_include_path($new_inc_path) === FALSE)
    throw new Exception('set_include_path error');
  
  require_once 'DB.php';
  require_once 'Stores.php';
  
})();
?>