<?php
require_once("../../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  if (isset($_GET["date_start"]) && isset($_GET["date_end"]) && isset($_GET["dev_pseudonym"]) && isset($_GET["gtw_id"])) {
    if (isset($_GET["timezone_offset"]))
      $timezoneOffset = intval($_GET["timezone_offset"]);
    else
      $timezoneOffset = 0;

    $msg["msg_en"] = "The following packets were received";
    $msg["error"] = 0;
    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

    //Get packets for the specified device and gateway
    $statement = $pdo->prepare("SELECT DATE_FORMAT( packets.`time` , '%Y-%m-%dT%TZ' ) AS `time`, channel, rssi, snr, gateways.latitude, gateways.longitude, gateways.altitude FROM gateways LEFT JOIN (packets) ON (packets.id = gateways.packet_id) WHERE packets.time >= DATE_ADD(?, INTERVAL ? MINUTE) and packets.time <= DATE_ADD(?, INTERVAL ? MINUTE) and dev_pseudonym = ? and gtw_id = ?");
    $statement->execute(array($_GET["date_start"], $timezoneOffset, $_GET["date_end"], $timezoneOffset, $_GET["dev_pseudonym"], $_GET["gtw_id"]));

    $msg["packets"] = array();
    $n = 0;
    while ($packet = $statement->fetch()) {
      $msg["packets"][$n] = array();
      $msg["packets"][$n]["time"] = $packet["time"];
      $msg["packets"][$n]["channel"] = (int)$packet["channel"];
      $msg["packets"][$n]["rssi"] = (int)$packet["rssi"];
      $msg["packets"][$n]["snr"] = floatval($packet["snr"]);
      $msg["packets"][$n]["gateway_latitude"] = $packet["gateways.latitude"];
      $msg["packets"][$n]["gateway_longitude"] = $packet["gateways.longitude"];
      $msg["packets"][$n]["gateway_altitude"] = $packet["gateways.altitude"];
      $n++;
    }

    //Get stats for device gateway combination
    $msg["stats"] = array();
    $statement = $pdo->prepare("SELECT count(gateways.id) AS total_packets, MIN(channel) AS channel_min, MAX(channel) AS channel_max, MIN(rssi) AS rssi_min, MAX(rssi) AS rssi_max, MIN(snr) AS snr_min, MAX(snr) AS snr_max FROM gateways LEFT JOIN (packets) ON (packets.id = gateways.packet_id) WHERE packets.time >= ? and packets.time <= ? and dev_pseudonym = ? and gtw_id = ?");
    $statement->execute(array($_GET["date_start"], $_GET["date_end"], $_GET["dev_pseudonym"], $_GET["gtw_id"]));
    $stats = $statement->fetch();
    $msg["stats"]["packets"] = $stats["total_packets"];
    $msg["stats"]["channel_min"] = $stats["channel_min"];
    $msg["stats"]["channel_max"] = $stats["channel_max"];
    $msg["stats"]["rssi_min"] = $stats["rssi_min"];
    $msg["stats"]["rssi_max"] = $stats["rssi_max"];
    $msg["stats"]["snr_min"] = $stats["snr_min"];
    $msg["stats"]["snr_max"] = $stats["snr_max"];
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
