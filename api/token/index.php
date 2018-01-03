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
  $msg["error"] = 0;
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
