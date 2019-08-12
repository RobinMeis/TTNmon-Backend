# /api/device/<devEUI>
Modify a single device

## Method DELETE
Deletes the specified device

### Parameters
devEUI

### Headers
Authorization has to be set with Authorization token

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
Device has been successfully removed

#### Error code 1
DevEUI is invalid, Status 400 is sent

#### Error code 2
DevEUI is not found for the given Authorization Token, Status 404 is sent
