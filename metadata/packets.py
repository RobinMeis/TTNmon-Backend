from . import packet

class packets:
    def __init__(self, device):
        self.packets = {}
        self.__device = device

        self.count = 0
        self.minSF = None
        self.maxSF = None
        self.minGatewayCount = None
        self.maxGatewayCount = None

    def fromInflux(self, data):
        for raw_pkt in data:
            pkt = packet.packet()
            pkt.fromInflux(self.__device, raw_pkt)
            self.packets[pkt.timestamp] = pkt

    def calcStats(self):
        for packet in self.packets:
            self.count += 1
            if (packet.SF < self.minSF or self.minSF == None):  # find minSF
                self.minSF = packet.SF

            if (packet.SF > self.maxSF or self.maxSF == None):  # find maxSF
                self.maxSF = packet.SF

            if (packet.gateways.gatewayCount < self.minGatewayCount or self.minGatewayCount == None):  # find minGatewayCount
                self.minGatewayCount = packet.gateways.gatewayCount

            if (packet.gateways.gatewayCount > self.maxGatewayCount or self.maxGatewayCount == None):  # find maxGatewayCount
                self.maxGatewayCount = packet.gateways.gatewayCount
