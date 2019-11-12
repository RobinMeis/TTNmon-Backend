from flask import Flask, g, make_response, jsonify, request
from flask_cors import CORS
import configparser
import json
import dateutil

from MySQL import MySQL
from Influx import Influx

import metadata.packet
import metadata.packets
import log
import device

config = configparser.ConfigParser() #Load config
config.read('TTNmon.conf')

TTNmonAPI = Flask(__name__) #Initialize App
CORS(TTNmonAPI)
with TTNmonAPI.app_context(): #Setup App
    frontend_url = config["General"]["frontend_url"]

    log = log.logging(config["Logging"]["logdir"])
    log.general.enabled = config["Logging"].getboolean("log_general_events")
    log.packets.enabled = config["Logging"].getboolean("log_all_packets")
    log.invalid_packets.enabled = config["Logging"].getboolean("log_invalid_packets")

    mySQL = MySQL(
      config["MySQL"]["host"],
      config["MySQL"]["username"],
      config["MySQL"]["password"],
      config["MySQL"]["database"],
      config["MySQL"].getboolean("pool_reset_session"),
      config["MySQL"].getboolean("ssl_verify_cert"),
      config["MySQL"].getboolean("ssl_verify_identity"),
      config["MySQL"].getboolean("ssl_disabled"),
      config["MySQL"]["ca_cert"],
      config["MySQL"]["pool_name"],
      config["MySQL"].getint("pool_size")
    )

    influx = Influx(
      config["Influx"]["host"],
      config["Influx"]["port"],
      config["Influx"]["username"],
      config["Influx"]["password"],
      config["Influx"]["database"],
      config["Influx"].getboolean("ssl_disabled"),
      config["Influx"].getboolean("verify_ssl")
    )

@TTNmonAPI.route("/")
def hello():
    response = "This is the TTNmon API. The frontend is located <a href=\"%s\">here</a>" % (frontend_url,)
    return make_response(response, 200)

@TTNmonAPI.route("/webhook/", methods=['POST'])
def webhook():
    print("Request")
    authorization = request.headers.get('Authorization')
    mySQL.getDevices(authorization)
    packet = metadata.packet.packet()
    try:
        packet.fromTTN(request.json)
        print(request.json)
    except Exception as e:
        response = jsonify(error=1, msg_en="Invalid data!")
        log.invalid_packets.logWrite("%s\n\n%s" % (e, request.data))
        return response,400
    else:
        if log.packets.enabled:
            log.packets.logWrite(json.dumps(request.json))

        pseudonym = mySQL.getPseudonym(authorization, packet.device)
        if pseudonym == None:
            if mySQL.checkToken(authorization):
                pseudonym = mySQL.createDevice(authorization, packet.device)
                if pseudonym == None: #Creation failed
                    log.general.logAppend("Creation of devEUI %s using authorization %s failed because of duplicate entry" % (packet.device.devEUI, authorization,))
                    response = jsonify(error=3, msg_en="Creation of device for your authorization failed")
                    return response,500
            else:
                log.general.logAppend("Authorization to webhook using %s failed" % (authorization,))
                response = jsonify(error=2, msg_en="Authorization failed")
                return response,403
        else:
            mySQL.updateDevice(authorization, packet.device)

        influx.addPacket(packet)
        response = jsonify(error=0, msg_en="Success!")
        return response

@TTNmonAPI.route("/v2/token", methods=['GET', 'POST'])
def createToken():
    token = mySQL.createToken()
    if token == None:
        response = jsonify(error=1,
                        msg_en="Token generation failed. Please retry later")
    else:
        response = jsonify(error=0,
                        msg_en="Your new token has been created",
                        auth_token=token)
    return response

