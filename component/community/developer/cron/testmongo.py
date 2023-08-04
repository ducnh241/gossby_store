import os
import json
import datetime
import sys, getopt
import pymongo

mongo_client = None
def _getMongo(config):
    global mongo_client
    if mongo_client is None:
        try:
            host = config['mongodb']['host']
            port = config['mongodb']['port']
            username = config['mongodb']['username']
            password = config['mongodb']['password']
            dbname = config['mongodb']['dbname']
            tls_enable = config['mongodb']['tls_enable']
            tls_dir = config['mongodb']['tls_dir']
            auth_dbname = config['mongodb']['auth_dbname']
            env = config['mongodb']['env']

            auth = ''
            if username and password:
                auth = f"{username}:{password}@"

            auth_db = auth_dbname if auth_dbname != '' else dbname

            option = []

            if auth_db != '':
                option.append(f"authSource={auth_db}")

            if env != '' and env == 'production':
                option.append('retryWrites=false')

            if tls_enable and tls_enable == True and tls_dir:
                option.append(f"tls=true&tlsCAFile={tls_dir}")

            uri = f"mongodb://{auth}{host}:{port}/" + ("?" + "&".join(option) if option else "")

            mongo_client = pymongo.MongoClient(uri)
        except Exception as err:
            print("Connect to mongo fail: {}".format(err))
            sys.exit(2)

    return mongo_client

def main(argv):
    key = ''
    site_path = ''

    try:
        opts, args = getopt.getopt(argv, "hk:r:", ["key=", "path="])
    except getopt.GetoptError as err:
        print('getopt ' + str(err))
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('test.py -d <data> -k <key> -r <path>')
            sys.exit()
        elif opt in ("-k", "-key"):
            key = arg
        elif opt in ("-r", "--path"):
            site_path = arg

    config_file = site_path + '/.python.json'

    if (os.path.exists(config_file) == False):
        print("Python config file is not exist")
        sys.exit(2)

    try:
        f = open(config_file, 'r')
        config = json.loads(f.read())
        f.close()
    except Exception as err:
        print("Python config file not accessible: {}".format(err))
        sys.exit(2)

    client = _getMongo(config)
    db = client[config['mongodb']['dbname']]
    mongo_test_connection = db["mongo_test_connection"]

    if key and int(key) == 1:
        now = datetime.datetime.now()
        mongo_test_connection.insert_one({
            "message": "Test",
            "added_timestamp": int(now.timestamp()),
            "created_at": now.strftime("%d %B %Y %H:%M:%S")
        })

    for x in mongo_test_connection.find():
        print(x)

if __name__ == "__main__":
    main(sys.argv[1:])