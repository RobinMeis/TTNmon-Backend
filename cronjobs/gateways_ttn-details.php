<?php
function isset_null($input) {
  if (isset($input)) return $input;
  else return NULL;
}

require_once("../config.php");
$pdo = new PDO('mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWD);

$sql = "SELECT id, gtw_id FROM gateway_list WHERE ttn_updated is NULL or ttn_updated <= DATE_SUB(UTC_DATE(), INTERVAL 7 DAY)";
foreach ($pdo->query($sql) as $gateway) {
   echo $gateway["gtw_id"];

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, "https://account.thethingsnetwork.org/gateways/".$gateway["gtw_id"]);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_USERAGENT, "TTNmon.meis.space / ttnmon@meis.space");
   $output = curl_exec($ch);
   curl_close($ch);
   $json = json_decode($output, TRUE);
   if ($json !== NULL  && $json !== FALSE) {
     $data = array();
     $data["description"] = isset_null($json["attributes"]["description"]);
     $data["model"] = isset_null($json["attributes"]["model"]);
     $data["brand"] = isset_null($json["attributes"]["brand"]);
     $data["username"] = isset_null($json["owner"]["username"]);
     $data["id"] = $gateway["id"];
     $statement = $pdo->prepare("UPDATE gateway_list SET ttn_description = :description, ttn_brand = :brand, ttn_model = :model, ttn_username = :username, ttn_updated = UTC_DATE() WHERE id = :id");
     $statement->execute($data);
   }
}
?>
