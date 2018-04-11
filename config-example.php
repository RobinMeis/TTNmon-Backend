<?php
$MYSQL_SERVER                       = "localhost";
$MYSQL_DB                           = "database";
$MYSQL_USER                         = "user";
$MYSQL_PASSWD                       = "password";
$AUTO_ADOPTION                      = FALSE; //If true, deveui will be added automatically be webhook to authoriziation if device is not registered yet
$ADOPTION_PROOF                     = FALSE; //If true, a device will be checked with the application key using TTN API before auto adoption
$ALLOW_MANUAL_DEVICE_REGISTRATION   = TRUE; //Enables device registration using the API. This is not recommended when enabling adoption proof as the API can't perform the proof against TTN backend
$LOG_WEBHOOK                        = FALSE; //Logs packets in webhook/logging/. Warning: Access to logs is prevented using htaccess. If your webserver doesn't support htaccess the logs are publicly available which exposes private information
$LOG_DEVEUI                         = "<deveui>"; //DevEUI to log
$ENDPOINT_URL                       = "https://api.meis.space/"; //URL of TTNmon Backend
?>
