import os
import json
import datetime
import sys
import getopt
import pymongo
import subprocess

mongo_client = None


def _getMongo(config):
    global mongo_client
    if mongo_client is None:
        try:
            host = config['mongodb']['host']
            port = config['mongodb']['port']
            username = config['mongodb']['username']
            password = config['mongodb']['password']
            dbname = config['mongodb']['dbname']
            tls_enable = config['mongodb']['tls_enable']
            tls_dir = config['mongodb']['tls_dir']
            auth_dbname = config['mongodb']['auth_dbname']
            env = config['mongodb']['env']

            auth = ''
            if username and password:
                auth = f"{username}:{password}@"

            auth_db = auth_dbname if auth_dbname != '' else dbname

            option = []

            if auth_db != '':
                option.append(f"authSource={auth_db}")

            if env != '' and env == 'production':
                option.append('retryWrites=false')

            if tls_enable and tls_enable is True and tls_dir:
                option.append(f"tls=true&tlsCAFile={tls_dir}")

            uri = f"mongodb://{auth}{host}:{port}/" + ("?" + "&".join(option) if option else "")

            mongo_client = pymongo.MongoClient(uri)
        except Exception as err:
            print("Connect to mongo fail: {}".format(err))
            sys.exit(2)

    return mongo_client


def main(argv):
    output = subprocess.getoutput(
        "ps -o pid,size,pmem,command ax --sort=-size | grep -e gossby-php-fpm -e master-php-fpm -e __SERVER__").splitlines()

    total_mem_usage_process = 0.0
    for row in output:
        # lấy theo size vì pmem bị làm tròn lên, gây sai lệch khi cộng dồn
        total_mem_usage_process = total_mem_usage_process + float(row.split()[1])

    percent_total_mem_usage_process = round(total_mem_usage_process / 64930556 * 100, 2)

    highest_percent_mem_usage_process = output[0].split()[2]

    site_path = ''

    try:
        opts, args = getopt.getopt(argv, "r:", ["path="])
    except getopt.GetoptError as err:
        print('getopt ' + str(err))
        sys.exit(2)

    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('test.py -d <data> -r <path>')
            sys.exit()
        elif opt in ("-r", "--path"):
            site_path = arg

    config_file = site_path + '/.python.json'

    if not os.path.exists(config_file):
        print("Python config file is not exist")
        sys.exit(2)

    try:
        f = open(config_file, 'r')
        config = json.loads(f.read())
        f.close()
    except Exception as err:
        print("Python config file not accessible: {}".format(err))
        sys.exit(2)

    client = _getMongo(config)
    db = client[config['mongodb']['dbname']]
    log_php_process = db["log_php_process"]
    log_cpu_process = db["log_cpu_process"]
    now = datetime.datetime.now()

    if float(highest_percent_mem_usage_process) > float(1) and percent_total_mem_usage_process > float(50):
        # nếu process tốn RAM nhất lớn hơn 1% và tổng RAM lớn hơn 50%, thì ghi log vào mongodb
        log_php_process.insert_one({
            "message": output[0:10],
            "percent_total_mem_usage_process": percent_total_mem_usage_process,
            "added_timestamp": int(now.timestamp()),
            "created_at": now.strftime("%d %B %Y %H:%M:%S")
        })

    percent_total_cpus_usage_process = subprocess.getoutput(
        'echo | awk -v c="$(nproc)" -v l="$(awk "{print $3}"< /proc/loadavg)" "{print l*100/c}"')

    if float(percent_total_cpus_usage_process) > 60:
        # nếu cpu trung bình của tất cả các cores > 60
        cpu_process = subprocess.getoutput("ps aux --sort -%cpu | head -10").splitlines()

        log_cpu_process.insert_one({
            "message": cpu_process[0:10],
            "percent_total_cpus_usage_process": percent_total_cpus_usage_process,
            "added_timestamp": int(now.timestamp()),
            "created_at": now.strftime("%d %B %Y %H:%M:%S")
        })


if __name__ == "__main__":
    main(sys.argv[1:])
