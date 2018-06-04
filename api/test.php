<?php
header("Content-Type: application/json; charset=UTF-8");
// $obj = json_decode($_GET["x"], false);

// $conn = new mysqli("myServer", "myUser", "myPassword", "Northwind");
// $result = $conn->query("SELECT name FROM ".$obj->table." LIMIT ".$obj->limit);
// $outp = array();
// $outp = $result->fetch_all(MYSQLI_ASSOC);

$content_type = explode(';', trim(strtolower($_SERVER['CONTENT_TYPE'])));
$media_type = $content_type[0];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $media_type == 'application/json') {
    // application/json で送信されてきた場合の処理
    $payload = json_decode(file_get_contents('php://input'), true);
} else {
  $payload = [
    "aa" => "11",
    "bb" => "22",
  ];
}


echo json_encode($payload);
?>
