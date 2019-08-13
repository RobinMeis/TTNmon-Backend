#Stores metadata of a packet for a specific gateway

import datetime
import dateutil.parser
import geopy.distance

import location

class gateway:
    def __init__(self, packet=None):
        self.__packet = packet
        self.gtwID = None
        self.__time = None
        self.__channel = None
        self.__RSSI = None
        self.__SNR  = None
        self.__rf_chain = None
        self.location = location.location()

    def fromTTN(self, metadata):
        self.gtwID = metadata["gtw_id"]
        self.timestamp = metadata["time"]
        self.channel = metadata["channel"]
        self.RSSI = metadata["rssi"]
        self.SNR = metadata["snr"]
        self.rf_chain = metadata["rf_chain"]
        self.location.fromTTN(metadata)

    #time getter/setter
    @property
    def timestamp(self):
        return self.__timestamp

    @timestamp.setter
    def timestamp(self, timestamp):
        if (isinstance(timestamp, str)): #Convert string to timestamp
            try:
                self.__timestamp = dateutil.parser.parse(timestamp)
            except ValueError: #No timestamp provided
                self.__timestamp = None
        elif (isinstance(timestamp, datetime)): #Copy datetime
            self.__timestamp = timestamp
        else:
            raise ValueError("Invalid type of timestamp. Must be str or datetime")

    @property
    def unixTimestamp(self):
        return datetime.datetime.timestamp(self.__timestamp)

    #channel getter/setter
    @property
    def channel(self):
        return self.__channel

    @channel.setter
    def channel(self, channel):
        if (isinstance(channel, int)): #Channel must be of type int...
            self.__channel = channel
        else:
            raise ValueError("Invalid type of channel")

    #RSSI getter/setter
    @property
    def RSSI(self):
        return self.__RSSI

    @RSSI.setter
    def RSSI(self, RSSI):
        if (isinstance(RSSI, int)): #RSSI must be of type int...
            self.__RSSI = RSSI
        else:
            raise ValueError("Invalid type of RSSI")

    #SNR getter/setter
    @property
    def SNR(self):
        return self.__SNR

    @SNR.setter
    def SNR(self, SNR):
        if (isinstance(SNR, (float, int))): #SNR must be of type int...
            self.__SNR = float(SNR)
        else:
            raise ValueError("Invalid type of SNR")

    #rf_chain getter/setter
    @property
    def rfChain(self):
        return self.__rf_chain

    @rfChain.setter
    def rfChain(self, rf_chain):
        if (isinstance(rf_chain, int)): #rf_chain must be of type int...
            self.__rf_chain = rf_chain
        else:
            raise ValueError("Invalid type of rf_chain")

    #Returns the distance between node and gateway in m
    @property
    def distance(self):
        if (self.location.latitude is None or self.location.longitude is None or self.__packet.location.latitude is None or self.__packet.location.longitude is None):
            return None
            
        gateway = (self.location.latitude, self.location.longitude)
        node = (self.__packet.location.latitude, self.__packet.location.longitude)

        return geopy.distance.distance(gateway, node).m
