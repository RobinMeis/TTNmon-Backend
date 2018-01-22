<?php
require_once("../config.php");
$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
$pdo->query("TRUNCATE `preprocessed_gateway-list`");
$pdo->query("INSERT INTO `preprocessed_gateway-list` (`gtw_id`, `latitude`, `longitude`, `altitude`, `first_seen`, `last_seen`, `packets`, `channels`)
  SELECT `gtw_id`, gateways.`latitude`, gateways.`longitude`, gateways.`altitude`, MIN(packets.`time`) AS `first_seen`, MAX(packets.`time`) AS `last_seen`, COUNT(gateways.`id`), COUNT(DISTINCT channel) AS `packets` FROM gateways
    LEFT JOIN (packets) ON (packets.id = gateways.packet_id) GROUP BY `gtw_id`");
?>
