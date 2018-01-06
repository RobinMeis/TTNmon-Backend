# API
## Status codes
If a request could be processed, it will return code 200, even if the user input was wrong. In that case the API will return a documented error code. In case of a server error, status code 500 is sent.

In case of an unsupported HTTP method, the error code will be -1

## Auto Adoption
Auto audoption registers unknown devices automatically when the webhook is called with a valid authorization token. The device is added to this authorization token and the data is stored. Auto adoption needs to be enabled in config.php

### Adoption proof
Adoption proof checks if the deveui received by the webhook is not spoofed. Using the downling url, the API Key for the TTN application is extracted. After that the device is checked. If the dev_eui matches the data received by the webhook, the auto adoption is performed.
