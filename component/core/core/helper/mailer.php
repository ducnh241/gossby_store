<?php

use PHPMailer\PHPMailer\PHPMailer;

class Helper_Core_Mailer extends OSC_Object {

    const SMTP_HOST = OSC_SMTP_HOST;
    const SMTP_PORT = OSC_SMTP_PORT;
    const SMTP_SECURE = OSC_SMTP_SECURE;
    const SMTP_USERNAME = OSC_SMTP_USERNAME;
    const SMTP_PASSWORD = OSC_SMTP_PASSWORD;
    const SMTP_SENDER_EMAIL = OSC_SMTP_SENDER_EMAIL;
    const SMTP_SENDER_NAME = OSC_SMTP_SENDER_NAME;

    /**
     * 
     * @param type $recipient
     * @param string $subject
     * @param string $content
     * @param array $attachments
     * @return type
     */
    public function send($recipient, string $subject, string $content, array $attachments = [], $options = []) {
        return $this->_send($recipient, $subject, ['content' => $content, 'is_html' => false], $attachments, $options);
    }

    /**
     * 
     * @param type $recipient
     * @param string $subject
     * @param string $html_content
     * @param array $attachments
     * @param string $text_content
     * @return type
     */
    public function sendHTML($recipient, string $subject, string $html_content, array $attachments = [], string $text_content = '', $options = []) {
        return $this->_send($recipient, $subject, ['content' => $html_content, 'alt_body' => $text_content, 'is_html' => true], $attachments, $options);
    }

    protected function _send($recipient, string $subject, $content, array $attachments = [], $options = []) {
        if(!is_array($options)) {
            $options = [];
        }
        
        $mail = new PHPMailer;

        $mail->isSMTP();

        $mail->Host = static::SMTP_HOST;
        $mail->Port = static::SMTP_PORT;

        $mail->SMTPAuth = true;
        $mail->SMTPSecure = static::SMTP_SECURE;
        $mail->Username = static::SMTP_USERNAME;
        $mail->Password = static::SMTP_PASSWORD;

        //$mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'ConfigSet');

        $mail->setFrom((isset($options['sender_email']) && $options['sender_email']) ? $options['sender_email'] : OSC::helper('core/setting')->get('theme/contact/email'), (isset($options['sender_name']) && $options['sender_name']) ? $options['sender_name'] : OSC::helper('core/setting')->get('theme/site_name'));

        if (is_array($recipient)) {
            $recipient_name = $recipient[1];
            $recipient = $recipient[0];
        }

        $mail->addAddress($recipient, $recipient_name);

        $mail->isHTML($content['is_html']);
        $mail->Subject = $subject;
        $mail->Body = $content['content'];

        if ($content['is_html'] && isset($content['alt_body']) && $content['alt_body']) {
            $mail->AltBody = $content['alt_body'];
        }

        if (count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                if (!is_array($attachment)) {
                    $attachment = [$attachment];
                }

                if (count($attachment) < 1 || !isset($attachment[0])) {
                    continue;
                }

                $attachment[0] = trim(strval($attachment[0]));

                if (!isset($attachment[1])) {
                    $attachment[1] = '';
                } else {
                    $attachment[1] = trim(strval($attachment[1]));
                }

                if (is_file($attachment[0])) {
                    $mail->addAttachment($attachment[0], $attachment[1]);
                } else {
                    if (!$attachment[1]) {
                        continue;
                    }

                    $mail->addStringAttachment($attachment[0], $attachment[1]);
                }
            }
        }

        if (!$mail->send()) {
            throw new Exception($mail->ErrorInfo);
        }
    }

}
