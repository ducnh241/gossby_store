# -*- coding: utf-8 -*-
import mysql.connector
import json
import sys, getopt
import pwd
import grp
import os
import re
import json
import time

def insertLog(mydb, title, content):
    try:
        mycursor = mydb.cursor(dictionary=True)
        query = "INSERT INTO osc_convert_log (`title`, `content`, `added_timestamp`) VALUES ('{}', '{}', {})".format(title, content, int(time.time()))
        mycursor.execute(query)
        mydb.commit()
    except Exception as err:
        print("Insert into osc_convert_log error {}".format(err))

def main(argv):
    site_key = ''
    site_path = ''
    list_order = ''
    is_resync = 0

    try:
        opts, args = getopt.getopt(argv, "i:r:l:u:", ["key=", "path="])
    except getopt.GetoptError:
        print('convertProduct.py -i <site_key> -r <site_path>')
        sys.exit(2)

    for opt, arg in opts:
        if opt == '-e':
            print('convertProduct.py -i <site_key> -r <site_path>')
            sys.exit()
        elif opt in ("-i", "--key"):
            site_key = arg
        elif opt in ("-r", "--path"):
            site_path = arg

    if (site_path == ''):
        print('convertProduct.py -i <site_key> -r <site_path> -l <list_order> -u <is_resync>')
        sys.exit(2)

    config_file = site_path + '/.python.json'

    try:
        f = open(config_file, 'r')
        config = json.loads(f.read())
        f.close()
    except:
        print("Python config file not accessible")
        sys.exit(2)

    try:
        mydb = mysql.connector.connect(host=config['db']['host'], port=config['db']['port'], user=config['db']['username'], passwd=config['db']['password'], database=config['db']['database'], auth_plugin='mysql_native_password')
        mycursor = mydb.cursor(dictionary=True)
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    offset = 0
    limit = 100
    while(True):
        try:
            query = ""
            query = "SELECT `product_id`, `meta_data` FROM osc_catalog_product WHERE ORDER BY product_id ASC LIMIT {},{}".format(offset, limit)

            mycursor.execute(query)
            rows = mycursor.fetchall()
        except Exception as err:
            insertLog(mydb, "Query product error", "{}".format(err))
            print("Query product error: {}".format(err))
            sys.exit(2)

        row_count = len(rows)

        if (row_count < 1):
            sys.exit(2)

        offset += row_count

        for row in rows:


if __name__ == "__main__":
    main(sys.argv[1:])