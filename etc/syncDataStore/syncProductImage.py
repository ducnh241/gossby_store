import mysql.connector
import json
import sys, getopt
import pwd
import grp
import os
import uuid
import shutil
import re
from mysql.connector import Error


def main(argv):
    new_store_name = ""

    try:
        opts, args = getopt.getopt(argv, "domain:", ["domain="])
    except getopt.GetoptError:
        print('syncProductImage.py -d <domain>')
        sys.exit(2)

    for opt, arg in opts:
        if opt == '-e':
            print('syncProduct.py -d <domain>')
            sys.exit()
        elif opt in ("-d", "--domain"):
            new_store_name = arg

    if (new_store_name == ''):
        print('syncProduct.py -d <domain>')
        sys.exit(2)

    sync_folder_path = '/var/www/9prints/store/site'

    # folder code gossby
    sync_root_path = sync_folder_path + '/gossby.com'

    # folder storage gossby
    storage_path_current_store = sync_root_path + '/storage'

    # folder store seller
    root_path_new_store = sync_folder_path + '/' + new_store_name

    # folder storage store seller
    storage_path_new_store = root_path_new_store + '/storage'

    config_file = root_path_new_store + '/.python.json'

    if(os.path.exists(config_file)==False):
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
        mydb = mysql.connector.connect(host=config['db']['host'],port=config['db']['port'], user=config['db']['username'], passwd=config['db']['password'], database=config['db']['database'])
        mycursor = mydb.cursor(dictionary=True)
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    # copy image review
    mycursor.execute("SELECT filename FROM osc_catalog_product_review_image")
    rows = mycursor.fetchall()
    if len(rows) > 0:
        for row in rows:
            src_fpath = storage_path_current_store + '/' + row['filename']
            dest_fpath = storage_path_new_store + '/' + row['filename']

            if os.path.isfile(dest_fpath) == True or os.path.isfile(src_fpath) == False:
                continue
            try:
                shutil.copy(src_fpath, dest_fpath)
            except IOError as io_err:
                os.makedirs(os.path.dirname(dest_fpath), exist_ok=True)
                shutil.copy(src_fpath, dest_fpath)

    # copy image product
    mycursor.execute("SELECT product_id, filename FROM osc_catalog_product_image")
    rows = mycursor.fetchall()
    if len(rows) > 0:
        for row in rows:
            src_fpath = storage_path_current_store + '/' + row['filename']
            dest_fpath = storage_path_new_store + '/' + row['filename']

            if os.path.isfile(dest_fpath) == True or os.path.isfile(src_fpath) == False:
                continue

            try:
                shutil.copy(src_fpath, dest_fpath)
            except IOError as io_err:
                os.makedirs(os.path.dirname(dest_fpath), exist_ok=True)
                shutil.copy(src_fpath, dest_fpath)

if __name__ == "__main__":
    main(sys.argv[1:])
