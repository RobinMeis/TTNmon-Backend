<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") { //Get packets
  $msg["msg_en"] = "The following dumps are available for download";
  $msg["error"] = 0;
  $msg["dumps"] = array();
  $files = scandir (".");
  foreach ($files as $file)
    if (strpos($file, "ttnmon_dump-") !== FALSE)
      $msg["dumps"][] = $file;
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
