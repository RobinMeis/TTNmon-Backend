from influxdb import InfluxDBClient

class Influx:
    def __init__(self, host, port, username, password, database, ssl_disabled, verify_ssl):
        self.cnx = InfluxDBClient(host=host,
                                    port=port,
                                    username=username,
                                    password=password,
                                    database=database,
                                    ssl=(not ssl_disabled),
                                    verify_ssl=verify_ssl)

    #Adds a packet to the DB
    def addPacket(self, packet):
        cache = []

        cache.append (
            {
                "measurement": "packets_metadata",
                "tags": {
                    "devPseudonym": packet.device.pseudonym,
                    "modulation": packet.modulation,
                },
                "time": packet.timestamp,
                "fields": {
                    "frequency": packet.frequency,
                    "packetCount": packet.counter,
                    "CR_n": packet.CR_n,
                    "CR_k": packet.CR_k,
                    "SF": packet.SF,
                    "BW": packet.BW,
                    "latitude": packet.location.latitude,
                    "longitude": packet.location.longitude,
                    "altitude": packet.location.altitude,
                    "gatewayCount": len(packet.gateways),
                    "payloadLength": packet.payloadLength
                }
            }
        )

        for gateway in packet.gateways:
            cache.append(
              {
                "measurement": "packets_gateways_metadata",
                "tags": {
                    "devPseudonym": packet.device.pseudonym,
                    "gtwID": gateway.gtwID,
                    "channel": gateway.channel
                },
                "time": packet.timestamp,
                "fields": {
                    "gtwTime": gateway.unixTimestamp,
                    "RSSI": gateway.RSSI,
                    "SNR": gateway.SNR,
                    "rfChain": gateway.rf_chain,
                    "latitude": gateway.location.latitude,
                    "longitude": gateway.location.longitude,
                    "altitude": gateway.location.altitude,
                    "distance": gateway.distance
                }
              }
            )


        self.cnx.write_points(cache)

    # Fetches a series of received packets for a device within the specified timerange
    def getPacketsMetadata(self, device, start, end):
        query = "SELECT * FROM packets_metadata WHERE devPseudonym=$devPseudonym and time>=$start and time<=$end"
        params = {
          "devPseudonym": device.pseudonym,
          "start": start,
          "end": end
        }

        result = self.cnx.query(query, bind_params=params)
        print (result)

    # Fetches connection metadata for a device within the specified timerange
    def getPacketsGateways(self, device, start, end):
        query = "SELECT * FROM packets_gateways_metadata WHERE devPseudonym = $devPseudonym and time < $start and time > $end"
        params = {
          "devPseudonym": device.pseudonym,
          "start": start,
          "end": end
        }

        result = self.cnx.query(query, params=params)
        print (result)
