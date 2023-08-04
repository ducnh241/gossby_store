import os
import sys
import grp
import pwd
import json
import copy
import getopt
import errno
import mysql.connector

import magic
import redis
import boto3
import botocore


def main(argv):
    inputs = {}

    try:
        opts, args = getopt.getopt(argv, "h:i:", ["help", "input="])
    except getopt.GetoptError as err:
        print('imageOptimize.py -i <input>')
        sys.exit(2)
    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print('imageOptimize.py -i <input>')
            sys.exit()
        elif opt in ("-i", "--input"):
            inputs = json.loads(arg)

    for key in ['root_path', 'process_key', 'object_prefix', 'cache_prefix']:
        if inputs.get(key) == None or inputs.get(key) == '':
            print('imageOptimize.py -i <input>: Missing input params "' + key + '"')
            sys.exit(2)

    root_path = inputs.get('root_path')
    process_key = inputs.get('process_key')
    object_prefix_s3 = str(inputs.get('object_prefix'))
    cache_prefix = inputs.get('cache_prefix')

    config_file = root_path + '/.python.json'

    if not os.path.exists(config_file):
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
        mydb = mysql.connector.connect(
            host=config['db']['host'],
            port=config['db']['port'],
            user=config['db']['username'],
            passwd=config['db']['password'],
            database=config['db']['database']
        )
        mycursor = mydb.cursor(dictionary=True)
        mycursor.execute("SELECT * FROM osc_core_image_optimize WHERE process_key = %s ORDER BY record_id ASC",
                         [process_key])
        rows = mycursor.fetchall()
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

    if len(rows) < 1:
        print("No rows found to render")
        sys.exit(2)

    cache = get_redis(config['redis'])
    s3_client = get_s3_client(config['aws'])

    for row in rows:
        local_file_path = root_path + '/' + row['original_path']
        s3_object_path = object_prefix_s3 + '/' + row['original_path']
        optimized_path = row['optimized_path']
        optimized_file_path = root_path + '/' + row['optimized_path']
        s3_optimized_object_path = object_prefix_s3 + '/' + optimized_path

        if object_exist(s3_client, config['aws']['s3']['bucket'], s3_optimized_object_path):
            optimized_url = get_s3_url(
                config['aws']['s3']['region'],
                config['aws']['s3']['bucket'],
                s3_optimized_object_path
            )
            cache_key = cache_prefix + ':' + optimized_path
            cache.set(cache_key, optimized_url)
            download_file_from_s3(s3_client, config['aws']['s3']['bucket'], s3_optimized_object_path, optimized_file_path)
            continue

        if object_exist(s3_client, config['aws']['s3']['bucket'], s3_object_path):
            download_file_from_s3(s3_client, config['aws']['s3']['bucket'], s3_object_path, local_file_path)

        if not os.path.exists(local_file_path):
            continue

        pre_command = ['convert', local_file_path]

        if row['extension'] == 'jpg':
            pre_command.append('-background')
            pre_command.append('white')
            pre_command.append('-flatten')

        if row['extension'] == 'gif':
            pre_command.append('-coalesce')

        if row['crop_flag'] == 1 and row['width'] > 0 and row['height'] > 0:
            pre_command.append('-resize')
            pre_command.append(str(row['width']) + 'x' + str(row['height']) + '^')
            pre_command.append('-gravity')
            pre_command.append('center')
            pre_command.append('-crop')
            pre_command.append(str(row['width']) + 'x' + str(row['height']) + '+0+0')
        else:
            pre_command.append('-resize')
            pre_command.append('"' + str(row['width']) + 'x' + str(row['height']) + '>"')

        if row['extension'] != 'gif':
            pre_command.append('-sampling-factor')
            pre_command.append('4:2:0')
            pre_command.append('-strip')
            pre_command.append('-quality')
            pre_command.append('70')
            pre_command.append('-interlace')
            pre_command.append('Plane')

        command = copy.copy(pre_command)
        local_optimized_path = root_path + '/' + optimized_path
        command.append(root_path + '/' + optimized_path)

        try:
            os.system(' '.join(command))
        except:
            print('Unable to process file: ' + row['original_path'])

            if (uid != False and gid != False):
                os.chown(optimized_path, uid, gid)
                os.chmod(optimized_path, 0644)

        result = upload_file_to_s3(
            s3_client, config['aws']['s3']['bucket'],
            s3_optimized_object_path,
            local_optimized_path
        )
        if result:
            optimized_url = get_s3_url(
                config['aws']['s3']['region'],
                config['aws']['s3']['bucket'],
                s3_optimized_object_path
            )
            cache_key = cache_prefix + ':' + optimized_path
            cache.set(cache_key, optimized_url)

        if os.path.exists(local_file_path):
            os.remove(local_file_path)

    mycursor.execute("DELETE FROM osc_core_image_optimize WHERE process_key = %s", [process_key])
    mydb.commit()


def get_s3_client(aws_config):
    try:
        s3_client = boto3.client(
            's3',
            region_name=aws_config['s3']['region'],
            aws_access_key_id=aws_config['s3']['client_id'],
            aws_secret_access_key=aws_config['s3']['client_secret']
        )
    except botocore.exceptions.ClientError as err:
        print("Something went wrong: {}".format(err))
        sys.exit(2)

    return s3_client


def object_exist(s3_client, bucket, s3_object_path):
    try:
        s3_client.head_object(Bucket=bucket, Key=s3_object_path)
    except botocore.exceptions.ClientError:
        return False
    return True


def download_file_from_s3(s3_client, s3_bucket, s3_object_path, local_file_path):
    try:
        if os.path.exists(local_file_path):
            return

        try:
            os.makedirs(os.path.dirname(local_file_path))
        except OSError as e:
            if e.errno != errno.EEXIST:
                raise

        s3_client.download_file(s3_bucket, s3_object_path, local_file_path)
    except Exception as err:
        print("Unable to download file: {}".format(err))


def upload_file_to_s3(s3_client, s3_bucket, s3_object_path, local_file_path):
    try:
        if object_exist(s3_client, s3_bucket, s3_object_path):
            return True

        mime = magic.Magic(mime=True)
        content_type = mime.from_file(local_file_path)
        extra_args = {'ACL': 'public-read', 'ContentType': content_type}
        s3_client.upload_file(local_file_path, s3_bucket, s3_object_path, ExtraArgs=extra_args)
    except:
        print("Unable to upload file: {}".format(s3_object_path))
        return False
    return True


def get_s3_url(region, bucket_name, object_path):
    return "https://s3-%s.amazonaws.com/%s/%s" % (region, bucket_name, object_path)


def get_redis(redis_config):
    return redis.Redis(
        host=redis_config['host'],
        port=redis_config['port'],
        password=redis_config['password']
    )


if __name__ == "__main__":
    main(sys.argv[1:])
