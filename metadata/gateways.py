#This class stores a list of gateways for a packet

from . import gateway

class gateways:
    def __init__(self):
        self.gateways = []

    def addGateway(self, metadata):
        gtw = gateway.gateway()
        gtw.fromTTN(metadata)
        self.gateways.append(gtw)
