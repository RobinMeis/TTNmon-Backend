<?php
require_once("config.php");
require_once("check_array.inc.php");

$headers = getallheaders();
if (isset($headers["Authorization"])) {
  $authorization = $headers["Authorization"];

  $pdo = new PDO('mysql:host=localhost;dbname=smrtnoob_ttnmon', $MYSQL_USER, $MYSQL_PASSWD);

  $check_auth = $pdo->prepare("SELECT id FROM authorizations WHERE authorization = ? LIMIT 1"); //Check authorization
  $check_auth->execute(array($authorization));
  $authorization_id = $check_auth->fetch();

  if (!isset($authorization_id["id"])) { //Auth error
    print("Authorization not found");
  } else { //Auth success
    $authorization_id = $authorization_id["id"];
    $data = json_decode(file_get_contents('php://input'), true); //Get request body

    if (isset($data["metadata"]["modulation"])) {
      if ($data["metadata"]["modulation"] == "LORA") {
        if (check_array($data, array("hardware_serial", "metadata", "dev_id")) && check_array($data, array("time", "frequency", "data_rate", "coding_rate", "gateways"))) { //Check packet data for required fields
          foreach ($data["metadata"]["gateways"] as $key=>$gateway) { //Check if all required fields for gateway were transmitted
            if (!isset($gateway["latitude"]) or !isset($gateway["longitude"])) { //gateway lat, lon not available
              $data["metadata"]["gateways"][$key]["latitude"] = null;
              $data["metadata"]["gateways"][$key]["longitude"] = null;
            }

            if (!isset($gateway["altitude"])) //gateway altitude not available
              $data["metadata"]["gateways"][$key]["altitude"] = null;

            if (!check_array($gateway, array("gtw_id", "time", "channel", "rssi", "snr", "rf_chain"))) {
              print("Error: Gateway data incomplete. Required fields are gtw_id, time, channel, rssi, snr, rf_chain");
              exit();
            }
          }
          //Finally we can assume, that we have all required data and continue with data storage
          file_put_contents("log.json", $data); //Log last request

          if (!isset($data["metadata"]["latitude"]) or !isset($data["metadata"]["longitude"])) { //node lat, lon not available
            $data["metadata"]["latitude"] = null;
            $data["metadata"]["longitude"] = null;
          }

          if (!isset($data["metadata"]["altitude"])) //node altitude not available
            $data["metadata"]["altitude"] = null;

            

        } else {
          print("Error: Packet data incomplete. Required fields are hardware_serial, metadata, dev_id, time, frequency, data_rate, bit_rate, coding_rate, gateways");
        }
      } else {
        print("Error: Unknown modulation. If FSK -> Currently not supported"); //TODO: Implement FSK
      }
    } else {
      print("Error: Modulation not found -> Maybe a test packet?");
    }
  }

} else {
  print("Error: Authorization Header missing");
}

exit();


$pdo = new PDO('mysql:host=localhost;dbname=smrtnoob_ttnmon', $MYSQL_USER, $MYSQL_PASSWD);

$data = array();
$data['key'] = json_encode(getallheaders());

$statement = $pdo->prepare("INSERT INTO applications (`key`) VALUES (:key)");
$statement->execute($data);

?>
