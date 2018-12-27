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
    if ($table["Name"] == 'gateway_list') {
      $msg["stats"]["unique_gateways"] = array();
      $msg["stats"]["unique_gateways"]["count"] = $table["Rows"];
    } else {
      $msg["stats"][$table["Name"]] = array();
      $msg["stats"][$table["Name"]]["count"] = $table["Rows"];
    }
  }

  $statement = $pdo->prepare("SELECT count(id) AS packets FROM `packets` WHERE time >= ? and time <= ?"); //Get table stats
  $statement->execute(array(strftime("%Y-%m-%d 00:00:00", time() - 86400), strftime("%Y-%m-%d 23:59:59", time() - 86400)));
  $msg["stats"]["packets"]["per_day"] = $statement->fetch()["packets"];
  $msg["error"] = 0;
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
