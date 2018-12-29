<?php
require_once("../../config.php");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
$msg = array();
$msg["msg_en"] = "The following networks are currently known";
$msg["error"] = 0;

$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

$msg["networks"] = array();
$statement = $pdo->prepare("SELECT `prefix`, `location`, `name`, `netid` FROM `networks`");
$statement->execute();
while ($row = $statement->fetch()) {
  $msg["networks"][] = array(
    "prefix" => $row["prefix"],
    "location" => $row["location"],
    "name" => $row["name"],
    "netid" => $row["netid"],
  );
}

echo json_encode($msg);
?>
