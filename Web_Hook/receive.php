<?php
require_once("config.php");
$pdo = new PDO('mysql:host=localhost;dbname=smrtnoob_ttnmon', $MYSQL_USER, $MYSQL_PASSWD);

$data = array();
$data['key'] = json_encode(getallheaders());

$statement = $pdo->prepare("INSERT INTO applications (`key`) VALUES (:key)");
$statement->execute($data);

$pdo = null;
?>
