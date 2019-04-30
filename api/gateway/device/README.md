# /api/gateway/device/
Generates a list of all packets of a specific device, received by a specific gateway.

## Method GET

### Parameters
#### pseudonym
Pseudonym of device. NOT DevEUI. If Pseudonym is unknown it can be queried using search
#### date_start
Date and time of last packet. Encoded as YYYY-MM-DD_HH-MM-SS (UTC)
#### date_end
Date and time of last packet. Encoded as YYYY-MM-DD_HH-MM-SS (UTC)
#### gtw_id
Gateway ID
#### timezone_offset
Timezone offset in minutes (optional)

### Returns
JSON encoded

#### Error code 0
Returns list of packets and stats

#### Error code 1
Required parameters missing
