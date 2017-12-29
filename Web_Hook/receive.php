<?php
require_once("config.php");
$headers = getallheaders();
if (isset($headers["Authorization"])) {
  $authorization = $headers["Authorization"];

  $pdo = new PDO('mysql:host=localhost;dbname=smrtnoob_ttnmon', $MYSQL_USER, $MYSQL_PASSWD);

  $check_auth = $pdo->prepare("SELECT id FROM authorizations WHERE authorization = ? LIMIT 1"); //Check authorization
  $check_auth->execute(array($authorization));
  $authorization_id = $check_auth->fetch();

  if (!isset($authorization_id["id"])) { //Auth error
    print("Authorization not found");
    $pdo = null;
  } else { //Auth success
    $authorization_id = $authorization_id["id"];
    $data = file_get_contents('php://input');

    file_put_contents("log.json", $data);

    $fields_root = array("hardware_serial", "is_retry", "metadata", "gateways"); //Check for required fieds in POST
    $fields_metadata = array("time", "frequency", "modulation", "data_rate", "bit_rate", "coding_rate", "latitude", "longitude", "altitude");
    $fields_gateway = array("gtw_id", "time", "channel", "rssi", "snr", "rf_chain", "latitude", "longitude", "altitude");
    $pdo = null;

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
