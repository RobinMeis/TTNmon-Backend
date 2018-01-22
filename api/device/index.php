<?php
require_once("../../config.php");

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //get registered devices
  if (isset($_GET["auth_token"])) { //All required parameters were specified
    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

    $statement = $pdo->prepare("SELECT authorization, administrator FROM authorizations WHERE authorization = ?");
    $statement->execute(array($_GET["auth_token"]));
    $administrator = $statement->fetch();
    if (empty($administrator)) { //Auth Error
      $msg["error"] = 1;
      $msg["msg_en"] = "Invalid authorization token";
    } else { //Auth OK
      $administrator = $administrator["administrator"];
      $msg["devices"] = array();

      if ($administrator == 1) //If administrator: Allowed to __see__ any device
        $statement = $pdo->prepare("SELECT deveui, app_id, dev_id, pseudonym, created, last_seen, latitude, longitude, altitude FROM devices");
      else
        $statement = $pdo->prepare("SELECT deveui, app_id, dev_id, pseudonym, created, last_seen, latitude, longitude, altitude FROM devices WHERE authorization = ?");
      $statement->execute(array($_GET["auth_token"]));

      $n = 0;
      while ($device = $statement->fetch()) {
        $msg["devices"][$n]["pseudonym"] = $device["pseudonym"];
        $msg["devices"][$n]["deveui"] = bin2hex($device["deveui"]);
        $msg["devices"][$n]["app_id"] = $device["app_id"];
        $msg["devices"][$n]["dev_id"] = $device["dev_id"];
        $msg["devices"][$n]["created"] = $device["created"];
        $msg["devices"][$n]["last_seen"] = $device["last_seen"];
        if ($device["latitude"] == null || $device["longitude"] == null) {
          $msg["devices"][$n]["latitude"] = null;
          $msg["devices"][$n]["longitude"] = null;
          $msg["devices"][$n]["altitude"] = null;
        } else {
          $msg["devices"][$n]["latitude"] = floatval($device["latitude"]);
          $msg["devices"][$n]["longitude"] = floatval($device["longitude"]);
          if ($device["altitude"] == null)
            $msg["devices"][$n]["altitude"] = null;
          else
            $msg["devices"][$n]["altitude"] = floatval($device["altitude"]);
        }
        $n++;
      }

      $msg["error"] = 0;
      $msg["msg_en"] = "The following devices are currently registered";
    }
  } else { //Parameters missing
    $msg["error"] = 2;
    $msg["msg_en"] = "Invalid request. At least one parameter is missing";
  }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
  if ($ALLOW_MANUAL_DEVICE_REGISTRATION == TRUE) { //Manual registration enabled
    if (isset($_GET["auth_token"]) && isset($_GET["deveui"])) { //All required parameters were specified
      if (strlen($_GET["deveui"]) != 16 || hex2bin($_GET["deveui"]) === False) { //Invalid deveui length
        $msg["error"] = 4;
        $msg["msg_en"] = "Invalid deveui";
      } else {
        $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

        $statement = $pdo->prepare("SELECT authorization FROM authorizations WHERE authorization = ? and administrator = FALSE"); //Check authorization, administrators are not allowed to add devices
        $statement->execute(array($_GET["auth_token"]));
        if (empty($statement->fetch())) { //Auth Error
          $msg["error"] = 1;
          $msg["msg_en"] = "Invalid authorization token. The device was not added";
        } else { //Auth OK
          $statement = $pdo->prepare("SELECT deveui FROM devices WHERE deveui = ?"); //Check if device is already registered
          $statement->execute(array(hex2bin($_GET["deveui"])));
          if (!empty($statement->fetch())) { //Device already registered
            $msg["error"] = 3;
            $msg["msg_en"] = "The device is already registered. If you forgot your auth token, please contact me";
          } else { //Register device
            $statement = $pdo->prepare("INSERT INTO devices (authorization, deveui, created) VALUES (?, ?, UTC_TIMESTAMP())"); //Register device
            $statement->execute(array($_GET["auth_token"], hex2bin($_GET["deveui"])));
            $msg["error"] = 0;
            $msg["msg_en"] = "The device was added";
          }
        }
      }
    } else { //Parameters missing
      $msg["error"] = 2;
      $msg["msg_en"] = "Invalid request. At least one parameter is missing";
    }
  } else { //Manual registration disabled
    $msg["error"] = 5;
    $msg["msg_en"] = "Manual device registration was disabled for security reasons. Your device will be adopted automatically when we receive the first message";
  }
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
  if (isset($_GET["auth_token"]) && isset($_GET["deveui"])) { //All required parameters were specified
    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

    $statement = $pdo->prepare("DELETE FROM devices WHERE authorization = ? and deveui = ?"); //Try to remove device with current authorization
    $statement->execute(array($_GET["auth_token"], hex2bin($_GET["deveui"])));

    if ($statement->rowCount() == 0) { //Device was not deleted
      $msg["error"] = 1;
      $msg["msg_en"] = "The device was not deleted because it does either not exist or does not belong to you";
    } else { //Device was deleted
      $msg["error"] = 0;
      $msg["msg_en"] = "The device was successfully deleted";
    }
  } else { //Parameters missing
    $msg["error"] = 2;
    $msg["msg_en"] = "Invalid request. At least one parameter is missing";
  }
} else {
  $msg["error"] = 1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
