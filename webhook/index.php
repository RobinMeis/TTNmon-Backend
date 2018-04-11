<?php
require_once("../config.php");
require_once("check_array.inc.php");
require_once("getallheaders.inc.php");

$headers = getallheaders();
if (isset($headers["Authorization"])) {
  $authorization = $headers["Authorization"];

  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

  $data = json_decode(file_get_contents('php://input'), true); //Get request body

  if (isset($data["hardware_serial"])) {
    $check_auth = $pdo->prepare("SELECT pseudonym FROM devices WHERE authorization = ? and deveui = ?"); //Check authorization
    $check_auth->execute(array($authorization, hex2bin($data["hardware_serial"])));
    $pseudonym = $check_auth->fetch()["pseudonym"];
    if (empty($pseudonym)) { //Device ID does not belong to Authorization token
      if ($AUTO_ADOPTION == FALSE) { //Don't try to adopt device, if auto adoption is disabled
        print("Error: The authorization token is invalid or device ID (Hardware Serial) does not belong to your authorization token");
        exit();
      } else { //Try to adopt device
        $statement = $pdo->prepare("SELECT deveui FROM devices WHERE deveui = ?"); //Check if device is already registered
        $statement->execute(array(hex2bin($data["hardware_serial"])));
        if (!empty($statement->fetch())) { //Device already registered
          print("Error: The device belongs to another authorization");
          exit();
        } else { //Register device
          $check_auth = $pdo->prepare("SELECT created FROM authorizations WHERE authorization = ? and administrator = FALSE"); //Check authorization, disallow admins to register new devices
          $check_auth->execute(array($authorization));
          if (empty($check_auth->fetch())) { //Authorization invalid
            print("Error: The authorization token is invalid");
            exit();
          } else { //Authorization valid
            $comment = "auto adopted";

            if ($ADOPTION_PROOF == TRUE) {
              if (isset($data["downlink_url"]) && isset($data["app_id"]) && isset($data["dev_id"])) {
                $url = parse_url ($data["downlink_url"]);
                parse_str($url["query"], $url);
                if (isset($url["key"])) {
                  $ch = curl_init("http://eu.thethings.network:8084/applications/".$data["app_id"]."/devices/".$data["dev_id"]);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Key ".$url["key"]));
                  $device = curl_exec($ch);
                  curl_close($ch);

                  $device = json_decode($device, true);
                  if (isset($device["lorawan_device"]["dev_eui"])) {
                    if ($device["lorawan_device"]["dev_eui"] == $data["hardware_serial"]) {
                      print("Notice: Adoption proof successful");
                      $comment .= ", proofed";
                    } else {
                      print("Error: Adoption proof failed. Try to hack something else!");
                      exit();
                    }
                  } else {
                    print("Error: Invalid response from TTN. Device was not adopted!");
                    exit();
                  }
                } else {
                  print("Error: Auto adoption failed because of missing key in downlink_url");
                  exit();
                }
              } else {
                  print("Error: Auto adoption failed because of missing data");
                  exit();
              }
            }

            if (isset($data["dev_id"])) $comment = $comment . ": " . $data["dev_id"];

            $statement = $pdo->prepare("INSERT INTO devices (authorization, deveui, app_id, dev_id, comment, created) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP())"); //Register device
            $statement->execute(array($authorization, hex2bin($data["hardware_serial"]), $data["app_id"], $data["dev_id"], $comment));
            $pseudonym = $pdo->lastInsertId();
            print("Notice: The device was auto adopted");
          }
        }
      }
    }
  } else {
    print("Error: hardware_serial missing");
    exit();
  }

  if (isset($data["metadata"]["modulation"])) {
    if ($data["metadata"]["modulation"] == "LORA") {
      if (check_array($data, array("hardware_serial", "metadata", "dev_id", "counter")) && check_array($data["metadata"], array("time", "frequency", "data_rate", "coding_rate", "gateways"))) { //Check packet data for required fields
        if ($LOG_WEBHOOK == TRUE)
          file_put_contents("logging/". $pseudonym."_".gmdate("Y-m-d_H-i-s") .".json", json_encode($data)); //Log last request if enabled

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

        $sfbw = explode("BW", $data["metadata"]["data_rate"]);
        $cr = explode("/", $data["metadata"]["coding_rate"]);
        $data["metadata"]["SF"] = str_replace("SF", "", $sfbw[0]);
        $data["metadata"]["BW"] = $sfbw[1];
        $data["metadata"]["CR_k"] = $cr[0];
        $data["metadata"]["CR_n"] = $cr[1];
        //Finally we can assume, that we have all required data and prepare data for saving

        if (!isset($data["metadata"]["latitude"]) or !isset($data["metadata"]["longitude"])) { //node lat, lon not available
          $data["metadata"]["latitude"] = null;
          $data["metadata"]["longitude"] = null;
        }

        if (!isset($data["metadata"]["altitude"])) //node altitude not available
          $data["metadata"]["altitude"] = null;

        //After preparing data, we can finally store it
        $mysql_data = array();
        $mysql_data['dev_pseudonym'] = $pseudonym;
        $mysql_data['packet_count'] = $data["counter"];
        $mysql_data['pkt_time'] = $data["metadata"]["time"];
        $mysql_data['frequency'] = $data["metadata"]["frequency"];
        $mysql_data['modulation'] = "LORA";
        $mysql_data['SF'] = $data["metadata"]["SF"];
        $mysql_data['BW'] = $data["metadata"]["BW"];
        $mysql_data['CR_k'] = $data["metadata"]["CR_k"];
        $mysql_data['CR_n'] = $data["metadata"]["CR_n"];
        $mysql_data['gateway_count'] = count($data["metadata"]["gateways"]);
        $mysql_data['latitude'] = $data["metadata"]["latitude"];
        $mysql_data['longitude'] = $data["metadata"]["longitude"];
        $mysql_data['altitude'] = $data["metadata"]["altitude"];

        $statement = $pdo->prepare("INSERT INTO packets (`dev_pseudonym`, `packet_count`, `time`, `frequency`, `modulation`, `SF`, `BW`, `CR_k`, `CR_n`, `gateway_count`, `latitude`, `longitude`, `altitude`) VALUES (:dev_pseudonym, :packet_count, :pkt_time, :frequency, :modulation, :SF, :BW, :CR_k, :CR_n, :gateway_count, :latitude, :longitude, :altitude)");
        $statement->execute($mysql_data);
        $packet_id = $pdo->lastInsertId();
        foreach ($data["metadata"]["gateways"] as $gateway) {
          $mysql_data = array();
          $mysql_data['packet_id'] = $packet_id;
          $mysql_data['gtw_id'] = $gateway["gtw_id"];
          $mysql_data['channel'] = $gateway["channel"];
          $mysql_data['rssi'] = $gateway["rssi"];
          $mysql_data['snr'] = $gateway["snr"];
          $mysql_data['rf_chain'] = $gateway["rf_chain"];

          if (check_array($gateway, array("latitude", "longitude"))) {
            $mysql_data["latitude"] = $gateway["latitude"];
            $mysql_data["longitude"] = $gateway["longitude"];
          } else {
            $mysql_data["latitude"] = null;
            $mysql_data["longitude"] = null;
          }

          if (isset($gateway["altitude"]) && $gateway["altitude"] != "null") $mysql_data["altitude"] = $gateway["altitude"];
          else $mysql_data["altitude"] = null;

          if (isset($gateway["time"]) && $gateway["time"] != "null") $mysql_data["time"] = $gateway["time"];
          else $mysql_data["time"] = null;

          $statement = $pdo->prepare("INSERT INTO gateways (`packet_id`, `gtw_id`, `channel`, `rssi`, `snr`, `rf_chain`, `latitude`, `longitude`, `altitude`, `time`) VALUES (:packet_id, :gtw_id, :channel, :rssi, :snr, :rf_chain, :latitude, :longitude, :altitude, :time)");
          $statement->execute($mysql_data);

          $mysql_data = array(); //Try to update gateway
          $mysql_data['gtw_id'] = $gateway["gtw_id"];
          $mysql_data['latitude'] = $data["metadata"]["latitude"];
          $mysql_data['longitude'] = $data["metadata"]["longitude"];
          $mysql_data['altitude'] = $data["metadata"]["altitude"];
          $statement = $pdo->prepare("UPDATE gateway_list SET `last_seen` = UTC_TIMESTAMP(), `packets` = `packets` + 1, `latitude` = :latitude, `longitude` = :longitude, `altitude` = :altitude WHERE `gtw_id` = :gtw_id");
          $statement->execute($mysql_data);

          if ($statement->rowCount() == 0) { //gateway is not yet in gateway list, add it
            $mysql_data = array();
            $mysql_data['gtw_id'] = $gateway["gtw_id"];
            $mysql_data['latitude'] = $data["metadata"]["latitude"];
            $mysql_data['longitude'] = $data["metadata"]["longitude"];
            $mysql_data['altitude'] = $data["metadata"]["altitude"];
            $statement = $pdo->prepare("INSERT INTO gateway_list (`gtw_id`, `first_seen`, `last_seen`, `latitude`, `longitude`, `altitude`) VALUES (:gtw_id, UTC_TIMESTAMP(), UTC_TIMESTAMP(), :latitude, :longitude, :altitude)");
            $statement->execute($mysql_data);
          }
        }

        $mysql_data = array(); //Update device
        $mysql_data['latitude'] = $data["metadata"]["latitude"];
        $mysql_data['longitude'] = $data["metadata"]["longitude"];
        $mysql_data['altitude'] = $data["metadata"]["altitude"];
        $mysql_data['dev_pseudonym'] = $pseudonym;
        $statement = $pdo->prepare("UPDATE devices SET `latitude` = :latitude, `longitude` = :longitude, `altitude` = :altitude, `last_seen` = UTC_TIMESTAMP() WHERE `pseudonym` = :dev_pseudonym");
        $statement->execute($mysql_data);
        print("OK");
      } else {
        print("Error: Packet data incomplete. Required fields are hardware_serial, counter, metadata, dev_id, time, frequency, data_rate, bit_rate, coding_rate, gateways");
      }
    } else {
      print("Error: Unknown modulation. If FSK -> Currently not supported"); //TODO: Implement FSK
    }
  } else {
    print("Error: Modulation not found -> Maybe a test packet?");
  }
} else {
  print("Error: Authorization Header missing");
}
?>
