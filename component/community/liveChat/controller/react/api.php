<?php
# Attachment details:
class FileDetail {
    public $path;
    public $name;
    public $contentType;

    function __construct($path, $name, $contentType) {
        $this->path = $path;
        $this->name = $name;
        $this->contentType = $contentType;
    }
}

class Controller_LiveChat_React_Api extends Abstract_Frontend_ReactApiController
{
    protected function getDataConfig() {
        if (!defined('FRESHDESK_DOMAIN')) {
            define('FRESHDESK_DOMAIN', '');
        }
        if (!defined('FRESHDESK_API_KEY')) {
            define('FRESHDESK_API_KEY', '');
        }
        if (!defined('FRESHDESK_API_PASSWORD')) {
            define('FRESHDESK_API_PASSWORD', '');
        }
        if (!defined('FRESHDESK_RESPONDER_ID')) {
            define('FRESHDESK_RESPONDER_ID', 0);
        }
    }

    public function actionPostTicket()
    {
        try {
            $this->getDataConfig();

            $concern = $this->_request->get('concern', 'default');

            $title = $this->_request->get('title', 'New ticket from Chatbot about '. $concern);

            $email = $this->_request->get('email');

            $fullname = $this->_request->get('fullname');

            $tag = $this->_request->get('tag');

            $message = $this->_request->get('message');

            if ($title == '' || $email == '' || $message == '' || $fullname == '') {
                throw new Exception('Data is incorrect');
            }

            OSC::core('validate')->validEmail($email);

            $data_request = [
                'name' => $fullname,
                'description' => $message,
                'subject' => $title,
                'email' => $email,
                'tags' => explode(',', $tag),
                'attachments' => $this->_request->get('attachments'),
            ];

            OSC::helper('liveChat/common')->createTicketFreshDesk($data_request);

            $this->sendSuccess();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionUploadAttachment() {
        try {
            $uploader = new OSC_Uploader();

            if (!in_array($uploader->getExtension(), ['png', 'jpg', 'gif', 'jpeg'], true)) {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $tmp_file_path = 'customer/livechat/'. OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();

            $tmp_file_path_saved = OSC_Storage::preDirForSaveFile($tmp_file_path);

            $uploader->save($tmp_file_path_saved, true);

            OSC_File::verifyImage($tmp_file_path_saved);

            $this->sendSuccess($tmp_file_path);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }
}
