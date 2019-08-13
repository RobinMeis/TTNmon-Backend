#This class stores a list of gateways for a packet

from . import gateway

class gateways:
    def __init__(self, packet=None):
        self.gateways = []
        self.__packet = packet

    def addGateway(self, metadata):
        gtw = gateway.gateway(self.__packet)
        gtw.fromTTN(metadata)
        self.gateways.append(gtw)

    #Count gateways
    @property
    def gatewayCount(self):
        return len(self.gateways)
