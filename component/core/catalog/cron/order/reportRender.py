import mysql.connector
import json
import xlsxwriter
import sys, getopt
import pwd
import grp
import os
import re

def main(argv):
    export_key = ''
    output_file = ''
    site_path = ''

    try:
        opts, args = getopt.getopt(argv,"hi:o:r:",["help","key=","file=","path="])
    except getopt.GetoptError:
        print 'renderReport.py -i <export_key> -o <excel_file_path> -r <site_path>'
        sys.exit(2)
    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print 'renderReport.py -i <export_key> -o <excel_file_path> -r <site_path>'
            sys.exit()
        elif opt in ("-i", "--key"):
            export_key = arg
        elif opt in ("-o", "--file"):
            output_file = arg
        elif opt in ("-r", "--path"):
            site_path = arg

    if(export_key=='' or output_file=='' or site_path == ''):
        print 'renderReport.py -i <export_key> -o <excel_file_path> -r <site_path>'
        sys.exit(2)

    config_file = site_path + '/.python.json'

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
        uid = pwd.getpwnam("apache").pw_uid
        gid = grp.getgrnam("apache").gr_gid
    except:
        try:
            uid = pwd.getpwnam("www-data").pw_uid
            gid = grp.getgrnam("www-data").gr_gid
        except:
            uid = False
            gid = False
      
    workbook = xlsxwriter.Workbook(output_file)
    
    headers = {
        'order': {
            'code': "Code",
            'date': "Date",
            'product_name': "Product Name",
            'variant_title': "Variant title",
            'quantity': "Quantity",
            'gross_sale': "Gross Sale",
            'discount': "Discount",
            'shipping_fee': "Shipping Fee",
            'revenue': "Revenue",
            'payment_method': "Payment Method",
            'payment_account': "Payment Account",
            'vendor': "Vendor",
            'sref': "SREF",
            'province_code': "Province Code",
            'country': "Country",
            'country_code': "Country Code"
        },
        'refund': {
            'code': "Code",
            'date': "Date",
            'product_name': "Product Name",
            'payment_method': "Payment Method",
            'payment_account': "Payment Account",
            'variant_title': "Variant title",
            'vendor': "Vendor",
            'sref': "SREF",
            'province_code': "Province Code",
            'country': "Country",
            'country_code': "Country Code",
            'quantity': "Refunded Quantity",
            'refunded': "Refunded Amount",
            'reason': 'Reason'
        },
        'fulfill': {
            'code': "Code",
            'date': "Date",
            'product_name': "Product Name",
            'variant_title': "Variant title",
            'vendor': "Vendor",
            'sref': "SREF",
            'province_code': "Province Code",
            'country': "Country",
            'country_code': "Country Code",
            'supplier': "Supplier",
            'tracking_url': "Tracking URL",
            'shipping_carrier': "Shipping Carrier",
            'tracking_number': "Tracking number",
            'quantity': "Fulfilled Quantity"
        }
    }
    header_keys = {
        'order': ['code', 'date', 'product_name', 'variant_title', 'quantity', 'gross_sale', 'discount', 'shipping_fee', 'revenue', 'payment_method', 'payment_account', 'vendor', 'sref', 'province_code', 'country', 'country_code'],
        'refund': ['code', 'date', 'product_name', 'variant_title', 'vendor', 'sref', 'province_code', 'country', 'country_code', 'quantity', 'refunded', 'payment_method', 'payment_account', 'reason'],
        'fulfill': ['code', 'date', 'product_name', 'variant_title', 'vendor', 'sref', 'province_code', 'country', 'country_code', 'supplier', 'quantity', 'shipping_carrier', 'tracking_number', 'tracking_url']
    }
    reset_column = {
        'order': {'gross_sale': 0, 'discount': 0, 'shipping_fee': 0, 'revenue': 0},
        'refund': {'refunded': 0},
        'fulfill': {}    
    }
       
    worksheet = ''
    current_type = ''
    row_idx=1
    last_order_code = ''
    order_counter=0
    offset = 0
    limit = 10000

    while(True):
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

        for row in rows:
            matched = re.findall("^(fulfill|refund|order)(:[0-9]+)?$", row['secondary_key'])

            if(len(matched) != 1):
                print("Worksheet type not found: " + row['secondary_key'] + ' (#' + row['record_id'] + ')')
                sys.exit(2)
            
            if(matched[0][0] != current_type):
                worksheet = workbook.add_worksheet(matched[0][0])
                current_type = matched[0][0]
                    
                header_format = workbook.add_format({'bold': True, 'font_color': '#ffffff', 'bg_color': '#89b7e5'})
                                
                cell_idx=0
                        
                for key in header_keys[current_type]:
                    worksheet.write(0, cell_idx, headers[current_type][key], header_format)
                    cell_idx+=1
                        
                row_idx=1
                last_order_code = ''
                order_counter=0
                
            export_data=json.loads(row['export_data'])
            
            is_new_order=False
                
            if(export_data['code'] != last_order_code):
                last_order_code = export_data['code']
                is_new_order=True
                order_counter+=1
                
            if order_counter%2 :
                format = workbook.add_format({'bg_color': '#f7f7f7', 'border':1, 'border_color':'#bcbcbc'})
            else:
                format = workbook.add_format({'bg_color': '#ffffff', 'border':1, 'border_color':'#bcbcbc'})
               
            if(is_new_order):
                format.set_top(2)
                format.set_top_color('#bcbcbc')
            else:
                for key in reset_column[current_type]:
                     export_data[key] = reset_column[current_type][key]
    
            cell_idx=0
               
            for key in header_keys[current_type]:
                if(key not in export_data):
                    export_data[key] = ''
                    
                worksheet.write(row_idx, cell_idx, export_data[key], format)
                    
                cell_idx+=1

            row_idx+=1
            
        if(row_count < limit):
            break
        
    workbook.close() 

    if(uid != False and gid != False):
        os.chown(output_file, uid, gid)
    
    mycursor.execute("DELETE FROM osc_catalog_order_export_draft WHERE export_key = %s", [export_key])
    mydb.commit()

if __name__ == "__main__":
    main(sys.argv[1:])
