<?php
require_once("../../config.php");

function findNetID($pdo, $devaddr) {
  //return Null;
  for ($length = strlen($devaddr); $length >= 2; $length-=2) {
    $find = substr($devaddr, 0, $length);
    $statement = $pdo->prepare("SELECT `netid` FROM `networks` WHERE `prefix` LIKE :find");
    $statement->bindValue(':find', '0x' . $find . '%');
    $statement->execute();
    $netid = $statement->fetch();
    if (!empty($netid)) return $netid["netid"];
  }
  return Null;
}

$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

$data = file_get_contents('php://input'); //Read and parse input

$requirements = array("SF", "BW", "snr", "cr_k", "modulation", "cr_n", "type", "payload_size", "frequency", "channel", "time", "rssi", "gtw_addr", "gtw_id", "fcount", "adr", "ack", "fport", "airtime", "time");

foreach ($json["pkts"] as $packet) {
  print("pkt");
  foreach ($requirements as $required) { //Check if all required fields exist in packet
    if (!isset($packet[$required])) {
      print ($required . " missing!");
      $string = json_encode($packet);
      file_put_contents("invalid_packets.log", $string, FILE_APPEND);
      break;
    }
  }

  print($packet["type"]);
  if ($packet["type"] == "JOIN") { //Handle JOIN Requests
    print("JOIN");
    if (isset($packet["deveui"])) {
      $data = array (
        "type" => $packet["type"],
        "frequency" => $packet["frequency"],
        "modulation" => $packet["modulation"],
        "SF" => $packet["SF"],
        "BW" => $packet["BW"],
        "CR_k" => $packet["cr_k"],
        "CR_n" => $packet["cr_n"],
        "airtime" => $packet["airtime"],
        "payload_size" => $packet["payload_size"],
        "rssi" => $packet["rssi"],
        "snr" => $packet["snr"],
        "ack" => 0,
        "adr" => 1,
        "fport" => $packet["fport"],
        "gtw_id" => $packet["gtw_id"],
        "devEUI" => hex2bin($packet["deveui"]),
        "time" => $packet["time"],
      );
      $statement = $pdo->prepare("INSERT INTO `gateway_packets` (`type`, `frequency`, `modulation`, `SF`, `BW`, `CR_k`, `CR_n`, `airtime`, `count`, `payload_size`, `RSSI`, `SNR`, `ACK`, `ADR`, `fport`, `gtw_id`, `devEUI`, `time`) VALUES (:type, :frequency, :modulation, :SF, :BW, :CR_k, :CR_n, :airtime, 0, :payload_size, :rssi, :snr, :ack, :adr, :fport, :gtw_id, :devEUI, :time)");
      $statement->execute($data);
    }
  } else if ($packet["type"] == "UPLINK") { //Handle Uplink
    print("uplink");
    if (isset($packet["dev_addr"])) {
      $data = array (
        "type" => $packet["type"],
        "frequency" => $packet["frequency"],
        "modulation" => $packet["modulation"],
        "SF" => $packet["SF"],
        "BW" => $packet["BW"],
        "CR_k" => $packet["cr_k"],
        "CR_n" => $packet["cr_n"],
        "airtime" => $packet["airtime"],
        "count" => $packet["fcount"],
        "payload_size" => $packet["payload_size"],
        "rssi" => $packet["rssi"],
        "snr" => $packet["snr"],
        "ack" => 0,
        "adr" => 1,
        "fport" => $packet["fport"],
        "gtw_id" => $packet["gtw_id"],
        "dev_addr" => hex2bin($packet["dev_addr"]),
        "netid" => findNetID($pdo, $packet["dev_addr"]),
        "time" => $packet["time"],
      );
      $statement = $pdo->prepare("INSERT INTO `gateway_packets` (`type`, `frequency`, `modulation`, `SF`, `BW`, `CR_k`, `CR_n`, `airtime`, `count`, `payload_size`, `RSSI`, `SNR`, `ACK`, `ADR`, `fport`, `gtw_id`, `devaddr`, `netid`, `time`) VALUES (:type, :frequency, :modulation, :SF, :BW, :CR_k, :CR_n, :airtime, :count, :payload_size, :rssi, :snr, :ack, :adr, :fport, :gtw_id, :dev_addr, :netid, :time)");
      $statement->execute($data);
    }
  } else { //Unsupported Type
    $string = json_encode($packet);
    file_put_contents("unsupported_packets.log", $string, FILE_APPEND);
  }
}
?>
