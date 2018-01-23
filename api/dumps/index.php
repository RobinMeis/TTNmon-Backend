<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require_once("../../config.php");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  $msg["msg_en"] = "The following dumps are available for download";
  $msg["error"] = 0;
  $msg["dumps"] = array();
  $files = scandir (".");
  foreach ($files as $file) {
    $n = 0;
    if (strpos($file, "ttnmon_dump-") !== FALSE) {
      $msg["dumps"][$n] = array();#
      $msg["dumps"][$n]["download_url"] = $ENDPOINT_URL ."api/dumps/".$file;
      $msg["dumps"][$n]["filesize"] = filesize ($file);
      $msg["dumps"][$n]["filetime"] = date ("c", filemtime ($file));
      $msg["dumps"][$n]["filename"] = $file;
    }
  }
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
