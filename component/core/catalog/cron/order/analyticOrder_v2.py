import mysql.connector
import json
import xlsxwriter
import sys, getopt
import pwd
import grp
import os
import re
import datetime

def main(argv):
    output_file = ''
    domain = ''
    start_date = ''
    end_date = ''
    list_product_id = ''
    list_product = list
    site_path = ''

    try:
        opts, args = getopt.getopt(argv, "hf:d:s:e:l:r:", ["file=", "domain=", "start_date=", "end_date=", "list_product_id=", "path="])
    except getopt.GetoptError:
        print('analyticOrder.py -f <file> -d <domain> -s <start_date> -e <end_date> -l <list_product_id> -r <path>')
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('analyticOrder.py -f <file> -d <domain> -s <start_date> -e <end_date> -l <list_product_id>')
            sys.exit()
        elif opt in ("-f", "--file"):
            output_file = arg
        elif opt in ("-d", "-domain"):
            domain = arg
        elif opt in ("-s", "--start_date"):
            start_date = arg
        elif opt in ("-e", "--end_date"):
            end_date = arg
        elif opt in ("-l", "--list_product_id"):
            list_product_id = arg
        elif opt in ("-r", "--path"):
            site_path = arg

    if output_file == '' or start_date == '' or end_date == '' or list_product_id == '':
        print('analyticOrder.py -f <file> -d <domain> -s <start_date> -e <end_date> -l <list_product_id>')
        sys.exit(2)

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
        mydb_store = mysql.connector.connect(host=config['db']['host'], port=config['db']['port'],
                                       user=config['db']['username'], passwd=config['db']['password'],
                                       database=config['db']['database'])
        mycursor_store = mydb_store.cursor(dictionary=True)
        query = "SELECT product_id, sku, slug FROM osc_catalog_product WHERE product_id IN ({})".format(list_product_id)
        mycursor_store.execute(query)
        list_product = mycursor_store.fetchall()
    except mysql.connector.Error as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    try:
        mydb = mysql.connector.connect(host=config['db_master']['host'], port=config['db_master']['port'],
                                       user=config['db_master']['username'], passwd=config['db_master']['password'],
                                       database=config['db_master']['database'])
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
    worksheet = workbook.add_worksheet()
    worksheet.set_column('A:A', 20)

    headers = {
        'code': 'Order Id',
        'added_timestamp': 'Order Date',
        'item_type': 'Item Type',
        'item_name': 'Item Name',
        'item_value': 'Item Value',
    }
    header_keys = ['code', 'added_timestamp', 'item_type', 'item_name', 'item_value']

    item_headers = {
        'shipping_full_name': 'Customer name',
        'email': 'Customer email',
        'quantity': 'Quantity',
        'product_link': 'Product link',
        'order_detail': 'Order detail',
        'preview': 'Preview',
        'design_clipart': 'Design clipart'
    }
    item_header_keys = ['shipping_full_name', 'email', 'quantity', 'product_link', 'order_detail', 'preview', 'design_clipart']

    offset = 0
    limit = 10000
    start_row_idx = 1
    row_idx = 1
    row_idx_image_mockup_url = 1

    list_order_code = dict()

    header_format = workbook.add_format({'bold': True, 'font_color': '#ffffff', 'bg_color': '#89b7e5'})
    cell_header_idx = 0
    for key in header_keys:
        worksheet.write(0, cell_header_idx, headers.get(key), header_format)
        cell_header_idx += 1

    while True:
        try:
            query = "SELECT o.code, o.added_timestamp, o.email, o.shipping_full_name, o.ukey, oi.options, oi.product_id, oi.order_item_meta_id, oi.additional_data, oi.quantity, oi.design_url " \
                    "FROM osc_catalog_order AS o " \
                    "JOIN osc_catalog_order_item AS oi ON o.master_record_id = oi.order_master_record_id " \
                    "WHERE o.added_timestamp >= {} AND o.added_timestamp <= {} AND oi.product_id IN ({}) " \
                    "ORDER BY o.master_record_id ASC LIMIT {}, {}".format(start_date, end_date, list_product_id, offset, limit)
            mycursor.execute(query)
            rows = mycursor.fetchall()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            sys.exit(2)

        row_count = len(rows)
        offset += row_count

        if row_count < 1:
            break

        list_order_item_meta_id = []
        for row in rows:
            if row['order_item_meta_id'] is not None:
                list_order_item_meta_id.append(str(row['order_item_meta_id']))

        try:
            query = "SELECT `meta_id`, `custom_data` FROM `osc_catalog_order_item_meta` WHERE `master_record_id` IN ({})".format(','.join(list_order_item_meta_id))
            mycursor.execute(query)
            list_order_item_meta = mycursor.fetchall()
        except mysql.connector.Error as err:
            print("Something went wrong: {}".format(err))
            sys.exit(2)

        for row in rows:
            try:
                start_row_idx = row_idx

                list_options = list()
                list_image = list()
                list_mockup_image = list()
                list_design_url = list()
                list_ps_opt = dict()
                list_ps_photo = dict()
                list_ps_photo_full = dict()

                order_item_meta = next((item for item in list_order_item_meta if item['meta_id'] == row['order_item_meta_id']), None)

                if order_item_meta and order_item_meta['custom_data']:
                    try:
                        custom_data = json.loads(order_item_meta['custom_data'])
                        for data in custom_data:
                            if data['data']:
                                for item in data['data']:
                                    try:
                                        if data['data'][item] and isinstance(data['data'][item], dict) and 'config_preview' in data['data'][item]:
                                            config_preview = data['data'][item]['config_preview']
                                            for config_preview_item in config_preview:
                                                try:
                                                    if config_preview[config_preview_item]['type'] == 'input':
                                                        list_options.append(config_preview[config_preview_item]['form'] + ':' + config_preview[config_preview_item]['value'])

                                                    if config_preview[config_preview_item]['type'] == 'imageUploader':
                                                        list_image.append(json.loads(config_preview[config_preview_item]['value']))

                                                    m = re.match(r"ps_opt_([^_]+)", config_preview[config_preview_item]['layer'], flags=re.IGNORECASE)
                                                    if m:
                                                        content = re.sub('<[^>]+>', '', config_preview[config_preview_item]['value'].strip())
                                                        key = m.group(1) + '_' + str(item)
                                                        if key in list_ps_opt:
                                                            list_ps_opt[key] += ' | ' + content
                                                        else:
                                                            list_ps_opt[key] = content

                                                    m = re.match(r"ps_photo_([^_]+)", config_preview[config_preview_item]['layer'], flags=re.IGNORECASE)
                                                    if m:
                                                        key = m.group(1) + '_' + str(item)
                                                        if config_preview[config_preview_item]['type'] == 'imageUploader':
                                                            value = json.loads(config_preview[config_preview_item]['value'])
                                                            url = value['url']
                                                            url_full = 'https://personalizeddesign.9prints.com/storage/' + value['file']
                                                        else:
                                                            url = 'Wrong name for this option'
                                                            url_full = 'Wrong name for this option'

                                                        if key in list_ps_photo:
                                                            list_ps_photo[key] += ' | ' + url
                                                        else:
                                                            list_ps_photo[key] = url

                                                        if key in list_ps_photo_full:
                                                            list_ps_photo_full[key] += ' | ' + url_full
                                                        else:
                                                            list_ps_photo_full[key] = url_full
                                                except Exception as err:
                                                    continue
                                    except Exception as err:
                                        continue
                    except Exception as err:
                        continue

                try:
                    additional_data = json.loads(row['additional_data'])
                    if additional_data and additional_data['design_url_beta']:
                        for item in additional_data['design_url_beta']:
                            list_mockup_image.append(additional_data['design_url_beta'][item])
                except Exception:
                    ''

                try:
                    design_url = json.loads(row['design_url'])
                    if design_url:
                        for item in design_url:
                            if design_url[item] and type(design_url[item]) == dict:
                                for data in design_url[item]:
                                    if design_url[item][data]:
                                        list_design_url.append(design_url[item][data])
                except Exception:
                    ''

                format = workbook.add_format({
                    'bg_color': '#ffffff',
                    'border': 1,
                    'border_color': '#bcbcbc',
                    'text_wrap': True
                })

                if list_ps_opt:
                    for key, value in list_ps_opt.items():
                        worksheet.write(row_idx, 2, 'ps_opt', format)
                        worksheet.write(row_idx, 3, key, format)
                        worksheet.write(row_idx, 4, value, format)
                        row_idx += 1

                if list_ps_photo:
                    for key, value in list_ps_photo.items():
                        worksheet.write(row_idx, 2, 'ps_photo', format)
                        worksheet.write(row_idx, 3, key, format)
                        worksheet.write(row_idx, 4, value, format)
                        row_idx += 1

                if list_ps_photo_full:
                    for key, value in list_ps_photo_full.items():
                        worksheet.write(row_idx, 2, 'ps_photo_full', format)
                        worksheet.write(row_idx, 3, key, format)
                        worksheet.write(row_idx, 4, value, format)
                        row_idx += 1

                if row['options']:
                    try:
                        row_options = json.loads(row['options'])
                        for item in row_options:
                            try:
                                if item['title'] and item['value']:
                                    worksheet.write(row_idx, 2, 'Product options', format)
                                    worksheet.write(row_idx, 3, item['title'], format)
                                    worksheet.write(row_idx, 4, item['value'], format)
                                    row_idx += 1
                            except Exception:
                                continue
                    except Exception:
                        continue

                    if list_options:
                        for key, value in list_ps_photo.items():
                            worksheet.write(row_idx, 2, 'Product options', format)
                            worksheet.write(row_idx, 3, key, format)
                            worksheet.write(row_idx, 4, value, format)
                            row_idx += 1

                for key in item_header_keys:
                    if key == 'product_link':
                        product = next((item for item in list_product if item['product_id'] == row['product_id']), None)
                        if product and 'sku' in product:
                            value_cell = domain + '/product/' + product['sku'] + '/' + product['slug']
                        else:
                            value_cell = ''

                        worksheet.write(row_idx, 2, item_headers[key], format)
                        worksheet.write(row_idx, 4, value_cell, format)
                        row_idx += 1
                    elif key == 'order_detail':
                        worksheet.write(row_idx, 2, item_headers[key], format)
                        worksheet.write(row_idx, 4, domain + '/catalog/order/' + row['ukey'], format)
                        row_idx += 1
                    elif key == 'preview':
                        worksheet.write(row_idx, 2, item_headers[key], format)
                        for item in list_design_url:
                            worksheet.write(row_idx, 4, item, format)
                            row_idx += 1
                    elif key == 'design_clipart':
                        worksheet.write(row_idx, 2, item_headers[key], format)
                        for item in list_mockup_image:
                            worksheet.write(row_idx, 4, item, format)
                            row_idx += 1
                    else:
                        worksheet.write(row_idx, 2, item_headers[key], format)
                        worksheet.write(row_idx, 4, row[key], format)
                        row_idx += 1

                row_idx = max(row_idx, row_idx_image_mockup_url) + 1

                for i in range(start_row_idx, row_idx - 1):
                    worksheet.write(i, 0, row['code'] + '_' + str(list_order_code[row['code']]) if row['code'] in list_order_code else row['code'], format)
                    worksheet.write(i, 1, datetime.datetime.fromtimestamp(row['added_timestamp']).strftime('%d/%m/%Y %H:%M'), format)

                if row['code'] in list_order_code:
                    list_order_code[row['code']] += 1
                else:
                    list_order_code[row['code']] = 1
            except Exception as err:
                continue

    workbook.close()
    if uid != False and gid != False:
        os.chown(output_file, uid, gid)

if __name__ == "__main__":
    main(sys.argv[1:])