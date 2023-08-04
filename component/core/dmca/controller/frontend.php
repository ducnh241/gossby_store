<?php

class Controller_Dmca_Frontend extends Abstract_Frontend_Controller {

    public function __construct() {
        parent::__construct();

        $this->getTemplate()->setPageTitle($this->setting('theme/site_name') . ' | DMCA');
    }

    public function actionIntellectuaProperty() {
        $this->getTemplate()->addBreadcrumb('DMCA');

        if ($this->_request->get('dmca')){
            $dmca = $this->_request->get('dmca');

            try{
                if (!OSC::core('validate')->validUrl($dmca['campaign_url'])){
                    throw new Exception('Campaign Url is incorrect format');
                }

                if ($dmca['first_name'] == '') {
                    throw new Exception('First Name not found');
                }

                if ($dmca['last_name'] == '') {
                    throw new Exception('Last name not found');
                }

                if ($dmca['add1'] == '') {
                    throw new Exception('Address1 not found');
                }

                if ($dmca['country'] == '') {
                    throw new Exception('Country not found');
                }

                OSC::core('validate')->validEmail($dmca['email']);

                if (!preg_match("/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im", $dmca['phone'])) {
                    throw new Exception('Phone number is incorrect format');
                }

                if (!in_array($dmca['rights_owner'],['Rights Owner', 'Agent'])) {
                    throw new Exception('Are you the Rights Owner or an Agent');
                }

                if ($dmca['ip_owner'] == '') {
                    throw new Exception('Ip Owner not found');
                }

                if ($dmca['specific_concern'] == '') {
                    throw new Exception('specific concern not found');
                }

                if ($dmca['url_to_original_work'] == '') {
                    throw new Exception('url to original work not found');
                }

                $ukey = OSC::model('dmca/dmca')->setData([
                    'data' => $dmca,
                    'form' => 'IntellectuaProperty',
                    'added_timestamp' => time()
                ])->save()->getUkey();

                $file_url = OSC_FRONTEND_BASE_URL . '/dmca/report/' . $ukey;

                $email_content = <<<EOF
You are being reported DMCA<br />
Click the URL below to view:<br />
<a href="{$file_url}">{$file_url}</a>
EOF;

                $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

                if ($klaviyo_api_key != '') {
                    OSC::helper('klaviyo/common')->create([
                        'token' => $klaviyo_api_key,
                        'event' => 'System office',
                        'customer_properties' => [
                            '$email' => OSC::helper('core/setting')->get('theme/contact/email'),
                        ],
                        'properties' => [
                            'receiver_email' => OSC::helper('core/setting')->get('theme/contact/email'),
                            'receiver_name' => OSC::helper('core/setting')->get('theme/contact/name'),
                            'title' => 'Report for Intellectua Property',
                            'message' => $email_content
                        ]
                    ]);
                }

                $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
                if ($skip_amazon != 1) {
                    OSC::helper('postOffice/email')->create([
                        'priority' => 1000,
                        'subject' => 'Report for Intellectua Property',
                        'receiver_email' => OSC::helper('core/setting')->get('theme/contact/email'),
                        'receiver_name' => OSC::helper('core/setting')->get('theme/contact/name'),
                        'html_content' => $email_content,
                    ]);
                }

                $this->_ajaxResponse();


            }catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }


        }
        $this->output($this->getTemplate()->build('dmca/index'));
    }

    public function actionViewReport(){
        try{
            $ukey = $this->_request->get('key');

            $model =  OSC::model('dmca/dmca')->loadByUKey($ukey);

            if ($model->data['form'] == 'IntellectuaProperty'){
                $this->output($this->getTemplate()->build('dmca/complete_ipc', ['model' => $model]));
            }else{
                $this->output($this->getTemplate()->build('dmca/complete_aup', ['model' => $model]));
            }
        }catch (Exception $ex){
            if ($ex->getCode() == 404) {
                $this->_ajaxError('Report not exists');
            } else {
                $this->_ajaxError($ex->getCode());
            }
        }

    }

    public function actionAcceptableUsePolicy() {
        $this->getTemplate()->addBreadcrumb('DMCA');
        if ($this->_request->get('dmca')){
            $dmca = $this->_request->get('dmca');

            try{
                if (!OSC::core('validate')->validUrl($dmca['report_url'])){
                    throw new Exception('Report Url is incorrect format');
                }

                if (empty($dmca['report_checkbox'])) {
                    throw new Exception('need reason to report');
                }

                $ukey = OSC::model('dmca/dmca')->setData([
                    'data' => $dmca,
                    'form' => 'AcceptableUsePolicy',
                    'added_timestamp' => time()
                ])->save()->getUkey();


                $file_url = OSC_FRONTEND_BASE_URL . '/dmca/report/' . $ukey;

                $email_content = <<<EOF
You are being reported DMCA<br />
Click the URL below to view:<br />
<a href="{$file_url}">{$file_url}</a>
EOF;
                $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

                if ($klaviyo_api_key != '') {
                    OSC::helper('klaviyo/common')->create([
                        'token' => $klaviyo_api_key,
                        'event' => 'System office',
                        'customer_properties' => [
                            '$email' => OSC::helper('core/setting')->get('theme/contact/email'),
                        ],
                        'properties' => [
                            'receiver_email' => OSC::helper('core/setting')->get('theme/contact/email'),
                            'receiver_name' => OSC::helper('core/setting')->get('theme/contact/name'),
                            'title' => 'Report for Acceptable Use Policy',
                            'message' => $email_content
                        ]
                    ]);
                }

                $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
                if ($skip_amazon != 1) {
                    OSC::helper('postOffice/email')->create([
                        'priority' => 1000,
                        'subject' => 'Report for Acceptable Use Policy',
                        'receiver_email' => OSC::helper('core/setting')->get('theme/contact/email'),
                        'receiver_name' => OSC::helper('core/setting')->get('theme/contact/name'),
                        'html_content' => $email_content,
                    ]);
                }

                $this->_ajaxResponse($ukey);

            }catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }

        }

        $this->output($this->getTemplate()->build('dmca/report'));
    }
}
