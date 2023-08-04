import os
import sys, getopt
import json
import datetime
import pymongo
from kafka import KafkaProducer

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
    decode_data = None
    data = ''
    key = ''
    site_path = ''

    try:
        opts, args = getopt.getopt(argv, "hd:k:r:", ["data=", "key=", "path="])
    except getopt.GetoptError as err:
        print('getopt ' + str(err))
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('producerD2Flow.py -d <data> -k <key> -r <path>')
            sys.exit()
        elif opt in ("-d", "-data"):
            data = arg
        elif opt in ("-k", "-key"):
            key = arg
        elif opt in ("-r", "--path"):
            site_path = arg

    if data == '' or key == '':
        print('Data or key null')
        sys.exit(2)

    config_file = site_path + '/.python.json'

    if (os.path.exists(config_file) == False):
        print("Python config file is not exist")
        sys.exit(2)

    try:
        f = open(config_file, 'r')
        config = json.loads(f.read())
        f.close()
    except:
        print("Python config file not accessible")
        sys.exit(2)

    try:
        decode_data = json.loads(data)
    except Exception as err:
        print("Decode data wrong: {}".format(err))
        sys.exit(2)

    client = _getMongo(config)
    db = client[config['mongodb']['dbname']]
    d2_flow_mongo = db['d2_flow']
    now = datetime.datetime.now()

    producer = KafkaProducer(bootstrap_servers=[config['kafka_d2']['host'] + ':' + config['kafka_d2']['port']],
                             request_timeout_ms=1000000,
                             api_version_auto_timeout_ms=1000000,
                             acks=1)

    try:
        producer.send(config['kafka_d2']['topic'], value=bytes(data, 'utf-8'))
        producer.flush()
        producer.close()

        if decode_data is not None and 'orderId' in decode_data and 'orderItemId' in decode_data:
            d2_flow_mongo.insert_one({
                'order_id': decode_data['orderId'],
                'order_item_id': decode_data['orderItemId'],
                'queue_data': data,
                'message': 'Producer send data successfully',
                'added_timestamp': int(now.timestamp()),
                'created_at': now.strftime("%d %B %Y %H:%M:%S")
            })
    except Exception as err:
        message = "Something went wrong: {}".format(err)
        if decode_data is not None and 'orderId' in decode_data and 'orderItemId' in decode_data:
            d2_flow_mongo.insert_one({
                'order_id': decode_data['orderId'],
                'order_item_id': decode_data['orderItemId'],
                'queue_data': data,
                'message': message,
                'added_timestamp': int(now.timestamp()),
                'created_at': now.strftime("%d %B %Y %H:%M:%S")
            })

        producer.close()
        print(message)
        sys.exit(2)

if __name__ == "__main__":
    main(sys.argv[1:])