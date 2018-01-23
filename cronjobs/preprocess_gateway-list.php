<?php
require_once("../config.php");
$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
$pdo->query("TRUNCATE `preprocessed_gateway-list`");
$pdo->query("INSERT INTO `preprocessed_gateway-list` (`gtw_id`, `latitude`, `longitude`, `altitude`, `first_seen`, `last_seen`, `packets`, `channels`)
SELECT gtw_id, latitude, longitude, altitude, MIN(`time`) AS `first_seen`, MAX(`time`) AS `last_seen`, COUNT(`id`) AS `packets`, COUNT(DISTINCT channel) AS `channels` FROM (SELECT `gtw_id`, gateways.`latitude`, gateways.`longitude`, gateways.`altitude`, packets.`time`, gateways.`id`, gateways.`channel` FROM gateways
  LEFT JOIN (packets) ON (packets.id = gateways.packet_id) ORDER BY packets.`time` DESC) AS base GROUP BY `gtw_id`");
?>
