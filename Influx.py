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
        result = self.cnx.query("show databases;")
        print(result)

    def addPacket(self, packet):
        cache = []

        cache.append (
            {
                "measurement": "packets_metadata",
                "tags": {
                    "devPseudonym": packet.pseudonym,
                },
                "time": packet.timestamp,
                "fields": {
                    "frequency": packet.frequency,
                    "packetCount": packet.counter,
                    "CR_n": packet.CR_n,
                    "CR_k": packet.CR_k,
                    "SF": packet.SF,
                    "BW": packet.BW,
                    #"gatewayCount": row["gateway_count"]
                }
            }
        )
