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
                    "payloadLength": packet.payloadLength,
                    "fport": packet.fport
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
    def getPacketsMetadata(self, device, dateFrom, dateTo):
        query = """SELECT
                        time,
                        packetCount,
                        frequency,
                        modulation,
                        BW,
                        CR_k,
                        CR_n,
                        SF,
                        payloadLength,
                        latitude,
                        longitude,
                        altitude
                    FROM
                        packets_metadata
                    WHERE
                        devPseudonym=$devPseudonym and
                        time>=$dateFrom and
                        time<=$dateTo"""
        params = {
          "devPseudonym": str(device.pseudonym),
          "dateFrom": dateFrom.strftime('%Y-%m-%dT%H:%M:%SZ'),
          "dateTo": dateTo.strftime('%Y-%m-%dT%H:%M:%SZ')
        }

        return self.cnx.query(query, bind_params=params)

    # Fetches connection metadata for a device within the specified timerange
    def getPacketsGateways(self, device, dateFrom, dateTo):
        query = """SELECT
                       time,
                       RSSI,
                       SNR,
                       channel,
                       gtwID,
                       gtwTime,
                       latitude,
                       longitude,
                       altitude,
                       distance
                   FROM
                       packets_gateways_metadata
                   WHERE
                       devPseudonym=$devPseudonym and
                       time>=$dateFrom and
                       time<=$dateTo"""
        params = {
          "devPseudonym": str(device.pseudonym),
          "dateFrom": dateFrom.strftime('%Y-%m-%dT%H:%M:%SZ'),
          "dateTo": dateTo.strftime('%Y-%m-%dT%H:%M:%SZ')
        }

        return self.cnx.query(query, bind_params=params)

    def countPackets(self, device, dateFrom, dateTo):
        if (device == None):
            query = """SELECT count(SF)
                                    FROM
                                        packets_metadata
                                    WHERE
                                        time>=$dateFrom and
                                        time<=$dateTo"""
        else:
            query = """SELECT count(SF)
                        FROM
                            packets_metadata
                        WHERE
                            devPseudonym=$devPseudonym and
                            time>=$dateFrom and
                            time<=$dateTo"""

        params = {
            "devPseudonym": str(device.pseudonym),
            "dateFrom": dateFrom.strftime('%Y-%m-%dT%H:%M:%SZ'),
            "dateTo": dateTo.strftime('%Y-%m-%dT%H:%M:%SZ')
        }

        packets = 0
        for line in cnx.query(query).get_points():
            packets = line["count"]

        return packets
