# /api/device
Handles the device management

## Method GET
Lists all devices belonging to an authentication token

### URL Parameters
`auth_token` The token which should be used for device query

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
Devices where queried successfully. They are encoded in the field `devices`

Example: TODO

#### Error code 1
The authentication token could not be found. No devices are returned

#### Error code 2
Incomplete input

## Method POST
Adds a device to an authentication token

### URL Parameters
`auth_token` The token which the device should be added to
`deveui` The hardware serial of the device provived by TTN

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
The device was added successfully

#### Error code 1
The auth_token was not found. Device was not added

#### Error code 2
Incomplete input

#### Error code 3
The device (deveui) is already registered with (another) auth_token. It has to be removed before it can be registered agiain

#### Error code 4
The deveui is invalid (Hex string length is not 16 chars) or hex2bin can't convert the hex string to binary.

#### Error code 5
Manual device registration was disabled for security reasons. Your device will be adopted automatically when we receive the first message.

## Method DELETE
Removes an device from an authentication token

### URL Parameters
`auth_token` The token which the device should be remove from
`deveui` The hardware serial of the device provived by TTN

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
The device was removed successfully

#### Error code 1
The device was not found or does not belong to this auth token

#### Error code 2
Incomplete input
