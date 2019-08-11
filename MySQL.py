import mysql.connector
from mysql.connector import pooling, ClientFlag
import string
import random

class MySQL:
    def __init__(self, host, username, password, database, pool_reset_session, ssl_verify_cert, ssl_verify_identity, ssl_disabled, ca_cert, pool_name, pool_size):
        self.cnxpool = mysql.connector.pooling.MySQLConnectionPool(pool_name = pool_name,
                                                                    pool_size = int(pool_size),
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
