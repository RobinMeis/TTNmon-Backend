<?php
require_once("../../config.php");

function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i)
        $str .= $keyspace[random_int(0, $max)];
    return $str;
}

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "POST") { //Create new token
  $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
  $mysql_data = array();

  $msg["error"] = 1;
  $msg["msg_en"] = "The code could not be generated. If this error persists, please inform me";

  for ($n=0; $n<3; ++$n) { //Retry if auth token already exists
    $mysql_data["authorization"] = random_str(20);
    $statement = $pdo->prepare("INSERT INTO authorizations (`authorization`, `created`) VALUES (:authorization, UTC_TIMESTAMP())");
    $statement->execute($mysql_data);

    if ($pdo->lastInsertId() != 0) { //If token did not exist yet, leave loop
      $msg["error"] = 0;
      $msg["msg_en"] = "A new token was generated";
      $msg["auth_token"] = $mysql_data["authorization"];
      break;
    }
  }

  $msg["error"] = 0;
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
  if (isset($_GET["auth_token"])) {
    $pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
    $mysql_data = array();
    $mysql_data["authorization"] = $_GET["auth_token"];
    $statement = $pdo->prepare("SELECT id FROM authorizations WHERE authorization = :authorization"); //Fetch ID
    $statement->execute($mysql_data);
    $authorization_id = $statement->fetch();

    if (empty($authorization_id)) { //Auth token not found
      $msg["error"] = 1;
      $msg["msg_en"] = "The auth token could not be found";
    } else { //Delete authorization and its devices
      $statement = $pdo->prepare("DELETE FROM authorizations WHERE authorization = :authorization"); //Delete Auth Token
      $statement->execute($mysql_data);

      $mysql_data = array(); //Delete the tokens devices
      $mysql_data["authorization_id"] = $authorization_id["id"];
      $statement = $pdo->prepare("DELETE FROM devices WHERE authorization_id = :authorization_id");
      $statement->execute($mysql_data);

      $msg["error"] = 0;
      $msg["msg_en"] = "The auth token and its devices have been successfully deleted";
    }
  } else { //Missing parameters
    $msg["error"] = 2;
    $msg["msg_en"] = "Invalid request. At least one parameter is missing";
  }
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
