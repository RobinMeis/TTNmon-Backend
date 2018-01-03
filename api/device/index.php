<?php
require_once("../../config.php");
$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //get registered devices
  if (isset($_GET["auth_token"])) { //All required parameters were specified
    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

    $statement = $pdo->prepare("SELECT authorization FROM authorizations WHERE authorization = ?");
    $statement->execute(array($_GET["auth_token"]));
    if (empty($statement->fetch())) { //Auth Error
      $msg["error"] = 1;
      $msg["msg_en"] = "Invalid authorization code";
    } else { //Auth OK
      $msg["devices"] = array();
      $statement = $pdo->prepare("SELECT deveui FROM devices WHERE authorization = ?");
      $statement->execute(array($_GET["auth_token"]));
      while ($device = $statement->fetch())
        $msg["devices"][] = bin2hex($device["deveui"]);

      $msg["error"] = 0;
      $msg["msg_en"] = "The following devices are currently registered";
    }
  } else { //Parameters missing
    $msg["error"] = 2;
    $msg["msg_en"] = "Invalid request. At least one parameter is missing";
  }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
  if (isset($_GET["auth_token"]) && isset($_GET["deveui"])) { //All required parameters were specified

  } else { //Parameters missing
    $msg["error"] = 2;
    $msg["msg_en"] = "Invalid request. At least one parameter is missing";
  }
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
  if (isset($_GET["auth_token"])) { //All required parameters were specified

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
