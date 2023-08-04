import sys
import mysql.connector
import os
import json
import math

deploy_root_path = os.path.dirname(os.path.realpath(__file__))
config_file = deploy_root_path + '/../site/gossby.com/.python.json'

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

try:
    mycursor.execute("SELECT COUNT(queue_id) AS counter FROM osc_post_office_email_queue WHERE state = 'queue' AND priority = 10")
    row = mycursor.fetchone()    
except mysql.connector.Error as err:
    print("Something went wrong: {}".format(err))
    sys.exit(2)

if row is not None:
    email_per_process = 50
    total_process = int(math.ceil(float(row['counter']) / float(email_per_process)))

    for x in range(total_process):
        try:
            mycursor.execute("SELECT queue_id FROM osc_post_office_email_queue WHERE state = 'queue' AND priority = 10 ORDER BY queue_id ASC LIMIT %s,1", [x * email_per_process])
            row = mycursor.fetchone()
        except mysql.connector.Error as err:
            print(("Error: {}").format(err))
            sys.exit(2)

        if row is not None:
            os.system("python " + deploy_root_path + "/.mailer.py -i " + str(row['queue_id']) + " > /dev/null 2>&1 &")
            print("queue_id: " + str(row['queue_id']))      