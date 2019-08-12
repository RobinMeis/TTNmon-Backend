# /api/getDevices
Returns a list of all available devices for an Authorization token

## Method GET
Returns list of devices

### Parameters
None

### Headers
Authorization has to be set with Authorization token

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`. In case if successfull authentication it also returns a list of devices.

#### Error code 0
Token is valid, device list has been generated

#### Error code 1
Token is invalid. HTTP Status 403 is sent
