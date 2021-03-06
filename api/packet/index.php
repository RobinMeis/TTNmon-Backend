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

    $statement = $pdo->prepare("SELECT DATE_FORMAT( `time` , '%Y-%m-%dT%TZ' ) AS `time`, `packet_count`, `frequency`, `SF`, `BW`, `CR_k`, `CR_n`, `gateway_count`, `latitude`, `longitude`, `altitude` FROM packets WHERE `dev_pseudonym` = ? and `time` >= ? and `time` <= ?");
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

      if ($packet["latitude"] == null || $packet["longitude"] == null) { //No coordinates
        $msg["packets"][$n]["latitude"] = null;
        $msg["packets"][$n]["longitude"] = null;
        $msg["packets"][$n]["altitude"] = null;
      } else { //latitude & longitude available
        $msg["packets"][$n]["latitude"] = floatval($packet["latitude"]);
        $msg["packets"][$n]["longitude"] = floatval($packet["longitude"]);
        if ($packet["altitude"] == null) //No altitude
          $msg["packets"][$n]["altitude"] = null;
        else //altitude
          $msg["packets"][$n]["altitude"] = floatval($packet["altitude"]);
      }
      $n++;
    }

    $statement = $pdo->prepare("SELECT count(id) AS packets, MIN(SF) AS SF_min, MAX(SF) AS SF_max, MIN(gateway_count) AS gateway_count_min, MAX(gateway_count) AS gateway_count_max FROM packets WHERE `dev_pseudonym` = ? and `time` >= ? and `time` <= ?");
    $statement->execute(array($_GET["dev_pseudonym"], $_GET["date_start"], $_GET["date_end"]));
    $packet_stats = $statement->fetch();
    $msg["packet_stats"] = array(); //Packet stats
    $msg["packet_stats"]["SF_min"] = $packet_stats["SF_min"];
    $msg["packet_stats"]["SF_max"] = $packet_stats["SF_max"];
    $msg["packet_stats"]["packets"] = $packet_stats["packets"];
    $msg["packet_stats"]["gateway_count_min"] = $packet_stats["gateway_count_min"];
    $msg["packet_stats"]["gateway_count_max"] = $packet_stats["gateway_count_max"];

    $statement = $pdo->prepare("SELECT gtw_id, MIN(snr) AS snr_min, MAX(SNR) as snr_max, MIN(rssi) AS rssi_min, MAX(rssi) AS rssi_max, count(gtw_id) AS packets, gateways.latitude AS latitude, gateways.longitude AS longitude, gateways.altitude AS altitude, gateways.distance AS distance FROM gateways LEFT JOIN (packets) ON (packets.id = gateways.packet_id) WHERE dev_pseudonym = ? and packets.time >= ? and packets.time <= ? GROUP BY `gtw_id`");
    $statement->execute(array($_GET["dev_pseudonym"], $_GET["date_start"], $_GET["date_end"]));

    $n = 0;
    $msg["gateways"] = array();
    while ($gateway = $statement->fetch()) {
      $msg["gateways"][$n] = array(); //List of all gateways which received the messages
      $msg["gateways"][$n]["gtw_id"] = $gateway["gtw_id"];
      $msg["gateways"][$n]["packets"] = (int)$gateway["packets"];
      $msg["gateways"][$n]["snr_min"] = floatval($gateway["snr_min"]);
      $msg["gateways"][$n]["snr_max"] = floatval($gateway["snr_max"]);
      $msg["gateways"][$n]["rssi_min"] = (int)$gateway["rssi_min"];
      $msg["gateways"][$n]["rssi_max"] = (int)$gateway["rssi_max"];

      if ($gateway["latitude"] == null || $gateway["longitude"] == null) { //No coordinates
        $msg["gateways"][$n]["lat"] = null;
        $msg["gateways"][$n]["lon"] = null;
        $msg["gateways"][$n]["alt"] = null;
      } else { //latitude & longitude available
        $msg["gateways"][$n]["lat"] = floatval($gateway["latitude"]);
        $msg["gateways"][$n]["lon"] = floatval($gateway["longitude"]);

        if ($gateway["altitude"] == null) //No altitude
          $msg["gateways"][$n]["alt"] = null;
        else //altitude
          $msg["gateways"][$n]["alt"] = floatval($gateway["altitude"]);

        if ($gateway["distance"] == null) //No distance
          $msg["gateways"][$n]["distance"] = null;
        else
          $msg["gateways"][$n]["distance"] = floatval($gateway["distance"]);
      }

      $gtw_name = $pdo->prepare("SELECT `ttn_description` FROM `gateway_list` WHERE gtw_id LIKE ? LIMIT 1");
      $gtw_name->execute(array($msg["gateways"][$n]["gtw_id"]));
      $gtw_name = $gtw_name->fetch();
      $msg["gateways"][$n]["description"] = $gtw_name["ttn_description"];
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
