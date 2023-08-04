import sys
import smtplib
import mysql.connector
from mysql.connector import Error
import json
import os
from datetime import datetime
import time
import re

def percent(part, whole):
    return "{:.1%}".format(float(part)/float(whole))

item_ids = []
order_ids = []
map_item_orders = {}
map_tracking_key_orders = {}
tracking_keys = []
list_another_product_in_order = []
list_another_variant_in_order = []
count_items_in_order = {}
try:

    product_id = ""
    shop_id = ""
    folder = ""
    domain = ""

    if("--product_id" in  sys.argv):
        product_id = sys.argv[sys.argv.index("--product_id") + 1]


    if("--shop_id" in  sys.argv):
        shop_id = sys.argv[sys.argv.index("--shop_id") + 1]

    if("--domain" in  sys.argv):
        domain = sys.argv[sys.argv.index("--domain") + 1]

    if("--folder" in  sys.argv):
        folder = sys.argv[sys.argv.index("--folder") + 1]

    if (product_id == "" or shop_id == "" or domain == "" or folder == ""):
        raise Exception("Not have product_id, shop_id, domain, folder to get data python")

    PIDS = os.popen("pgrep -f '" + __file__ +" --product_id "+ product_id + "'").read().split("\n")

    CUR_PID = int(os.getpid())

    for PID in PIDS:
        PID = int(PID.strip()) if PID != '' else 0

        if PID > 0 and PID != CUR_PID:
            raise Exception("Export data " + str(product_id) + " is running")

    config_file = folder + '/.python.json'

    if(os.path.exists(config_file)==False):
        raise Exception("Python config file is not exist")

    try:
        f = open(config_file, 'r')
        config = json.loads(f.read())
        f.close()
    except:
        raise Exception("Python config file not accessible")

    try:
        mydb = mysql.connector.connect(host=config['db']['host'],port=config['db']['port'], user=config['db']['username'], passwd=config['db']['password'], database=config['db']['database'])
        mycursor_store = mydb.cursor(dictionary=True)
    except mysql.connector.Error as err:
        raise Exception("Something went wrong: {}".format(err))

    try:
        mydb_master = mysql.connector.connect(host=config['db_master']['host'],port=config['db_master']['port'], user=config['db_master']['username'], passwd=config['db_master']['password'], database=config['db_master']['database'])
        mycursor_master = mydb_master.cursor(dictionary=True)
    except mysql.connector.Error as err:
        raise Exception("Something went wrong: {}".format(err))

    mycursor_store.execute("SELECT product_id, sku, added_timestamp FROM osc_catalog_product where product_id = %s;", [str(product_id)])

    row = mycursor_store.fetchone()

    if row is None:
        raise Exception("Not have product #" + str(product_id))

    product_sku = str(row['sku'])
    product_added_timestamp = str(row['added_timestamp'])

    list_first_trackings = []
    offset = 0
    limit = 10000
    while True:
        """ lấy tất cả danh sách tracking first có order của product """
        mycursor_store.execute("SELECT footprint_id,track_ukey FROM osc_tracking_footprint where request like '%"+ product_sku +"%' and referer not like '%"+ domain +"%' and added_timestamp >= "+ str(product_added_timestamp) +" group by track_ukey order by footprint_id LIMIT %s,%s", [offset,limit])

        first_trackings = mycursor_store.fetchall()

        offset += len(first_trackings)

        if len(first_trackings) < 1:
            break

        for first_tracking in first_trackings:
            list_first_trackings = first_tracking['track_ukey']

    if len(list_first_trackings) < 1:
        raise Exception("Not have first tracking of product #" + str(product_id))

    """ Check list have order """
    offset = 0
    list_order_ids = []
    while True:
        mycursor_master.execute("SELECT master_record_id FROM osc_catalog_order where added_timestamp >= "+ str(product_added_timestamp) +" and client_info like '%" + "%".join(tracking_keys) + "%' LIMIT %s,%s", [offset,limit])

        orders = mycursor_master.fetchall()

        offset += len(orders)

        if len(orders) < 1:
            break

        for order in orders:
            list_order_ids.append(order['master_record_id'])

    if len(list_order_ids) < 1:
        raise Exception("Not have product of product #" + str(product_id))


    str_order_ids = ','.join('%s' %id for id in list_order_ids)
    """ Check list have order """
    mycursor_master.execute("SELECT master_record_id, order_master_record_id, variant_id, product_id FROM osc_catalog_order_item where order_master_record_id in ("+ str_order_ids +")")

    order_items = mycursor_master.fetchall()

    list_order_first_trackings = []
    list_orders = []
    just_buy_A = []
    just_buy_B = []
    buy_A_and_another_product = []
    buy_A_and_another_variant = []
    list_order_item_products = {}
    list_order_item_orders = {}

    for item in order_items:
        list_order_first_trackings.append(str(item['order_master_record_id']))
        if item['order_master_record_id'] in list_orders:
            if str(item['product_id']) != str(product_id):
                """ tồn tại trong order có item có product khác """
                buy_A_and_another_product.append(str(item['order_master_record_id']))
            else:
                """ tồn tại trong order có item có cùng product nhưng khác variant """
                buy_A_and_another_variant.append(str(item['order_master_record_id']))

            """ loại khỏi danh sách order có 1 item """
            list_orders.remove(str(item['order_master_record_id']))
            continue

        list_orders.append(str(item['order_master_record_id']))
        list_order_item_products.update({str(item['master_record_id']) : str(item['product_id'])})
        list_order_item_orders.update({str(item['master_record_id']) : str(item['order_master_record_id'])})

    for item_id, order_id in list_order_item_orders.items():
        if order_id not in list_orders:
            continue
        if str(list_order_item_products[str(item_id)]) == str(product_id):
            just_buy_A.append(str(order_id))
        else:
            just_buy_B.append(str(order_id))

    list_order_first_trackings = list(set(list_order_first_trackings))
    buy_A_and_another_product = list(set(buy_A_and_another_product))
    buy_A_and_another_variant = list(set(buy_A_and_another_variant))
    just_buy_A = list(set(just_buy_A))
    just_buy_B = list(set(just_buy_B))

    data_message = []
    data_message.append("product_id:#" + str(product_id))
    data_message.append("total_order_first_trackings:#" + str(len(list_order_first_trackings)))
    data_message.append("just_buy_A:#" + str(len(just_buy_A)))
    data_message.append("just_buy_A_percent:#" + percent(int(len(just_buy_A)), int(len(list_order_first_trackings))))
    data_message.append("buy_A_and_another_product:#" + str(len(buy_A_and_another_product)))
    data_message.append("buy_A_and_another_product_percent:#" + percent(int(len(buy_A_and_another_product)), int(len(list_order_first_trackings))))
    data_message.append("buy_A_and_another_variant:#" + str(len(buy_A_and_another_variant)))
    data_message.append("buy_A_and_another_variant_percent:#" + percent(int(len(buy_A_and_another_variant)), int(len(list_order_first_trackings))))
    data_message.append("just_buy_B:#" + str(len(just_buy_B)))
    data_message.append("just_buy_B_percent: #" + percent(int(len(just_buy_B)), int(len(list_order_first_trackings))))

    print("\n".join(data_message))
except Exception as err:
    print("Something went wrong: {}".format(err))