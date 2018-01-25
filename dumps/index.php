<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
require_once("../../config.php");

$msg = array();
if ($_SERVER['REQUEST_METHOD'] == "GET") {
  $msg["msg_en"] = "The following dumps are available for download";
  $msg["error"] = 0;
  $msg["dumps"] = array();

  $n = 0;
  $handle = fopen("sha256sums.txt", "r"); //Read checksum file
  if ($handle) {
    while (($dump = fgets($handle)) !== false) {
      $dump = explode(" ", $dump); //Prepare checksum and filename
      $sha256sum = $dump[0];
      $filename = str_replace("\n", "", end(explode("/", $dump[2])));
      if (file_exists($filename)) { //Check if files exists
        $msg["dumps"][$n] = array(); //Prepare JSON
        $msg["dumps"][$n]["download_url"] = $ENDPOINT_URL ."api/dumps/".$filename;
        $msg["dumps"][$n]["filesize"] = filesize ($filename);
        $msg["dumps"][$n]["filetime"] = date ("c", filemtime ($filename));
        $msg["dumps"][$n]["filename"] = $filename;
        $msg["dumps"][$n]["sha256"] = $sha256sum;
        $n++;
      }
    }
    fclose($handle);
  }
} else {
  $msg["error"] = -1;
  $msg["msg_en"] = "Error: Unsupported method";
}

print(json_encode($msg));
?>
