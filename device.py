import location
import datetime

class device:
    def __init__(self):
        self.__pseudonym = None
        self.__devEUI = None
        self.appID = None
        self.devID = None
        self.__created = None
        self.__lastSeen = None
        self.location = location.location()
        self.comment = None

    def fromTTN(self, packet):
        self.location.fromTTN(packet)
        self.appID = packet["app_id"]
        self.devID = packet["dev_id"]
        self.devEUI = packet["hardware_serial"]

    def fromDB(self, row):
        self.location.fromDB(row)
        self.pseudonym = row["pseudonym"]
        self.appID = row["appID"]
        self.devID = row["devID"]
        self.devEUI = row["devEUI"]
        self.created = row["created"]
        self.lastSeen = row["lastSeen"]

    #pseudonym getter/setter
    @property
    def pseudonym(self):
        return self.__pseudonym

    @pseudonym.setter
    def pseudonym(self, pseudonym):
        if (isinstance(pseudonym, int)): #Store devEUI as string
            if (pseudonym >= 0): #must be positive
                self.__pseudonym = pseudonym
            else:
                raise ValueError("Invalid value of pseudonym")
        else:
            raise ValueError("Invalid type of pseudonym")

    #devEUI getter/setter
    @property
    def devEUI(self):
        return self.__devEUI

    @devEUI.setter
    def devEUI(self, devEUI):
        if (isinstance(devEUI, str)): #Store devEUI as string
            if (len(devEUI) == 16): #devEUI is 8 Bytes long
                self.__devEUI = devEUI
            else:
                raise ValueError("Invalid length of devEUI")
        else:
            raise ValueError("Invalid type of devEUI")

    #created getter/setter
    @property
    def created(self):
        return self.__created

    @created.setter
    def created(self, created):
        if (isinstance(created, str)): #Convert string to created
            try:
                self.__created = dateutil.parser.parse(created)
            except ValueError: #No created provided
                self.__created = None
        elif (isinstance(created, datetime.datetime)): #Copy datetime
            self.__created = created
        else:
            raise ValueError("Invalid type of created. Must be str or datetime")

    #lastSeen getter/setter
    @property
    def lastSeen(self):
        return self.__lastSeen

    @lastSeen.setter
    def lastSeen(self, lastSeen):
        if (isinstance(lastSeen, str)): #Convert string to lastSeen
            try:
                self.__lastSeen = dateutil.parser.parse(lastSeen)
            except ValueError: #No lastSeen provided
                self.__lastSeen = None
        elif (isinstance(lastSeen, datetime.datetime)): #Copy datetime
            self.__lastSeen = lastSeen
        else:
            raise ValueError("Invalid type of lastSeen. Must be str or datetime")
