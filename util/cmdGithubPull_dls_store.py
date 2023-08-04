import os
import sys
import subprocess
import socket
import requests

service_code_path = '/path/to/service/code/folder'
service_multisite_root_path = '/path/to/service/multisite/root/folder' #Set to empty if in one site mode
deploy_key_file = '/path/to/deploy/key'
telegram_bot_token = ''
telegram_chat_id = ''

def getServerAddress():
    return socket.gethostbyname(socket.gethostname())

def telegramBotSendText(msg):
    response = requests.get('https://api.telegram.org/bot' + telegram_bot_token + '/sendMessage?parse_mode=html&chat_id=' + telegram_chat_id + '&parse_mode=Markdown&text=' + msg)
    return response.json()

def execCMD(cmd):
    response = ''   
    
    print("==========\n" + cmd + "\n==========")
    
    try:
        returned_output = subprocess.check_output(cmd,shell=True,stderr=subprocess.STDOUT).decode("utf-8")
    except subprocess.CalledProcessError as e:
        returned_output = "command '{}' return with error (code {}): {}".format(e.cmd, e.returncode, e.output)
    except:
        returned_output = 'ERROR: ' + str(sys.exc_info()[1])
        
    print(returned_output + "\n\n")

execCMD("cd " + service_code_path + "; ssh-agent bash -c 'ssh-add " + deploy_key_file + "; git pull'");
execCMD("cd " + service_code_path + "/library; composer update --no-interaction");

if isinstance(service_multisite_root_path, str) and service_multisite_root_path != "":
    if os.path.isdir(service_multisite_root_path):    
        files = os.listdir(service_multisite_root_path);

        for file in files:
            site_path = service_multisite_root_path + '/' + file

            if os.path.isdir(site_path) == False:
                continue

            if re.search("^[^\/]+\.[a-zA-Z]+$", file) == None:
                continue

            print(file)

            if os.path.isdir(site_path + '/var/system'):
                execCmd("rm -rf " + site_path + "/var/system")
            else:
                print("==========\n" + site_path + "/var/system is not exist\n==========")
    else:
        print("==========\n" + service_multisite_root_path + " is not exist\n==========")
else:
    if os.path.isdir(service_code_path + '/var/system'):
        execCmd("rm -rf " + service_code_path + "/var/system")
    else:
        print("==========\n" + service_code_path + "/var/system is not exist\n==========")

execCMD("cd " + service_code_path + "/frontend && npm install --production && npm run build && pm2 start ecosystem.config.js --env production")

telegramBotSendText("Code manually pulled from repo <b>batsatla/dls_store</b> to server <b>{}</b> via command line".format(getServerAddress()))

print("DONE")
