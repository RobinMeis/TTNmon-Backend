import mysql.connector
from mysql.connector import pooling, ClientFlag
import string
import random
import device

class MySQL:
    def __init__(self, host, username, password, database, pool_reset_session, ssl_verify_cert, ssl_verify_identity, ssl_disabled, ca_cert, pool_name, pool_size):
        self.cnxpool = mysql.connector.pooling.MySQLConnectionPool(pool_name = pool_name,
                                                                    pool_size = pool_size,
                                                                    pool_reset_session=pool_reset_session,
                                                                    host = host,
                                                                    ssl_ca = ca_cert,
                                                                    client_flags = [ClientFlag.SSL],
                                                                    ssl_verify_cert = ssl_verify_cert,
                                                                    ssl_verify_identity = ssl_verify_identity,
                                                                    ssl_disabled = ssl_disabled,
                                                                    user = username,
                                                                    password = password,
                                                                    database = database,
                                                                  )

    # Creates a new authorization tokens
    def createToken(self):
        letters = string.ascii_letters + string.digits
        token = ''.join(random.choice(letters) for i in range(20))
        cnx = self.cnxpool.get_connection()
        cur = cnx.cursor()
        stmt = """INSERT INTO `authorizations` (
                `token`,
                `created`
            ) VALUES (
                %s,
                UTC_TIMESTAMP()
            )"""
        try:
            cur.execute(stmt, (token,))
        except:
            token = None
        else:
            cnx.commit()
        cnx.close()
        return token

    # Checks if a supplied authentication token is valid
    def checkToken(self, auth_token):
        cnx = self.cnxpool.get_connection()
        cur = cnx.cursor(dictionary=True)
        stmt = """SELECT
                    `created`
                FROM `authorizations` WHERE
                    `token` = %s"""
        cur.execute(stmt, (auth_token,))
        result = cur.fetchone()
        cnx.close()

        if result == None:
            return False
        else:
            return result

    # returns the device pseudonym for a supplied auth token
    def getPseudonym(self, auth_token, device):
        cnx = self.cnxpool.get_connection()
        cnx.commit()
        cur = cnx.cursor(dictionary=True)
        stmt = """SELECT
                    `pseudonym`,
                    `created`,
                    `lastSeen`
                FROM `devices` WHERE
                    `authorization` = %s and
                    `devEUI` = %s
                LIMIT 1"""
        cur.execute(stmt, (auth_token, device.devEUI))
        result = cur.fetchone()
        cnx.close()

        if (result == None):
            return None
        else:
            device.pseudonym = result["pseudonym"]
            device.created = result["created"]
            device.lastSeen = result["lastSeen"]
            return result["pseudonym"]

    # creates a new device
    def createDevice(self, auth_token, device):
        cnx = self.cnxpool.get_connection()
        cur = cnx.cursor()
        stmt = """INSERT INTO `devices` (
                    `authorization`,
                    `devEUI`,
                    `devID`,
                    `appID`,
                    `created`,
                    `lastSeen`,
                    `latitude`,
                    `longitude`,
                    `altitude`
                ) VALUES (
                    %(authorization)s,
                    %(devEUI)s,
                    %(devID)s,
                    %(appID)s,
                    UTC_TIMESTAMP(),
                    UTC_TIMESTAMP(),
                    %(latitude)s,
                    %(longitude)s,
                    %(altitude)s
                )"""

        values = {
            "authorization": auth_token,
            "devEUI": device.devEUI,
            "devID": device.devID,
            "appID": device.appID,
            "latitude": device.location.latitude,
            "longitude": device.location.longitude,
            "altitude": device.location.altitude
        }
        try:
            cur.execute(stmt, values)
        except mysql.connector.errors.IntegrityError: #Combination of authorization/devEUI does already exist
            pseudonym = None
        else:
            pseudonym = cur.lastrowid
            device.pseudonym = pseudonym

        cnx.commit()
        cnx.close()
        return pseudonym

    # removes a device
    def removeDevice(self, auth_token, device):
        cnx = self.cnxpool.get_connection()
        cnx.commit()
        cur = cnx.cursor()
        stmt = """DELETE FROM `devices` WHERE
                    `authorization` = %s and
                    `devEUI` = %s"""
        cur.execute(stmt, (auth_token, device.devEUI))
        cnx.commit()
        cnx.close()

        return cur.rowcount > 0

    # updates a device
    def updateDevice(self, auth_token, device):
        cnx = self.cnxpool.get_connection()
        cnx.commit()
        cur = cnx.cursor()
        stmt = """UPDATE `devices` SET
                        `lastSeen` = UTC_TIMESTAMP(),
                        `latitude` = %(latitude)s,
                        `longitude` = %(longitude)s,
                        `altitude` = %(altitude)s
                    WHERE
                        `authorization` = %(authorization)s and
                        `devEUI` = %(devEUI)s"""

        values = {
            "authorization": auth_token,
            "devEUI": device.devEUI,
            "latitude": device.location.latitude,
            "longitude": device.location.longitude,
            "altitude": device.location.altitude
        }

        cur.execute(stmt, values)
        cnx.commit()
        cnx.close()

        if cur.rowcount == 0:
            return False
        else:
            return True

    # checks an authorization token
    def checkToken(self, auth_token):
        cnx = self.cnxpool.get_connection()
        cnx.commit()
        cur = cnx.cursor()
        stmt = """SELECT
                    `created`
                FROM `authorizations` WHERE
                    `token` = %s"""

        cur.execute(stmt, (auth_token,))
        result = cur.fetchone()
        cnx.close()

        return result is not None

    # returns a list of devices for the supplied auth token
    def getDevices(self, auth_token):
        devices = []
        cnx = self.cnxpool.get_connection()
        cnx.commit()
        cur = cnx.cursor(dictionary=True)
        stmt = """SELECT
                    `pseudonym`,
                    `devEUI`,
                    `appID`,
                    `devID`,
                    `created`,
                    `lastSeen`,
                    `latitude`,
                    `longitude`,
                    `altitude`
                FROM `devices` WHERE
                    `authorization` = %s"""

        cur.execute(stmt, (auth_token,))
        for row in cur:
            dev = device.device()
            dev.fromDB(row)
            devices.append(dev)
        cnx.close()
        return devices

    def getDevice(self, dev):
        cnx = self.cnxpool.get_connection()
        cnx.commit()
        cur = cnx.cursor(dictionary=True)
        stmt = """SELECT
                    `pseudonym`,
                    `devEUI`,
                    `appID`,
                    `devID`,
                    `created`,
                    `lastSeen`,
                    `latitude`,
                    `longitude`,
                    `altitude`,
                    `authorization`
                FROM `devices` WHERE
                    `pseudonym` = %s"""

        cur.execute(stmt, (dev.pseudonym,))
        result = cur.fetchone()
        cnx.close()
        if (result):
            dev.fromDB(result)
            return True
        else:
            return False
