from . import packet

class packets:
    def __init__(self, device):
        self.packets = {}
        self.__device = device

    def fromInflux(self, data):
        for raw_pkt in data:
            pkt = packet.packet()
            pkt.fromInflux(self.__device, raw_pkt)
            self.packets[pkt.timestamp] = pkt
