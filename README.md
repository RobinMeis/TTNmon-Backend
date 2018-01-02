# TTNmon-Backend
Monitoring of TTN nodes

## Requirements
- PHP
- MySQL

## How does it work?
You have to upload the Web_Hook to a public web server. The file receive.php has to be called by the TTN HTTP integration. After configuration you will be able to store your data.

## Setup
1. Create new database
2. Restore the database structure from Database/
3. Copy config-example.php to config.php
4. Modify your configuration

## Authentication
To prevent third parties to send spoofed data, there is an authentication mechanism. The database structure is explained in Database/

1. The Authentication header is checked
2. If valid the device serial is checked
3. Only if the device serial is registered to the users key, the data accepted
