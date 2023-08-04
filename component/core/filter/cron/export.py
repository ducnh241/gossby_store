import mysql.connector
import json
import sys, getopt
import pwd
import grp
import os
import re
import datetime

def main(argv):
    output_file = ''
    start_date = ''
    end_date = ''
    site_path = ''

    try:
        opts, args = getopt.getopt(argv, "hf:s:e:r:", ["file=", "start_date=", "end_date=", "path="])
    except getopt.GetoptError:
        print('export.py -f <file> -s <start_date> -e <end_date>')
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('export.py -f <file> -s <start_date> -e <end_date>')
            sys.exit()
        elif opt in ("-s", "--start_date"):
            start_date = arg
        elif opt in ("-e", "--end_date"):
            end_date = arg
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
        mydb = mysql.connector.connect(host=config['db']['host'], port=config['db']['port'],
                                             user=config['db']['username'], passwd=config['db']['password'],
                                             database=config['db']['database'])
        mycursor = mydb.cursor(dictionary=True)
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    try:
        uid = pwd.getpwnam("apache").pw_uid
        gid = grp.getgrnam("apache").gr_gid
    except:
        try:
            uid = pwd.getpwnam("www-data").pw_uid
            gid = grp.getgrnam("www-data").gr_gid
        except:
            uid = False
            gid = False

    offset = 0
    limit = 10000
    row_idx = 1
    list_search_keyword = dict()

    while True:
        try:
            query = "SELECT request FROM osc_tracking_footprint WHERE " \
                    "request LIKE '%/search?%keywords=%' " \
                    "AND added_timestamp >= {} " \
                    "AND added_timestamp <= {} " \
                    "ORDER BY footprint_id ASC LIMIT {}, {}".format(start_date, end_date, offset, limit)
            mycursor.execute(query)
            rows = mycursor.fetchall()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            sys.exit(2)

        row_count = len(rows)
        offset += row_count

        if row_count < 1:
            break

        for row in rows:
            try:
                m = re.search(r"keywords=([^\&]+)\&?", row['request'], flags=re.IGNORECASE)
                if m and m.group(1):
                    key = m.group(1).lower()
                    if key in list_search_keyword:
                        list_search_keyword[key] += 1
                    else:
                        list_search_keyword[key] = 1
            except Exception as err:
                continue

    if list_search_keyword:
        list_search_keyword = dict(sorted(list_search_keyword.items(), key=lambda x: x[1], reverse=True))
    print(json.dumps(list_search_keyword))

if __name__ == "__main__":
    main(sys.argv[1:])