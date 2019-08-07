import mysql.connector
from mysql.connector import pooling
import string
import random

class MySQL:
    def __init__(self, host, username, password, database, ca_cert, pool_name, pool_size):
        self.cnxpool = mysql.connector.pooling.MySQLConnectionPool(pool_name = pool_name,
                                                                    pool_size = int(pool_size),
                                                                    host = host,
                                                                    user = username,
                                                                    password = password,
                                                                    database = database,
                                                                  )

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
