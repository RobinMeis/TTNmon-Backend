#This class gets a decoded JSON from TTN and converts it to an packet object

import datetime
import dateutil.parser
import re

import device
from . import gateways

class packet:
    def __init__(self):
        self.device = device.device()
        self.__counter = None
        self.__timestamp = None
        self.__frequency = None
        self.__modulation = None
        self.__dataRate = None
        self.__SF = None
        self.__BW = None
        self.__CR = None
        self.__CR_k = None
        self.__CR_n = None
        self.__gateways = gateways.gateways(self)

    def fromTTN(self, packet):
        self.device.fromTTN(packet)
        self.counter = packet["counter"]
        self.timestamp = packet["metadata"]["time"]
        self.frequency = packet["metadata"]["frequency"]
        self.modulation = packet["metadata"]["modulation"]
        self.dataRate = packet["metadata"]["data_rate"]
        self.CR = packet["metadata"]["coding_rate"]

        for gateway in packet["metadata"]["gateways"]:
            self.__gateways.addGateway(gateway)

    #counter getter/setter
    @property
    def counter(self):
        return self.__counter

    @counter.setter
    def counter(self, counter):
        if (isinstance(counter, int)): #Counter must be of type int...
            if (counter >= 0): #...and >= 0
                self.__counter = counter
            else:
                raise ValueError("Invalid value of counter")
        else:
            raise ValueError("Invalid type of counter")

    #time getter/setter
    @property
    def timestamp(self):
        return self.__timestamp

    @timestamp.setter
    def timestamp(self, timestamp):
        if (isinstance(timestamp, str)): #Convert string to timestamp
            self.__timestamp = dateutil.parser.parse(timestamp)
        elif (isinstance(timestamp, datetime)): #Copy datetime
            self.__timestamp = timestamp
        else:
            raise ValueError("Invalid type of timestamp. Must be str or datetime")

    #frequency getter/setter
    @property
    def frequency(self):
        return self.__frequency

    @frequency.setter
    def frequency(self, frequency):
        if (isinstance(frequency, float)):
            self.__frequency = frequency
        else:
            raise ValueError("Invalid type of frequency")

    #modulation getter/setter
    @property
    def modulation(self):
        return self.__modulation

    @modulation.setter
    def modulation(self, modulation):
        if (modulation == "LORA"):
            self.__modulation = modulation
        else:
            raise ValueError("Unsupported modulation type")

    #dataRate getter/setter
    @property
    def dataRate(self):
        return self.__dataRate

    @dataRate.setter
    def dataRate(self, dataRate):
        if (isinstance(dataRate, str)):
            parser = re.compile('^SF(\d*)BW(\d*)$')
            results = parser.match(dataRate)
            try:
                self.__dataRate = results.group(0)
                self.SF = int(results.group(1))
                self.BW = int(results.group(2))
            except  IndexError:
                raise ValueError("Invalid dataRate string")
        else:
            raise ValueError("Invalid type of dataRate")

    #SF getter/setter
    @property
    def SF(self):
        return self.__SF

    @SF.setter
    def SF(self, SF):
        if (isinstance(SF, int)):
            self.__SF = SF
        else:
            raise ValueError("Invalid type of SF")

    #BW getter/setter
    @property
    def BW(self):
        return self.__BW

    @BW.setter
    def BW(self, BW):
        if (isinstance(BW, int)):
            self.__BW = BW
        else:
            raise ValueError("Invalid type of BW")

    #codingRate getter/setter
    @property
    def CR(self):
        return self.__CR

    @CR.setter
    def CR(self, CR):
        if (isinstance(CR, str)):
            parser = re.compile('^(\d*)\/(\d*)$')
            results = parser.match(CR)
            try:
                self.__CR = results.group(0)
                self.CR_k = int(results.group(1))
                self.CR_n = int(results.group(2))
            except  IndexError:
                raise ValueError("Invalid CR string")
        else:
            raise ValueError("Invalid type of CR")

    #CR_k getter/setter
    @property
    def CR_k(self):
        return self.__CR_k

    @CR_k.setter
    def CR_k(self, CR_k):
        if (isinstance(CR_k, int)):
            self.__CR_k = CR_k
        else:
            raise ValueError("Invalid type of CR_k")

    #CR_n getter/setter
    @property
    def CR_n(self):
        return self.__CR_n

    @CR_n.setter
    def CR_n(self, CR_n):
        if (isinstance(CR_n, int)):
            self.__CR_n = CR_n
        else:
            raise ValueError("Invalid type of CR_n")

    #gateways getter
    @property
    def gateways(self):
        return self.__gateways.gateways

    #location getter
    @property
    def location(self):
        return self.device.location