@TTNmonAPI.route("/v2/device/<devPseudonym>", methods=['GET'])
def getDevice(devPseudonym):
    authorization = request.headers.get('Authorization')
    authorized = mySQL.checkToken(authorization)
    dev = device.device()
    try:
        dev.pseudonym = int(devPseudonym)
    except ValueError:
        response = jsonify(error=1,
                        msg_en="Invalid pseudonym provided"),400
    else:
        result = mySQL.getDevice(dev)
        if result:
            data = {
              "pseudonym": dev.pseudonym,
              "created": dev.created.strftime("%Y-%m-%d %H:%M:%S"),
              "lastSeen": dev.lastSeen.strftime("%Y-%m-%d %H:%M:%S"),
              "location": {
                "latitude": dev.location.latitude,
                "longitude": dev.location.longitude,
                "altitude": dev.location.altitude
              }
            }

            owner = authorization == dev.authorization
            if (authorized and owner):
                data["appID"] = dev.appID
                data["devID"] = dev.devID
                data["devEUI"] = dev.devEUI

            response = jsonify(error=0,
                msg_en="The device has been queried according to the supplied privileges",
                device=data,
                authorized=authorized,
                owner=owner)
        else:
            response = jsonify(error=1,
                msg_en="Device not found"),404
    return response

@TTNmonAPI.route("/v2/devices", methods=['GET'])
def getDevices():
    authorization = request.headers.get('Authorization')
    authorized = mySQL.checkToken(authorization)

    if not authorized:
        response = jsonify(error=1, msg_en="Invalid authorization token")
        return response,403
    else:
        response = {}
        response["error"] = 0
        response["msg_en"] = "The following devices are currently registered"
        response["devices"] = []

        devices = mySQL.getDevices(authorization)
        for device in devices:
            dev = {}
            dev["pseudonym"] = device.pseudonym
            dev["devEUI"] = device.devEUI
            dev["appID"] = device.appID
            dev["devID"] = device.devID
            dev["created"] = device.created.strftime("%Y-%m-%d %H:%M:%S")
            dev["lastSeen"] = device.lastSeen.strftime("%Y-%m-%d %H:%M:%S")
            dev["latitude"] = device.location.latitude
            dev["longitude"] = device.location.longitude
            dev["altitude"] = device.location.altitude
            response["devices"].append(dev)
        response = jsonify(response)
        return response

@TTNmonAPI.route("/v2/device/<devEUI>", methods=['DELETE'])
def deleteDevice(devEUI):
    authorization = request.headers.get('Authorization')
    dev = device.device()
    try:
        dev.devEUI = devEUI
    except ValueError:
        response = jsonify(error=1,
                        msg_en="Invalid devEUI provided"),400
    else:
        result = mySQL.removeDevice(authorization, dev)
        if result:
            response = jsonify(error=0,
                    msg_en="Device has been successfully removed")
        else:
            response = jsonify(error=2,
                    msg_en="Device not found"),404
    return response

@TTNmonAPI.route("/v2/metadata/device/<pseudonym>/packets/<dateFrom>/<dateTo>", methods=['GET'])
def getMetadataStats(devPseudonym, dateFrom, dateTo):
    dev = device.device() # Collect device information from DB
    try:
        dev.pseudonym = int(devPseudonym)
    except ValueError:
        response = jsonify(error=2,
                msg_en="Invalid devPseudonym. Please supply an int"), 400
        return response

    if not mySQL.getDevice(dev):
        response = jsonify(error=2,
                          msg_en="devPseudonym not found"), 404
        return response

    try: # Check and Prepare date
        dateFrom = dateutil.parser.parse(dateFrom)
        dateTo = dateutil.parser.parse(dateTo)
    except ValueError:
        response = jsonify(error=1,
                msg_en="Invalid date format"), 400
        return response

    pktData = influx.getPacketsMetadata(dev, start, end)
    #pkts = packets.packets(dev)
    #pkts.fromInflux()

    response = jsonify(error=0,
            msg_en="JustNothingYet")
    return response

@TTNmonAPI.route("/v2/metadata/device/<devPseudonym>/gateways/<dateFrom>/<dateTo>", methods=['GET'])
def getGatewayList(devPseudonym, dateFrom, dateTo):
    try:
        dateFrom = dateutil.parser.parse(dateFrom)
        dateTo = dateutil.parser.parse(dateTo)
    except ValueError:
        response = jsonify(error=1,
                msg_en="Invalid date format"),400
        return response

    dev = device.device()

    try:
        dev.pseudonym = int(devPseudonym)
    except ValueError:
        response = jsonify(error=2,
                msg_en="Invalid devPseudonym. Please supply an int"),400
        return response

    mySQL.getDevice(dev)
    influx.getGateways(dev, dateFrom, dateTo)
    response = jsonify(error=0,
            msg_en="JustNothingYet")
    return response
