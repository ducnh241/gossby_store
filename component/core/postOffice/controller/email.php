<?php

class Controller_PostOffice_Email extends Abstract_Frontend_Controller {

    /**
     *
     * @var Model_PostOffice_Email
     */
    protected $_email = null;

    public function __construct() {
        parent::__construct();

        try {
            $this->_email = OSC::model('postOffice/email')->loadByUKey($this->_request->get('token'));

            if ($this->_email->data['opens'] == 0) {
                try {
                    OSC::model('postOffice/email_tracking')->setData([
                        'email_id' => $this->_email->getId(),
                        'event' => 'open',
                        'referer' => $_SERVER['HTTP_REFERER']
                    ])->save();
                    $this->_email->increment('opens', 1, 'modified_timestamp');
                } catch (Exception $ex) {
                    
                }
            }
        } catch (Exception $ex) {
            static::notFound();
        }
    }

    public function actionTrack() {
        $content = base64_decode("R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Disposition: attachment; filename="blank.gif"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($content));

        echo $content;

        die;
    }

    public function actionView() {
        if ($this->_email->data['html_content']) {
            echo $this->_email->data['html_content'];
        } else {
            echo '<pre>' . $this->_email->data['text_content'] . '</pre>';
        }
    }

    public function actionClick() {
        $clicked_url = $this->_request->get('ref');

        if ($clicked_url) {
            $clicked_url = base64_decode($clicked_url);
        } else {
            $clicked_url = OSC_FRONTEND_BASE_URL;
        }
        
        try {
            OSC::helper('report/common')->setReferer('https://osc-emailer.com/' . $this->_email->getUkey());
        } catch (Exception $ex) {

        }

        try {
            OSC::model('postOffice/email_tracking')->setData([
                'email_id' => $this->_email->getId(),
                'event' => 'click',
                'referer' => $_SERVER['HTTP_REFERER'],
                'event_data' => $clicked_url
            ])->save();
            $this->_email->increment('clicks', 1, 'modified_timestamp');
        } catch (Exception $ex) {
            
        }

        static::redirect($clicked_url);
    }

    public function actionUnsubscribing() {
        /* @var $DB OSC_Database */

        $DB = OSC::core('database');

        try {
            $DB->update('post_office_subscriber', ['newsletter' => 0], ['condition' => 'email = :email', 'params' => ['email' => $this->_email->data['receiver_email']]], 1);
        } catch (Exception $ex) {
            echo "Something went to wrong, please contact us to resolve the problem";
            die;
        }
        
        echo "You has been unsubscribed newsletter email successfully!";
        die;
    }
}
