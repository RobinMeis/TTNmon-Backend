<?php
require_once("../../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  $msg["msg_en"] = "The following gateways are known";
  $msg["error"] = 0;
  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

  if (isset($_GET["hide_offline"]) && $_GET["hide_offline"] == TRUE) //Shows only gateways which where seen within the last two weeks
    $statement = $pdo->prepare("SELECT `gtw_id`, `channels`, `packets`, `latitude`, `longitude`, `altitude`, `first_seen`, `last_seen`, `ttn_description`, `ttn_brand`, `ttn_model`, `ttn_username` FROM `gateway_list` WHERE `last_seen` > UTC_TIMESTAMP() - INTERVAL 14 DAY");
  else
    $statement = $pdo->prepare("SELECT `gtw_id`, `channels`, `packets`, `latitude`, `longitude`, `altitude`, `first_seen`, `last_seen`, `ttn_description`, `ttn_brand`, `ttn_model`, `ttn_username` FROM `gateway_list`");

  $statement->execute();

    $msg["gateways"] = array();
    $n = 0;
    while ($gateway = $statement->fetch()) {
      $msg["gateways"][$n] = array();
      $msg["gateways"][$n]["gtw_id"] = $gateway["gtw_id"];
      $msg["gateways"][$n]["channels"] = (int)$gateway["channels"];
      $msg["gateways"][$n]["packets"] = (int)$gateway["packets"];
      if ($gateway["latitude"] == null || $gateway["longitude"] == null) {
        $msg["gateways"][$n]["latitude"] = null;
        $msg["gateways"][$n]["longitude"] = null;
        $msg["gateways"][$n]["altitude"] = null;
      } else {
        $msg["gateways"][$n]["latitude"] = floatval($gateway["latitude"]);
        $msg["gateways"][$n]["longitude"] = floatval($gateway["longitude"]);
        if ($gateway["altitude"] == null)
          $msg["gateways"][$n]["altitude"] = null;
        else
          $msg["gateways"][$n]["altitude"] = floatval($gateway["altitude"]);
      }
      $msg["gateways"][$n]["first_seen"] = $gateway["first_seen"];
      $msg["gateways"][$n]["last_seen"] = $gateway["last_seen"];
      $msg["gateways"][$n]["description"] = $gateway["ttn_description"];
      $msg["gateways"][$n]["brand"] = $gateway["ttn_brand"];
      $msg["gateways"][$n]["model"] = $gateway["ttn_model"];
      $msg["gateways"][$n]["username"] = $gateway["ttn_username"];
      $n++;
    }

} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
