<?php
require_once("../../config.php");

header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //get registered devices
  if (isset($_GET["query"])) { //All required parameters were specified
    $query = $_GET["query"];

    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
    if (strlen($query) == 16 && ctype_xdigit($query)) { //propably a DevEUI
      $statement = $pdo->prepare("SELECT pseudonym FROM devices WHERE deveui = ?"); //Check if device is already registered
      $statement->execute(array(hex2bin($query)));
      $pseudonym = $statement->fetch();
      if (empty($pseudonym)) {
        $msg["error"] = 1;
        $msg["msg_en"] = "Pseudonym by DevEUI not found";
      } else {
        $msg["error"] = 0;
        $msg["msg_en"] = "Your device was found";
        $msg["pseudonym"] = (int)$pseudonym["pseudonym"];
      }
    } else if (is_numeric($query)) { //propably a pseudonym
      $statement = $pdo->prepare("SELECT pseudonym FROM devices WHERE pseudonym = ?"); //Check if device is already registered
      $statement->execute(array($query));
      $pseudonym = $statement->fetch();
      if (empty($pseudonym)) {
        $msg["error"] = 1;
        $msg["msg_en"] = "Pseudonym not found";
      } else {
        $msg["error"] = 0;
        $msg["msg_en"] = "Your device was found";
        $msg["pseudonym"] = (int)$pseudonym["pseudonym"];
      }
    } else {
      $msg["error"] = 3;
      $msg["msg_en"] = "Invalid query. Input a DevEUI or a Pseudonym";
    }


  } else { //Parameters missing
    $msg["error"] = 2;
    $msg["msg_en"] = "Parameter query is required";
  }
} else {
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
