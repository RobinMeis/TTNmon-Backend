import datetime
import os

# Handles all loggers for TTNmonAPI
class logging:
    def __init__(self, logdir):
        self.__logdir = logdir.rstrip("/")
        self.invalid_packets = self.new("invalid", "packets") #Loggers can be directly accessed to log to
        self.packets = self.new("packet", "packets")
        self.general = self.new("general")

    def new(self, logname, subdir="", overwrite=False): #Creates a new logger. Might be used to create external loggers
        return log(logname, "%s/%s" % (self.__logdir, subdir), overwrite)

#Represents a single log topic
class log:
    def __init__(self, logname, logdir="", overwrite=False):
        self.__logdir = logdir.rstrip("/")
        self.__logname = logname
        self.__overwrite = overwrite
        self.__enabled = False

        if not os.path.isdir(self.__logdir):
            print("[WARNING] Logdir %s/ does not exist. Logging is unavailable using this logger" % (self.__logdir,))

    def logAppend(self, message): #Appends to an existing logfile
        if self.__enabled:
            timestamp = datetime.datetime.now()

            try:
                with open("%s/%s.log" % (self.__logdir, self.__logname), 'a+') as log:
                    log.write("[%s] %s\n" % (timestamp.strftime("%Y-%m-%d %H:%M:%S"), message,))
            except Exception as e:
                print("[WARNING] Logging to %s/%s.log failed: %s" % (self.__logdir,self.__logname, e))


    def logWrite(self, message): #Write a new log. Can either overwrite existing file or create a new file for each entry
        if self.__enabled:
            timestamp = datetime.datetime.now()

            if self.__overwrite:
                filename = "%s/%s.log" % (self.__logdir, self.__logname,)
            else:
                n = 0
                while True: #Do not overwrite existing logfiles with same timestamp
                    filename = "%s/%s-%s-%d.log" % (self.__logdir, self.__logname, timestamp.strftime("%Y-%m-%d_%H-%M-%S"), n)
                    if not os.path.isfile(filename):
                        break
                    n += 1

            try:
                with open(filename, 'w+') as log:
                    log.write(message)
            except Exception as e:
                print("[WARNING] Logging to %s failed: %s" % (filename, e))

    def enable(self): #Enables the logger (default: disabled)
        self.__enabled = True

    def disable(self): #Disables the logger (default: disabled)
        self.__enabled = False

    @property #Getter for enabled
    def enabled(self):
        return self.__enabled

    @enabled.setter #Enables / Disables the logger. Boolean accepted
    def enabled(self, enabled):
        if (isinstance(enabled, bool)):
            self.__enabled = enabled
        else:
            raise ValueError("Invalid type of enabled")
