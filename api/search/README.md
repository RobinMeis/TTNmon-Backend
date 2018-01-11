# /api/search

## Method GET
Returns a devices pseudonym

### URL Parameters
`query` Either a DevEUI or a pseudonym. DevEUIs are resolved to pseudonyms if they exist, pseudonyms are returned as their original value if they exist

### Returns
JSON encoded. Always returns a field `error_code` and `msg_en`. If pseudonym was found `pseudonym` is additionally returned

#### Error code 0
Found pseudonym

#### Error code 1
Could not find pseudonym

#### Error code 2
Parameter query is required

#### Error code 3
Invalid query. Input a DevEUI or a Pseudonym
