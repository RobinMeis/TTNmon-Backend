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
                    "gatewayCount": len(packet.gateways)
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
                    "gtwTime": gateway.unixTimestamp * 1e9,
                    "RSSI": gateway.RSSI,
                    "SNR": gateway.SNR,
                    "rfChain": gateway.rf_chain,
                    "latitude": gateway.location.latitude,
                    "longitude": gateway.location.longitude,
                    "altitude": gateway.location.altitude,
                    "distance": None
                }
              }
            )


        self.cnx.write_points(cache)
