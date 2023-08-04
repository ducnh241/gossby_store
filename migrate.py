import mysql.connector
import sys
import pwd
import grp
import os
import re

def main(argv):
    try:
        mydb = mysql.connector.connect(host='',port=3306, user='root', passwd='', database='9prints_migrate')
        mycursor = mydb.cursor(dictionary=True)
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    while(True):
        try:
            mycursor.execute("SHOW TABLES", [])
            rows = mycursor.fetchall()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            sys.exit(2)

        for row in rows:
            print(row['Tables_in_9prints_migrate']);

        sys.exit(2)

        try:
            mycursor.execute("SELECT * FROM osc_catalog_order_export_draft WHERE export_key = %s ORDER BY record_id ASC LIMIT %s,%s", [export_key, offset, limit])
            rows = mycursor.fetchall()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            sys.exit(2)

        row_count = len(rows)
        offset +=row_count

        if(row_count < 1):
            break

    mycursor.execute("DELETE FROM osc_catalog_order_export_draft WHERE export_key = %s", [export_key])
    mydb.commit()

if __name__ == "__main__":
    main()
