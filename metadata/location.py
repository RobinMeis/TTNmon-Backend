class location:
    def __init__(self, metadata):
        self.__latitude = None
        self.__longitude = None
        self.__altitude = None

        try:
            self.latitude = metadata["metadata"]["latitude"]
            self.longitude = metadata["metadata"]["longitude"]
        except (KeyError, ValueError):
            self.__latitude = None
            self.__longitude = None

        try:
            self.altitude = metadata["metadata"]["altitude"]
        except (KeyError, ValueError):
            self.__altitude = None

    #latitude getter/setter
    @property
    def latitude(self):
        return self.__latitude

    @latitude.setter
    def latitude(self, latitude):
        if (isinstance(latitude, float)):
            self.__latitude = latitude
        else:
            raise ValueError("Invalid type of latitude")

    #longitude getter/setter
    @property
    def longitude(self):
        return self.__longitude

    @longitude.setter
    def longitude(self, longitude):
        if (isinstance(longitude, float)):
            self.__longitude = longitude
        else:
            raise ValueError("Invalid type of longitude")

    #altitude getter/setter
    @property
    def altitude(self):
        return self.__altitude

    @altitude.setter
    def altitude(self, altitude):
        if (isinstance(altitude, (int,float))):
            self.__altitude = altitude
        else:
            raise ValueError("Invalid type of altitude")