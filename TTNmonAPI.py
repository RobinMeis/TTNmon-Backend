from flask import Flask, g, make_response, jsonify, request
from flask_cors import CORS
import configparser

from MySQL import MySQL
from Influx import Influx

import metadata.packet

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
    try:
        packet = metadata.packet.packet(request.json)
    except Exception, e:
        print(str(e))
        response = jsonify(error=1, msg_en="Invalid data!")
        return response
    else:
        response = jsonify(error=0, msg_en="Strange, you should never ever see this page. Did you try to send fake data? Well, it's your device!")
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
