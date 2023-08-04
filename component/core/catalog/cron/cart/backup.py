# -*- coding: utf-8 -*-
from time import sleep
import mysql.connector
import json
import sys
import getopt
import json
import datetime
import traceback


def main(argv):
    # 45 days ago
    end_timestamp = datetime.datetime.combine(
        datetime.date.today() - datetime.timedelta(days=45), datetime.time(23, 59, 59)).timestamp()
    print('end_timestamp: {}'.format(end_timestamp))
    print(datetime.datetime.now().timestamp())

    site_path = ''
    try:
        opts, args = getopt.getopt(argv, "r:", ["site_path="])
    except getopt.GetoptError:
        print('backup.py -r <site_path>')
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-r", "--site_path"):
            site_path = arg

    if (site_path == ''):
        print('backup.py -r <site_path>')
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
        mydb = mysql.connector.connect(host=config['db']['host'], port=config['db']['port'], user=config['db']['username'],
                                       passwd=config['db']['password'], database=config['db']['database'], auth_plugin='mysql_native_password')
        backupdb = mysql.connector.connect(host=config['db_bak']['host'], port=config['db_bak']['port'], user=config['db_bak']['username'],
                                           passwd=config['db_bak']['password'], database=config['db_bak']['database'], auth_plugin='mysql_native_password')
        mycursor = mydb.cursor(dictionary=True)
        mycursor.execute('set profiling = 1')
        backupcursor = backupdb.cursor()
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    limit = 1000
    count_cart_column = 0
    count_cart_item_column = 0
    cart_ids_updated = []

    while (True):
        try:
            try:
                # SELECT CART
                if len(cart_ids_updated) > 0:
                    mycursor.execute(
                        "SELECT * FROM osc_catalog_cart WHERE cart_id NOT IN ({}) AND modified_timestamp <= %s LIMIT %s".format(','.join(map(str, cart_ids_updated))), (end_timestamp, limit))
                else:
                    mycursor.execute(
                        "SELECT * FROM osc_catalog_cart WHERE modified_timestamp <= %s LIMIT %s", (end_timestamp, limit))
                print(mycursor.statement)
                rows = mycursor.fetchall()
            except Exception as err:
                print("Query SELECT CART error: {}".format(err))
                sys.exit(2)
            row_count = len(rows)
            print('row_count: {}'.format(row_count))

            if (row_count < 1):
                break

            for row in rows:
                if (count_cart_column == 0):
                    count_cart_column = len(row)

                # SELECT CART ITEM BY CART
                mycursor.execute(
                    "SELECT * FROM osc_catalog_cart_item WHERE cart_id = {}".format(row['cart_id']))
                cart_item_rows = mycursor.fetchall()

                cart_is_updated = False
                list_tuple_cart_item_rows = []
                for cart_item_row in cart_item_rows:
                    list_tuple_cart_item_rows.append(
                        tuple([cart_item_row[field] for field in cart_item_row]))
                    if (count_cart_item_column == 0):
                        count_cart_item_column = len(cart_item_row)
                    # CHECK CART ITEM IS UPDATED? AND IGNORE IF CART UPDATED
                    if (cart_item_row['modified_timestamp'] > end_timestamp):
                        print("CART IS UPDATED = {}".format(row['cart_id']))
                        cart_ids_updated.append(row['cart_id'])
                        cart_is_updated = True

                if cart_is_updated == True:
                    break

                # DELETE CART AND ITEM IN DB BACKUP
                backupcursor.execute(
                    "DELETE FROM osc_catalog_cart WHERE cart_id = %s", (row['cart_id'],))
                backupcursor.execute(
                    "DELETE FROM osc_catalog_cart_item WHERE cart_id = %s", (row['cart_id'],))

                # INSERT CART TO DB BACKUP
                list_of_cart_args = ["%s"] * count_cart_column
                backupcursor.execute("INSERT INTO osc_catalog_cart VALUES ("+" ,".join(
                    list_of_cart_args)+")", tuple([row[field] for field in row]))

                list_of_cart_item_args = ["%s"] * count_cart_item_column
                backupcursor.executemany(
                    "INSERT INTO osc_catalog_cart_item VALUES ("+" ,".join(
                        list_of_cart_item_args)+")", list_tuple_cart_item_rows)
                backupdb.commit()

                # VERIFY INSERT DB BACKUP SUCCESS
                is_success_insert = False
                backupcursor.execute(
                    "SELECT COUNT(*) FROM osc_catalog_cart WHERE cart_id = %s", (row['cart_id'],))
                count_cart_record = backupcursor.fetchone()[0]
                if count_cart_record > 0:
                    backupcursor.execute(
                        "SELECT COUNT(*) FROM osc_catalog_cart_item WHERE cart_id = %s", (row['cart_id'],))
                    count_cart_item_record = backupcursor.fetchone()[0]
                    if count_cart_item_record == len(cart_item_rows):
                        is_success_insert = True

                print("insert_cart: {}, count_cart_item_db_backup: {}, count_cart_item_db_live: {}".format(
                    row['cart_id'], count_cart_item_record, len(cart_item_rows)))

                # DELETE CART AND CART ITEM IF INSERT DB BACKUP SUCCESS
                if is_success_insert == True:
                    mycursor.execute(
                        "DELETE FROM osc_catalog_cart WHERE cart_id = %s", (row['cart_id'],))
                    mycursor.execute(
                        "DELETE FROM osc_catalog_cart_item WHERE cart_id = %s", (row['cart_id'],))
                    mydb.commit()
                    print("Rows Deleted = ", mycursor.rowcount)
        except Exception as error:
            print(traceback.format_exc())
            sys.exit(2)

    # closing database connection.
    if mydb.is_connected():
        mycursor.close()
        mydb.close()
    if backupdb.is_connected():
        backupcursor.close()
        backupdb.close()
    print(datetime.datetime.now().timestamp())
    print("backup cart and cart item successfully. Connection is closed")


if __name__ == "__main__":
    main(sys.argv[1:])
