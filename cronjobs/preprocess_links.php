<?php
require_once("../config.php");
$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);
$pdo->query("TRUNCATE `preprocessed_links`");
$pdo->query("INSERT INTO preprocessed_links (gtw_id, dev_pseudonym, time, snr)
SELECT * FROM (SELECT gtw_id, dev_pseudonym, packets.time, snr FROM gateways LEFT JOIN (packets)
                     ON (packets.id = gateways.packet_id)
    WHERE packets.time >= CURDATE() - INTERVAL 7 DAY ORDER BY packets.time DESC) AS base GROUP BY dev_pseudonym, gtw_id");
?>
