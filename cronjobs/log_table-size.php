<?php
require_once("../config.php");
$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

$sql = "SELECT
  TABLE_NAME AS `table_name`,
  ((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `size_MB`
FROM
  information_schema.TABLES
WHERE TABLE_SCHEMA = '".$MYSQL_DB."'
ORDER BY
  (DATA_LENGTH + INDEX_LENGTH)
DESC";

$csv_data = array(
  "authorizations" => null,
  "devices" => null,
  "packets" => null,
  "gateways" => null,
  "preprocessed_gateway-list" => null,
  "preprocessed_links" => null,
);

$sum = 0;
foreach ($pdo->query($sql) as $table) {
  $csv_data[$table["table_name"]] = $table["size_MB"];
  $sum += $table["size_MB"];
}
if (!file_exists("table-size.csv"))
  file_put_contents("table-size.csv", "ISO 8601 date;Size in MB\ndate;authorizations;devices;packets;gateways;preprocessed_gateway-list;preprocessed_links;sum\n");

file_put_contents("table-size.csv", date("c").";".$csv_data["authorizations"].";".$csv_data["devices"].";".$csv_data["packets"].";".$csv_data["gateways"].";".$csv_data["preprocessed_gateway-list"].";".$csv_data["preprocessed_links"].";".$sum."\n", FILE_APPEND);
?>
