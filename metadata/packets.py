from . import packet
from . import gateway

class packets:
    def __init__(self, device):
        self.packets = {}
        self.__device = device

        self.count = 0
        self.minSF = None
        self.maxSF = None
        self.minGatewayCount = None
        self.maxGatewayCount = None
        self.minRSSI = None
        self.maxRSSI = None
        self.minSNR = None
        self.maxSNR = None
        self.gateways = {}

    def packetsFromInflux(self, data):
        for raw_pkt in data:
            pkt = packet.packet()
            pkt.fromInflux(self.__device, raw_pkt)
            self.packets[pkt.timestamp] = pkt

    def gatewaysFromInflux(self, data):
        for raw_gtw in data:
            gtw = gateway.gateway()
            gtw.fromInflux(raw_gtw)
            try:
                self.packets[gtw.timestamp]
            except:
                pass
            else:
                self.packets[gtw.timestamp].addGateway(gtw)

    def calcStats(self):
        for timestamp, packet in self.packets.items():
            self.count += 1
            if (self.minSF == None or packet.SF < self.minSF):  # find minSF
                self.minSF = packet.SF

            if (self.maxSF == None or packet.SF > self.maxSF):  # find maxSF
                self.maxSF = packet.SF

            if (self.minGatewayCount == None or packet.gatewayCount < self.minGatewayCount):  # find minGatewayCount
                self.minGatewayCount = packet.gatewayCount

            if (self.maxGatewayCount == None or packet.gatewayCount > self.maxGatewayCount):  # find maxGatewayCount
                self.maxGatewayCount = packet.gatewayCount

            for gateway in packet.gateways:
                if (self.minRSSI == None or gateway.RSSI < self.minRSSI): # find minRSSI
                    self.minRSSI = gateway.RSSI

                if (self.maxRSSI == None or gateway.RSSI > self.maxRSSI): # find maxRSSI
                    self.maxRSSI = gateway.RSSI

                if (self.minSNR == None or gateway.SNR < self.minSNR): # find minSNR
                    self.minSNR = gateway.SNR

                if (self.maxSNR == None or gateway.SNR > self.maxSNR): # find maxSNR
                    self.maxSNR = gateway.SNR

                try:
                    self.gateways[gateway.gtwID]
                except:
                    self.gateways[gateway.gtwID] = {}
                    self.gateways[gateway.gtwID]["obj"] = gateway
                    self.gateways[gateway.gtwID]["packets"] = 1
                    self.gateways[gateway.gtwID]["RSSImin"] = None
                    self.gateways[gateway.gtwID]["RSSImax"] = None
                    self.gateways[gateway.gtwID]["SNRmin"] = None
                    self.gateways[gateway.gtwID]["SNRmax"] = None
                else:
                    self.gateways[gateway.gtwID]["packets"] += 1

                if (self.gateways[gateway.gtwID]["RSSImin"] == None or gateway.RSSI < self.gateways[gateway.gtwID]["RSSImin"]): # find minRSSI
                    self.gateways[gateway.gtwID]["RSSImin"] = gateway.RSSI

                if (self.gateways[gateway.gtwID]["RSSImax"] == None or gateway.RSSI > self.gateways[gateway.gtwID]["RSSImax"]): # find maxRSSI
                    self.gateways[gateway.gtwID]["RSSImax"] = gateway.RSSI

                if (self.gateways[gateway.gtwID]["SNRmin"] == None or gateway.RSSI < self.gateways[gateway.gtwID]["SNRmin"]): # find minRSSI
                    self.gateways[gateway.gtwID]["SNRmin"] = gateway.SNR

                if (self.gateways[gateway.gtwID]["SNRmax"] == None or gateway.RSSI > self.gateways[gateway.gtwID]["SNRmax"]): # find maxRSSI
                    self.gateways[gateway.gtwID]["SNRmax"] = gateway.SNR

