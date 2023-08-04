import mysql.connector
import json
import sys
import os
from datetime import datetime
import time

# multi thread
import threading

# facebook_business SDK
from facebook_business.adobjects.serverside.action_source import ActionSource
from facebook_business.adobjects.serverside.content import Content
from facebook_business.adobjects.serverside.custom_data import CustomData
from facebook_business.adobjects.serverside.delivery_category import DeliveryCategory
from facebook_business.adobjects.serverside.event import Event
from facebook_business.adobjects.serverside.event_request import EventRequest
from facebook_business.adobjects.serverside.user_data import UserData
from facebook_business.api import FacebookAdsApi


class executeSetEvent (threading.Thread):
    def __init__(self, threadID, name, mod, max_thread):
        threading.Thread.__init__(self)
        self.threadID = threadID
        self.name = name
        self.mod = mod
        self.max_thread = max_thread

    def run(self):

        print("Start thread " + self.name)
        osc_site_path = ''
        if "--osc_site_path" in  sys.argv:
            osc_site_path = sys.argv[sys.argv.index("--osc_site_path") + 1]

        config_file = osc_site_path + '/.python.json'

        if os.path.exists(config_file) == False:
            print("Error: Python config file is not exist")
            sys.exit(2)

        try:
            f = open(config_file, 'r')
            config = json.loads(f.read())
            f.close()
        except:
            print("Error: Python config file not accessible")
            sys.exit(2)

        try:
            mydb = mysql.connector.connect(host = config['db']['host'], port = config['db']['port'], user = config['db']['username'], passwd = config['db']['password'], database = config['db']['database'])
            mycursor = mydb.cursor(dictionary = True)
        except mysql.connector.Error as err:
            print("Error: Something went wrong: {}".format(err))
            sys.exit(2)

        time = int(datetime.now().strftime('%s'))

        mycursor.execute(
            "SELECT setting_key, setting_value FROM osc_core_setting WHERE setting_key IN ('tracking/facebook_pixel_api/enable', 'tracking/facebook_pixel_api/access_token')")
        config_facebook_api = mycursor.fetchall()

        enable_facebook_pixel_api = False
        access_token = ''

        for cf_facebook_api in config_facebook_api:
            if cf_facebook_api['setting_key'] == 'tracking/facebook_pixel_api/enable' and cf_facebook_api['setting_value'] == '1':
                enable_facebook_pixel_api = True
            if cf_facebook_api['setting_key'] == 'tracking/facebook_pixel_api/access_token' and cf_facebook_api['setting_value'] != '':
                access_token = cf_facebook_api['setting_value'].strip('"')

        if not access_token or not enable_facebook_pixel_api:
            print('Error: Not enable or token invalid')
            sys.exit(2)

        try:
            mycursor.execute(
                "SELECT * FROM osc_facebook_api_queue WHERE queue_flag = 0 AND mod(queue_id, %s) = %s ORDER BY queue_id ASC LIMIT 500", [self.max_thread, self.mod])

            rows = mycursor.fetchall()

            if len(rows) < 1:
                print('no record to handle')
                sys.exit(2)

            execute_success = []
            execute_error = []
            for row in rows:
                print("Handle queue_id: %d" % row['queue_id'])
                try:
                    pixel_ids = json.loads(row['pixel_ids'])
                    data_events = json.loads(row['data_events'])
                    for even_key, data_event in data_events['events'].items():
                        FacebookAdsApi.init(access_token = access_token)

                        if even_key == 'ViewContent':
                            event_name = 'ViewContent'
                            event_id = data_event['eventID']
                            content = Content(
                                product_id = data_event['content_ids'],
                                quantity = 1,
                                item_price = data_event['value'],
                                title = data_event['content_name'],
                                delivery_category = DeliveryCategory.HOME_DELIVERY,
                            )

                            custom_data = CustomData(
                                contents = [content],
                                value = data_event['value'],
                                currency = data_event['currency'],
                                content_name = data_event['content_name'],
                                content_ids = data_event['content_ids'],
                                content_type = data_event['content_type'],
                                num_items = 1,
                                delivery_category = DeliveryCategory.HOME_DELIVERY,
                            )
                        elif even_key == 'AddToCart':
                            event_name = 'AddToCart'
                            event_id = data_event['eventID']

                            custom_data = CustomData(
                                value = data_event['value'],
                                currency = data_event['currency'],
                                content_name = data_event['content_name'],
                                content_ids = data_event['content_ids'],
                                content_type = data_event['content_type'],
                                delivery_category = DeliveryCategory.HOME_DELIVERY,
                            )
                        elif even_key == 'InitiateCheckout':
                            event_name = 'InitiateCheckout'
                            event_id = data_event['eventID']

                            custom_data = CustomData(
                                value = data_event['value'],
                                currency = data_event['currency'],
                                content_name = data_event['content_name'],
                                content_ids = data_event['content_ids'],
                                content_type = data_event['content_type'],
                                num_items = data_event['num_items'],
                                delivery_category=DeliveryCategory.HOME_DELIVERY,
                            )
                        elif even_key == 'Purchase':
                            event_name = 'Purchase'
                            event_id = data_event['eventID']

                            custom_data = CustomData(
                                value = data_event['value'],
                                currency = data_event['currency'],
                                content_name = data_event['content_name'],
                                content_ids = data_event['content_ids'],
                                content_type = data_event['content_type'],
                                order_id = data_event['order_id'],
                                num_items = data_event['num_items'],
                                delivery_category = DeliveryCategory.HOME_DELIVERY,
                            )
                        else:
                            event_name = 'PageView'
                            event_id = int(row['added_timestamp'])
                            custom_data = CustomData()

                        user_data = UserData(
                            emails = [data_events['user_data']['email']],
                            phones = [data_events['user_data']['phone']],
                            first_names = [data_events['user_data']['first_name']],
                            last_names = [data_events['user_data']['last_name']],
                            cities = [data_events['client_info']['city']],
                            country_codes = [data_events['client_info']['country_code']],
                            zip_codes = [data_events['user_data']['zip_code']],
                            client_ip_address = data_events['_SERVER']['REMOTE_ADDR'],
                            client_user_agent = data_events['_SERVER']['HTTP_USER_AGENT'],
                            fbc = data_events['fb_click_id'],
                            fbp = data_events['browser_id'],
                        )

                        event = Event(
                            event_name = event_name,
                            event_time = int(row['added_timestamp']),
                            user_data = user_data,
                            custom_data = custom_data,
                            event_source_url = data_events['source_url'],
                            action_source = ActionSource.WEBSITE,
                            event_id = event_id,
                        )

                        events = [event]
                        for pixel_id in pixel_ids:
                            event_request = EventRequest(
                                events = events,
                                pixel_id = pixel_id,
                            )
                            event_response = event_request.execute()
                            print(event_response)
                    execute_success.append(row['queue_id'])

                except Exception as ex:
                    # using , if using python 2.x
                    mycursor.execute("UPDATE osc_facebook_api_queue SET queue_flag = 2, error_message = %s WHERE queue_id = %s", [str(ex), row['queue_id']])
                    mydb.commit()

            if len(execute_success):
                mycursor.execute("DELETE FROM osc_facebook_api_queue WHERE queue_id IN (" + (", ".join(map(str, execute_success))) + ")")
                mydb.commit()
        except mysql.connector.Error as err:
            print("Error: Something went wrong: {}".format(err))
            sys.exit(2)



# Create new threads
for i in range(1, 20):
    thread1 = executeSetEvent(i, "Thread-" + str(i), i - 1, 20).start()
print("Exiting All Thread")
