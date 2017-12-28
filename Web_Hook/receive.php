<?php
require_once("config.php");
$headers = getallheaders();
if (isset($headers["Authorization"])) {
  $authorization = $headers["Authorization"];
  $check_auth = $pdo->prepare("SELECT id FROM authorizations WHERE authorization = :authorization LIMIT 1");
  $check_auth->execute(array($authorization));
  print_r($pdo->query($check_auth));

} else {
  print("Error: Authorization Header missing");
}

exit();
$pdo = new PDO('mysql:host=localhost;dbname=smrtnoob_ttnmon', $MYSQL_USER, $MYSQL_PASSWD);

$data = array();
$data['key'] = json_encode(getallheaders());

$statement = $pdo->prepare("INSERT INTO applications (`key`) VALUES (:key)");
$statement->execute($data);

$pdo = null;
?>
