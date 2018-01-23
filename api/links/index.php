<?php
require_once("../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  $msg["msg_en"] = "The following links were detected within the last 7 days";
  $msg["error"] = 0;
  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

  $statement = $pdo->prepare("SELECT `dev_pseudonym`, `gtw_id`, `time`, `snr` FROM `preprocessed_links`");
  $statement->execute();

    $msg["links"] = array();
    $n = 0;
    while ($link = $statement->fetch()) {
      $msg["links"][$n] = array();
      $msg["links"][$n]["dev_pseudonym"] = (int)$link["dev_pseudonym"];
      $msg["links"][$n]["gtw_id"] = $link["gtw_id"];
      $msg["links"][$n]["time"] = $link["time"];
      $msg["links"][$n]["snr"] = floatval ($link["snr"]);
      $n++;
    }

} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
