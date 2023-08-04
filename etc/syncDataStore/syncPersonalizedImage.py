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

def copy(src_fpath, dest_fpath):
    if os.path.isfile(dest_fpath) == True or os.path.isfile(src_fpath) == False:
        return
    try:
        shutil.copy(src_fpath, dest_fpath)
    except IOError as io_err:
        os.makedirs(os.path.dirname(dest_fpath), exist_ok=True)
        shutil.copy(src_fpath, dest_fpath)

def main(argv):
    new_store_name = ""

    try:
        opts, args = getopt.getopt(argv, "d:", ["domain="])
    except getopt.GetoptError:
        print('syncPersonalized.py -d <domain>')
        sys.exit(2)

    for opt, arg in opts:
        if opt == '-e':
            print('syncPersonalized.py -d <domain>')
            sys.exit()
        elif opt in ("-d", "--domain"):
            new_store_name = arg

    if (new_store_name == ''):
        print('syncPersonalized.py -d <domain>')
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

    # copy image personalized design
    mycursor.execute("SELECT design_id, design_data FROM osc_personalized_design")
    rows = mycursor.fetchall()

    if len(rows) > 0:
        for row in rows:
            matches = re.findall(new_store_name + '(.*?)"', row['design_data'], re.DOTALL)

            for match in matches:
                match = match.replace("\/", "/")

                src_fpath = sync_root_path + '/' + match
                dest_fpath = root_path_new_store + '/' + match
                copy(src_fpath, dest_fpath)

                thumb  = "{0}.{2}.{1}".format(*match.rsplit('.', 1) + ['thumb'])
                src_fpath_thumb = src_fpath = sync_root_path + '/' + thumb
                dest_fpath_thumb = src_fpath = sync_root_path + '/' + thumb
                copy(src_fpath_thumb, dest_fpath_thumb)

                preview  = "{0}.{2}.{1}".format(*match.rsplit('.', 1) + ['preview'])
                src_fpath_preview = src_fpath = sync_root_path + '/' + preview
                dest_fpath_preview = src_fpath = sync_root_path + '/' + preview
                copy(src_fpath_preview, dest_fpath_preview)

if __name__ == "__main__":
    main(sys.argv[1:])
