<?php
require_once("../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  if (isset($_GET["date_start"]) && isset($_GET["date_end"]) && isset($_GET["dev_pseudonym"])) {
    $msg["msg_en"] = "The following packets were received";
    $msg["error"] = 0;
    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

    $statement = $pdo->prepare("SELECT DATE_FORMAT( `time` , '%Y-%m-%dT%TZ' ) AS `time`, `packet_count`, `frequency`, `SF`, `BW`, `CR_k`, `CR_n`, `gateway_count` FROM packets WHERE `dev_pseudonym` = ? and `time` >= ? and `time` <= ?");
    $statement->execute(array($_GET["dev_pseudonym"], $_GET["date_start"], $_GET["date_end"]));

    $n = 0;
    $msg["packets"] = array(); //All packets
    while ($packet = $statement->fetch()) {
      $msg["packets"][$n] = array();
      $msg["packets"][$n]["time"] = $packet["time"];
      $msg["packets"][$n]["packet_count"] = (int)$packet["packet_count"];
      $msg["packets"][$n]["frequency"] = floatval($packet["frequency"]);
      $msg["packets"][$n]["SF"] = (int)$packet["SF"];
      $msg["packets"][$n]["gateway_count"] = (int)$packet["gateway_count"];
      $n++;
    }

    $statement = $pdo->prepare("SELECT count(id) AS packets, MIN(SF) AS SF_min, MAX(SF) AS SF_max FROM packets WHERE `dev_pseudonym` = ? and `time` >= ? and `time` <= ?");
    $statement->execute(array($_GET["dev_pseudonym"], $_GET["date_start"], $_GET["date_end"]));
    $packet_stats = $statement->fetch();
    $msg["packet_stats"] = array(); //Packet stats
    $msg["packet_stats"]["SF_min"] = $packet_stats["SF_min"];
    $msg["packet_stats"]["SF_max"] = $packet_stats["SF_max"];
    $msg["packet_stats"]["packets"] = $packet_stats["packets"];

    $statement = $pdo->prepare("SELECT gtw_id, MIN(snr) AS snr_min, MAX(SNR) as snr_max, MIN(rssi) AS rssi_min, MAX(rssi) AS rssi_max, count(gtw_id) AS packets, gateways.latitude AS lat, gateways.longitude AS lon, gateways.altitude AS alt FROM gateways LEFT JOIN (packets) ON (packets.id = gateways.packet_id) WHERE dev_pseudonym = ? and packets.time >= ? and packets.time <= ? GROUP BY `gtw_id`");
    $statement->execute(array($_GET["dev_pseudonym"], $_GET["date_start"], $_GET["date_end"]));

    $n = 0;
    $msg["gateways"] = array();
    while ($gateway = $statement->fetch()) {
      $msg["gateways"][$n] = array(); //List of all gateways which received the messages
      $msg["gateways"][$n]["gtw_id"] = $gateway["gtw_id"];
      $msg["gateways"][$n]["packets"] = $gateway["packets"];
      $msg["gateways"][$n]["snr_min"] = $gateway["snr_min"];
      $msg["gateways"][$n]["snr_max"] = $gateway["snr_max"];
      $msg["gateways"][$n]["rssi_min"] = $gateway["rssi_min"];
      $msg["gateways"][$n]["rssi_max"] = $gateway["rssi_max"];
      $msg["gateways"][$n]["lat"] = $gateway["lat"];
      $msg["gateways"][$n]["lon"] = $gateway["lon"];
      $msg["gateways"][$n]["alt"] = $gateway["alt"];
      $n++;
    }
  } else {
    $msg["error"] = 1;
    $msg["msg_en"] = "Reuiqred parameters are date_start, date_end and pseudonym";
  }

} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
