<?php
require_once("../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //get registered devices
  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
  $statement = $pdo->prepare("SHOW TABLE STATUS WHERE Name = 'packets' or Name = 'gateways' or Name = 'devices' or Name = 'authorizations' or Name = 'gateway_list'"); //Get table stats
  $statement->execute();
  $msg["stats"] = array();
  while ($table = $statement->fetch()) {
    if ($table["Name"] == 'preprocessed_gateway-list') {
      $msg["stats"]["unique_gateways"] = array();
      $msg["stats"]["unique_gateways"]["count"] = $table["Rows"];
    } else {
      $msg["stats"][$table["Name"]] = array();
      $msg["stats"][$table["Name"]]["count"] = $table["Rows"];
    }
  }
  $msg["error"] = 0;
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
