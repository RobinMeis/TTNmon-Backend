<?php
require_once("../../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  $msg["msg_en"] = "The following device are available with coordinates";
  $msg["error"] = 0;
  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

  $statement = $pdo->prepare("SELECT `pseudonym`, `created`, `last_seen`, `latitude, longitude`, `altitude` FROM devices WHERE `latitude` is not null and `longitude` is not null");
  $statement->execute();

    $msg["devices"] = array();
    $n = 0;
    while ($device = $statement->fetch()) {
      $msg["devices"][$n] = array();
      $msg["devices"][$n]["pseudonym"] = (int)$device["pseudonym"];
      $msg["devices"][$n]["latitude"] = floatval($device["latitude"]);
      $msg["devices"][$n]["longitude"] = floatval($device["latitude"]);
      $msg["devices"][$n]["altitude"] = floatval($device["latitude"]);
      $msg["devices"][$n]["created"] = $device["created"];
      $msg["devices"][$n]["last_seen"] = $device["last_seen"];
      $n++;
    }

} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
