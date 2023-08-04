import sys
from email import encoders
from email.mime.application import MIMEApplication
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import smtplib
import mysql.connector
import json
import os
from datetime import datetime
import time

folder = ""

if("--folder" in  sys.argv):
    folder = sys.argv[sys.argv.index("--folder") + 1]

if (folder == ""):
    print("Not have folder to get data python")
    sys.exit(2)

PIDS = os.popen("pgrep -f '" + __file__ +" --folder "+ folder + "'").read().split("\n")

CUR_PID = int(os.getpid())

for PID in PIDS:
    PID = int(PID.strip()) if PID != '' else 0

    if PID > 0 and PID != CUR_PID:
        print("The script is running by other process")
        sys.exit(2)

config_file = folder + '/.python.json'

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

server = smtplib.SMTP(config['email']['smtp_host'], config['email']['smtp_port'])
server.starttls()
server.login(config['email']['username'], config['email']['password'])
time = int(datetime.now().strftime('%s'))

email = ''
if("--email" in  sys.argv):
    email = sys.argv[sys.argv.index("--email") + 1]

while(True):
    if email != '':
        mycursor.execute("SELECT * FROM osc_post_office_email_queue WHERE email = '%s' state = 'queue' and running_timestamp <= %s ORDER BY priority DESC, queue_id ASC LIMIT 1", [email,str(time)])
    else:
        mycursor.execute("SELECT * FROM osc_post_office_email_queue WHERE state = 'queue' and running_timestamp <= %s ORDER BY priority DESC, queue_id ASC LIMIT 1", [str(time)])
    row = mycursor.fetchone()

    if row is None:
        print("No row found");
        break;

    try:
        mycursor.execute("UPDATE osc_post_office_email_queue SET state = 'sending' WHERE queue_id = %s AND state = 'queue'", [str(row['queue_id'])])
        mydb.commit()

        print("Queue [" + str(row['queue_id']) + "] LOCKED")

        if mycursor.rowcount < 1:
            print("Queue [" + str(row['queue_id']) + "] is not updated")
            continue

        try:
            msg = MIMEMultipart()
            sender = str(row['sender_name']) + ' <' + str(row['sender_email']) + '>'
            msg['From'] = sender
            msg['To'] = row['receiver_email']
            msg['Subject'] = row['subject']
            msg.attach(MIMEText(row['html_content'], 'html', 'utf-8'))
            """
            msg.add_header('X-SES-CONFIGURATION-SET','9Prints_test_ConfigSet')
            """
            print("Queue [" + str(row['queue_id']) + "] SET HTML")

            if row['attachments'] is not None and row['attachments'] != '':
                row['attachments'] = json.loads(row['attachments'])

                for f in row['attachments']:
                    print("Queue [" + str(row['queue_id']) + "] CHECK FILE: " + str(f))

                    if os.path.isfile(f):
                        with open(f, 'rb') as fil:
                            print("Queue [" + str(row['queue_id']) + "] FILE ATTACHT")
                            part = MIMEBase('application', "octet-stream")
                            part.set_payload(fil.read())
                            encoders.encode_base64(part)
                            part.add_header('Content-Disposition', 'attachment', filename=os.path.basename(f))
                            msg.attach(part)
                            print("Queue [" + str(row['queue_id']) + "] FILE ATTACHTED")

            server.sendmail(sender, row['receiver_email'], msg.as_string())

            print("Queue [" + str(row['queue_id']) + "] SENT")
        except smtplib.SMTPException as e:
            print("Queue [" + str(row['queue_id']) + "] ERR: ", e.smtp_error)

            mycursor.execute("UPDATE osc_post_office_email_queue SET state = 'error', error_message = %s WHERE queue_id = %s AND state = 'queue'", [e.smtp_error, str(row['queue_id'])])
            mydb.commit()

            continue;
        except Exception as e:
            print("Queue [" + str(row['queue_id']) + "] ERR: ", sys.exc_info()[0])

            mycursor.execute("UPDATE osc_post_office_email_queue SET state = 'error', error_message = %s WHERE queue_id = %s AND state = 'queue'", [sys.exc_info()[0], str(row['queue_id'])])
            mydb.commit()

            continue;

        try:
            mycursor.execute("INSERT INTO osc_post_office_email (`token`,`email_key`,`member_id`,`note`,`sender_name`,`sender_email`,`receiver_name`,`receiver_email`,`subject`,`added_timestamp`,`modified_timestamp`) SELECT `token`,`email_key`,`member_id`,`note`,`sender_name`,`sender_email`,`receiver_name`,`receiver_email`,`subject`,`added_timestamp`,`modified_timestamp` FROM osc_post_office_email_queue WHERE queue_id = %s", [str(row['queue_id'])])
            mydb.commit()

            if mycursor.rowcount < 1:
                print("Queue [" + str(row['queue_id']) + "] is not migrate to sent email")
                continue
        except mysql.connector.IntegrityError as err:
            print("Queue [" + str(row['queue_id']) + "] {}".format(err))

        mycursor.execute("DELETE FROM osc_post_office_email_queue WHERE queue_id = %s", [str(row['queue_id'])])
        mydb.commit()

        print("Queue [" + str(row['queue_id']) + "] OK")
    except Exception as e:
        print(("Queue [" + str(row['queue_id']) + "] {}").format(sys.exc_info()[0]))
        continue

server.quit()
