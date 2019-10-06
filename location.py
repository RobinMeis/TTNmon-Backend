import decimal

class location:
    def __init__(self):
        self.__latitude = None
        self.__longitude = None
        self.__altitude = None

    def fromTTN(self, metadata):
        try:
            self.latitude = float(metadata["latitude"])
            self.longitude = float(metadata["longitude"])
        except (KeyError, ValueError):
            try:
                self.latitude = float(metadata["metadata"]["latitude"])
                self.longitude = float(metadata["metadata"]["longitude"])
            except (KeyError, ValueError):
                self.__latitude = None
                self.__longitude = None

        try:
            self.altitude = float(metadata["altitude"])
        except (KeyError, ValueError):
            try:
                self.altitude = float(metadata["metadata"]["altitude"])
            except (KeyError, ValueError):
                self.__altitude = None

    def fromDB(self, row):
        try:
            self.latitude = row["latitude"]
            self.longitude = row["longitude"]
        except (KeyError, ValueError):
            self.__latitude = None
            self.__longitude = None

        try:
            self.altitude = row["altitude"]
        except (KeyError, ValueError):
            self.__altitude = None

    #latitude getter/setter
    @property
    def latitude(self):
        return self.__latitude

    @latitude.setter
    def latitude(self, latitude):
        if (isinstance(latitude, (float, decimal.Decimal))):
            self.__latitude = latitude
        else:
            raise ValueError("Invalid type of latitude")

    #longitude getter/setter
    @property
    def longitude(self):
        return self.__longitude

    @longitude.setter
    def longitude(self, longitude):
        if (isinstance(longitude, (float, decimal.Decimal))):
            self.__longitude = longitude
        else:
            raise ValueError("Invalid type of longitude")

    #altitude getter/setter
    @property
    def altitude(self):
        return self.__altitude

    @altitude.setter
    def altitude(self, altitude):
        if (isinstance(altitude, (int, float, decimal.Decimal))):
            self.__altitude = altitude
        else:
            raise ValueError("Invalid type of altitude")
