# /api/token
Handles the creation and removal of authentication tokens

## Method POST
Creates a new token

### Parameters
None

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
The new token was generated successfully. It is passed in the field `auth_token`. A token is 20 chars long.

#### Error code 1
There occured an error while generating the code. No auth token is returned

## Method DELETE
Deletes a token and all devices belonging to it

### URL Parameters
`auth_token` The auth token to delete

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`.

#### Error code 0
The auth token and its devices have been deleted successfully

#### Error code 1
The auth token could not be found

#### Error code 2
Invalid request. At least one parameter is missing
