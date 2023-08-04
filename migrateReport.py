import mysql.connector
import json
import sys, getopt
import pwd
import grp
import os
import copy
from getpass import getpass

def incrementProductRecord(dbcursor, report_key, product_id, report_value, referer_host, added_timestamp):
    try:
        dbcursor.execute("INSERT INTO osc_report_record_product (report_key, product_id, report_value, added_timestamp) VALUES(%s, %s, %s, %s) ON DUPLICATE KEY UPDATE report_value=(report_value + %s)", [report_key, product_id, report_value, added_timestamp, report_value])
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))

    try:
        dbcursor.execute("INSERT INTO osc_report_record_product_referer (report_key, product_id, referer, report_value, added_timestamp) VALUES(%s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE report_value=(report_value + %s)", [report_key, product_id, referer_host, report_value, added_timestamp, report_value])
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))

def increment(dbcursor, report_key, report_value, referer_host, added_timestamp):
    try:
        dbcursor.execute("INSERT INTO osc_report_record_new (report_key, report_value, added_timestamp) VALUES(%s, %s, %s) ON DUPLICATE KEY UPDATE report_value=(report_value + %s)", [report_key, report_value, added_timestamp, report_value])
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))

    try:
        dbcursor.execute("INSERT INTO osc_report_record_new_referer (report_key, referer, report_value, added_timestamp) VALUES(%s, %s, %s, %s) ON DUPLICATE KEY UPDATE report_value=(report_value + %s)", [report_key, referer_host, report_value, added_timestamp, report_value])
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))


def main(argv):
    dbname = ''
    dbhost = 'localhost'
    dbuser = ''
    dbport = '3306'

    try:
        opts, args = getopt.getopt(argv, "hi:h:p:u:",['dbhost=','dbport=','dbuser=','dbname='])
    except getopt.GetoptError as err:
        print("Something went wrong: {}".format(err))
        print('migrateReport.py -h <mysql_server> -p <mysql_port> -u <mysql_user> -i <database_name>')
        sys.exit(2)

    for opt, arg in opts:
        if opt == '-e':
            print('migrateReport.py -h <mysql_server> -p <mysql_port> -u <mysql_user> -i <database_name>')
            sys.exit()
        elif opt in ("-i",'--dbname'):
            dbname = arg
        elif opt in ("-h",'--dbhost'):
            dbhost = arg
        elif opt in ("-p",'--dbport'):
            dbport = arg
        elif opt in ("-u",'--dbuser'):
            dbuser = arg

    if dbname == '' or dbhost == '' or dbuser == '' or dbport == '':
        print('migrateReport.py -h <mysql_server> -p <mysql_port> -u <mysql_user> -i <database_name>')
        sys.exit(2)

    dbpass = getpass("Enter mysql password: ")

    try:
        mydb = mysql.connector.connect(host=dbhost, port=dbport, user=dbuser, passwd=dbpass, database=dbname)
        mycursor = mydb.cursor(dictionary=True)
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    last_record_id = 0

    while True:
        try:
            mycursor.execute("SELECT * FROM osc_report_record WHERE record_id > %s ORDER BY record_id ASC LIMIT 1000", [last_record_id])
            rows = mycursor.fetchall()

            for row in rows:
                last_record_id = row['record_id']

                if row['referer_host'] == '':
                    row['referer_host'] = 'direct'

                row['added_timestamp'] = int(row['added_timestamp'])
                row['added_timestamp'] = row['added_timestamp'] - (row['added_timestamp'] % (60 * 15))

                if row['report_key'] in ('catalog/item/view','catalog/item/visit','catalog/item/unique_visitor'):
                    incrementProductRecord(mycursor, row['report_key'], row['extra_key_1'], row['report_value'], row['referer_host'], row['added_timestamp'])
                elif row['report_key'] in ('catalog/add_to_cart','catalog/checkout_initialize'):
                    incrementProductRecord(mycursor, row['report_key'], 0, row['report_value'], row['referer_host'], row['added_timestamp'])
                elif row['report_key'] in ('unique_visitor','visit','new_visitor','returning_visitor','pageview'):
                    increment(mycursor, row['report_key'], row['report_value'], row['referer_host'], row['added_timestamp'])

            mycursor.execute("DELETE FROM osc_report_record WHERE record_id <= %s", [last_record_id])
            mydb.commit()

            if len(rows) < 1000:
                break
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            sys.exit(2)

if __name__ == "__main__":
    main(sys.argv[1:])
