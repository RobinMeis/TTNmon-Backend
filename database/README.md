# Database structure for TTNmon
The file database_structure.sql contains the database_structure for the backend. It can be restored into a database using phpMyAdmin.

## Table authorizations
The table authorizations contains the authorization tokens with a maximum length of 20 chars. The authorization token is unique and is used in table relations. This token is passed to the Web Hook using the Authorization header.

## Table devices
The table devices contains the hardware serials of the devices associated to an authorization token.
- The row authorization contains the authorization token from the table authorizations.
- The row deveui contains the hardware serial provived by TTN. It is possible to register multiple hardware serials for a single authorization token. deveui is unique and is used as index

## Table packets
The table packets contains all packets received.
- CR_k and CR_n means coding rate while it is read like CR_k/CR_n
- latitude, longitude and altitude are optional and can be setup in the TTN configuration
- Even if the row modulation is an enum between LORA and FSK, FSK is currently unsupported
- id is an auto incremented, unique value

## Table gateways
The table gateways contains the gateways which received a packet
- packet_id contains the id of the packet
- There can be multiple gateways which received the same packet, so the packet id might not be unique
