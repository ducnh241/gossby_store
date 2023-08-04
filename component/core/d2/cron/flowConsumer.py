import pymysql
import os
import sys, getopt
import requests
import json
import string
import random
import time
from datetime import datetime, timezone
from kafka import KafkaConsumer

def sendTelegramMessage(message):
    CONFIG = {
        'telegram': {
            'bot_token': '1151351654:AAGiQushIasj2ZlDRmqB42Mv8IFEfvhkbDc',
            'chat_id': '-409036884'
        }
    }
    return requests.get('https://api.telegram.org/bot' + CONFIG['telegram']['bot_token'] + '/sendMessage?parse_mode=html&chat_id=' + CONFIG['telegram']['chat_id'] + '&text=' + message).json()

def ukey_generator(size=6, chars=string.ascii_uppercase + string.digits):
    return ''.join(random.choice(chars) for _ in range(size))

def main(argv):
    site_path = ''

    try:
        opts, args = getopt.getopt(argv, "hr:", ["path="])
    except getopt.GetoptError:
        print('consumerD2Flow.py -r <path>')
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('consumerD2Flow.py -r <path>')
            sys.exit()
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
    except:
        print("Python config file not accessible")
        sys.exit(2)

    try:
        host = config['db']['host']
        port = config['db']['port']
        username = config['db']['username']
        password = config['db']['password']
        database = config['db']['database']

        connection = pymysql.connect(user=config['db']['username'],
                                     password=config['db']['password'],
                                     host=config['db']['host'],
                                     port=int(config['db']['port']),
                                     database=config['db']['database'])
        cursor = connection.cursor()
    except Exception as err:
        print("Connect to mysql fail: {}".format(err))
        sys.exit(2)

    try:
        consumer = KafkaConsumer(config['kafka_d2_reply']['topic'],
                                 bootstrap_servers=[config['kafka_d2_reply']['host'] + ':' + config['kafka_d2_reply']['port']],
                                 group_id=config['kafka_d2_reply']['group'],
                                 auto_commit_interval_ms=30 * 1000,
                                 auto_offset_reset='smallest')

        for msg in consumer:
            queue_data = ''
            try:
                ukey = 'd2FlowReply/' + ukey_generator()
                action = 'd2FlowReply'
                queue_data = msg.value.decode("utf-8")

                query = "INSERT INTO osc_catalog_product_bulk_queue (`ukey`, `member_id`, `action`, `queue_data`, `queue_flag`, `added_timestamp`, `modified_timestamp`) VALUES (%s, %s, %s, %s, %s, %s, %s)"
                cursor.execute(query, (ukey, 1, action, queue_data, 1, int(time.time()), int(time.time())))
                connection.commit()
            except Exception as err:
                message = "{}".format(err)
                sendTelegramMessage(' '.join([err, queue_data]))
                print(' '.join([message, host, port, username, password, database, str(datetime.now(timezone.utc))]))
    except Exception as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

if __name__ == "__main__":
    main(sys.argv[1:])