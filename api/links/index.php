<?php
require_once("../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  $msg["msg_en"] = "The following links were detected within the last 7 days";
  $msg["error"] = 0;
  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

  $statement = $pdo->prepare("SELECT `dev_pseudonym`, `gtw_id`, `time`, `snr`, `gtw_lat`, `gtw_lon`, `node_lat`, `node_lon`, `distance` FROM `link_list` WHERE `time` > UTC_TIMESTAMP() - INTERVAL 7 DAY");
  $statement->execute();

    $msg["links"] = array();
    $n = 0;
    while ($link = $statement->fetch()) {
      $msg["links"][$n] = array();
      $msg["links"][$n]["dev_pseudonym"] = (int)$link["dev_pseudonym"];
      $msg["links"][$n]["gtw_id"] = $link["gtw_id"];
      $msg["links"][$n]["time"] = $link["time"];
      $msg["links"][$n]["snr"] = floatval ($link["snr"]);
      if ($link["distance"] == null)
        $msg["links"][$n]["distance"] = null;
      else
        $msg["links"][$n]["distance"] = floatval ($link["distance"]);
      $msg["links"][$n]["coordinates"] = array();
      $msg["links"][$n]["coordinates"]["gateway"] = array();
      $msg["links"][$n]["coordinates"]["node"] = array();

      if ($link["gtw_lat"] == null || $link["gtw_lon"] == null) {
        $msg["links"][$n]["coordinates"]["gateway"]["lat"] = null;
        $msg["links"][$n]["coordinates"]["gateway"]["lon"] = null;
      } else {
        $msg["links"][$n]["coordinates"]["gateway"]["lat"] = floatval($link["gtw_lat"]);
        $msg["links"][$n]["coordinates"]["gateway"]["lon"] = floatval($link["gtw_lon"]);
      }

      if ($link["node_lat"] == null || $link["node_lon"] == null) {
        $msg["links"][$n]["coordinates"]["node"]["lat"] = null;
        $msg["links"][$n]["coordinates"]["node"]["lon"] = null;
      } else {
        $msg["links"][$n]["coordinates"]["node"]["lat"] = floatval($link["node_lat"]);
        $msg["links"][$n]["coordinates"]["node"]["lon"] = floatval($link["node_lon"]);
      }
      $n++;
    }

} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
