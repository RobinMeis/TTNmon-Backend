from flask import Flask, g, make_response
import configparser

from MySQL import MySQL
from Influx import Influx

config = configparser.ConfigParser() #Load config
config.read('TTNmon.conf')

conf = config["MySQL"]
mySQL = MySQL(
  config["MySQL"]["host"],
  config["MySQL"]["username"],
  config["MySQL"]["password"],
  config["MySQL"]["ca_cert"],
  config["MySQL"]["pool_name"],
  config["MySQL"]["pool_size"]
)

influx = Influx(
  config["Influx"]["host"],
  config["Influx"]["username"],
  config["Influx"]["password"],
  config["Influx"]["ca_cert"]
)

TTNmonAPI = Flask(__name__) #Initialize App
with TTNmonAPI.app_context(): #Setup App
    frontend_url = config["General"]["frontend_url"]

    print(config.sections())


@TTNmonAPI.route("/")
def hello():
    response = "This is the TTNmon API. The frontend is located <a href=\"%s\">here</a>" % (frontend_url,)
    return make_response(response, 200)
