# TTNmon-Backend
Monitoring of TTN nodes

## Requirements
- PHP
- MySQL

## Setup
0. Upload backend to public web server
1. Create new database
2. Restore the database structure from Database/
3. Copy config-example.php to config.php
4. Modify your configuration

## Authentication
To prevent third parties to send spoofed data, there is an authentication mechanism. The database structure is explained in Database/

1. The Authentication header is checked
2. If valid the device serial is checked
3. Only if the device serial is registered to the users key, the data accepted

## Directories
- webhook/ Contains the webhook for the TTN HTTP integration
