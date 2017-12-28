<?php
require_once("config.php");
$pdo = new PDO('mysql:host=localhost;dbname=smrtnoob_ttnmon', $MYSQL_USER, $MYSQL_PASSWD);

$statement = $pdo->prepare("INSERT INTO applications (key) VALUES (?)");
$statement->execute(json_encode(array(getallheaders())));

$pdo = null;
?>
