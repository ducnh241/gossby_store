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
        opts, args = getopt.getopt(argv, "d:", ["domain="])
    except getopt.GetoptError:
        print('syncProduct.py -d <domain>')
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

    #copy image review
    mycursor.execute("SELECT product_id, meta_data FROM osc_catalog_product")
    rows = mycursor.fetchall()
    design_ids = []

    if len(rows) > 0:
        for row in rows:
            try:
                meta_data = json.loads(row['meta_data'])
            except:
                print("An exception occurred #product_id")
                continue

            if 'campaign_config' not in meta_data:
                print('Not have json product # ' + str(row['product_id']))
                continue

            if 'print_template_config' not in meta_data['campaign_config']:
                print('Not have print template config product # ' + str(row['product_id']))
                continue

            for data_design in meta_data['campaign_config']['print_template_config']:
                if 'segments' not in data_design:
                    print('Not have segments product # ' + str(row['product_id']))
                    continue
                for segment_key, data_segment in data_design['segments'].items():
                    if 'source' not in data_segment:
                        print('not have source  product # ' + str(row['product_id']))
                        continue
                    if 'design_id' in data_segment['source']:
                        design_ids.append(str(data_segment['source']['design_id']))

    design_ids = list(set(design_ids))
    designs = []
    for design in design_ids:
        designs.append(int(design))
    designs.sort()
    print(designs)
    print(len(design_ids))
if __name__ == "__main__":
    main(sys.argv[1:])
