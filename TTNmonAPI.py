from flask import Flask, g, make_response, jsonify, request
from flask_cors import CORS
import configparser
import json

from MySQL import MySQL
from Influx import Influx

import metadata.packet
import log

config = configparser.ConfigParser() #Load config
config.read('TTNmon.conf')

influx = Influx(
  config["Influx"]["host"],
  config["Influx"]["username"],
  config["Influx"]["password"],
  config["Influx"]["ca_cert"]
)

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

@TTNmonAPI.route("/")
def hello():
    response = "This is the TTNmon API. The frontend is located <a href=\"%s\">here</a>" % (frontend_url,)
    return make_response(response, 200)

@TTNmonAPI.route("/webhook", methods=['POST'])
def webhook():
    authorization = request.headers.get('Authorization')
    packet = metadata.packet.packet()
    try:
        packet.fromTTN(request.json)
    except Exception as e:
        response = jsonify(error=1, msg_en="Invalid data!")
        log.invalid_packets.logWrite("%s\n\n%s" % (e, json.dumps(request.json)))
        return response,400
    else:
        response = jsonify(error=0, msg_en="Strange, you should never ever see this page. Did you try to send fake data? Well, it's your device!")
        if log.packets.enabled:
            log.packets.logWrite(json.dumps(request.json))

        pseudonym = mySQL.getPseudonym(authorization, packet.device)
        if pseudonym == None:
            if mySQL.checkToken(authorization):
                print("create device")
                pseudonym = mySQL.createDevice(authorization, packet.device)
                if pseudonym == None: #Creation failed
                    log.general.logAppend("Creation of devEUI %s using authorization %s failed because of duplicate entry" % (packet.device.devEUI, authorization,))
                    response = jsonify(error=3, msg_en="Creation of device for your authorization failed")
                    return response,500
            else:
                log.general.logAppend("Authorization to webhook using %s failed" % (authorization,))
                response = jsonify(error=2, msg_en="Authorization failed")
                return response,403

        print("ToDo: Do something with influx, this is pseudonym" + str(pseudonym))
        return response

@TTNmonAPI.route("/api/token", methods=['GET', 'POST'])
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
