# /api/token
Handles the creation of authentication tokens

## Method GET/POST
Creates a new token

### Parameters
None

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
The new token was generated successfully. It is passed in the field `auth_token`. A token is 20 chars long.

#### Error code 1
There occured an error while generating the code. No auth token is returned
