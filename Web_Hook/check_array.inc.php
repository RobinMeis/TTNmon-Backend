<?php
/* A function to check the passed data for all required fields

Example:
$array = array(
  "data" => 1,
  "test" => 2,
);

$fields = array(
  "data", "tesst",
);

print(check_array($array, $fields)); //Will return false
*/

function check_array($array, $required_fields) {
  foreach ($required_fields as $required_field)
    if (!isset($array[$required_field])) return false; //Return false if value not found
  return true; //Return true if every value was found
}
?>
