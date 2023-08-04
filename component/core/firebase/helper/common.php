<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Helper_Firebase_Common {

    /**
     * 
     * @param string $token
     * @return array
     * @throws Exception
     */
    public function tokenGetInfo($token) {
        $response = OSC::core('network')->curl('https://iid.googleapis.com/iid/info/' . $token . '?details=true', array('request_method' => 'GET', 'headers' => array('Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'))));

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        if (!is_array($response['content'])) {
            throw new Exception('Response content is incorrect');
        }

        return $response['content'];
    }

    /**
     * 
     * @param array $send_to
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tokenSendMessage($token, $title, $message, $url, $options = null) {
        return $this->_sendMessage(array('token', $token), $title, $message, $url, $options);
    }

    /**
     * 
     * @param integer $member_id
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function memberSendMessage($member_id, $title, $message, $url, $options) {
        return $this->groupSendMessage($member_id, $title, $message, $url, $options);
    }

    /**
     * 
     * @param integer $member_id
     * @param mixed $tokens
     * @return string
     * @throws Exception
     */
    public function memberAddToken($member_id, $tokens) {
        return $this->groupAddToken($member_id, $tokens);
    }

    /**
     * 
     * @param integer $member_id
     * @param mixed $tokens
     * @return string
     * @throws Exception
     */
    public function memberRemoveToken($member_id, $tokens) {
        return $this->groupRemoveToken($member_id, $tokens);
    }

    /**
     * 
     * @param integer $member_id
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function groupSendMessage($member_id, $title, $message, $url, $options = null) {
        $notification_key = $this->groupGetKey($member_id);

        if (!$notification_key) {
            throw new Exception();
        }

        return $this->_legacySendMessage(array('to', $notification_key), $title, $message, $url, $options);
    }

    /**
     * 
     * @param integer $member_id
     * @return string
     * @throws Exception
     */
    public function groupGetKey($member_id) {
        $member_id = intval($member_id);

        if ($member_id < 1) {
            throw new Exception('Member ID is less than 1');
        }

        $curl_options = array(
            'request_method' => 'GET',
            'headers' => array(
                'Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'),
                'project_id' => OSC::systemRegistry('FIREBASE_SENDER_ID'),
                'Content-Type' => 'application/json'
            )
        );

        $response = OSC::core('network')->curl('https://fcm.googleapis.com/fcm/notification?notification_key_name=member-' . $member_id, $curl_options);

        if ($response['response_code'] != 200 || !is_array($response['content']) || !isset($response['content']['notification_key'])) {
            return null;
        }

        return $response['content']['notification_key'];
    }

    /**
     * 
     * @param integer $member_id
     * @param mixed $tokens
     * @return string
     * @throws Exception
     */
    public function groupCreate($member_id, $tokens) {
        $member_id = intval($member_id);

        if ($member_id < 1) {
            throw new Exception('Member ID is less than 1');
        }

        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }

        $curl_options = array(
            'request_method' => 'POST',
            'json' => array(
                'operation' => 'create',
                'notification_key_name' => 'member-' . $member_id,
                'registration_ids' => $tokens
            ),
            'headers' => array(
                'Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'),
                'project_id' => OSC::systemRegistry('FIREBASE_SENDER_ID')
            )
        );

        $response = OSC::core('network')->curl('https://fcm.googleapis.com/fcm/notification', $curl_options);

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        if (!is_array($response['content']) || !isset($response['content']['notification_key'])) {
            throw new Exception('Response content is incorrect');
        }

        return $response['content']['notification_key'];
    }

    /**
     * 
     * @param integer $member_id
     * @param mixed $tokens
     * @return string
     * @throws Exception
     */
    public function groupAddToken($member_id, $tokens) {
        $notification_key = $this->groupGetKey($member_id);

        if (!$notification_key) {
            return $this->groupCreate($member_id, $tokens);
        }

        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }

        $curl_options = array(
            'request_method' => 'POST',
            'json' => array(
                'operation' => 'add',
                'notification_key' => $notification_key,
                'notification_key_name' => 'member-' . $member_id,
                'registration_ids' => $tokens
            ),
            'headers' => array(
                'Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'),
                'project_id' => OSC::systemRegistry('FIREBASE_SENDER_ID')
            )
        );

        $response = OSC::core('network')->curl('https://android.googleapis.com/gcm/notification', $curl_options);

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        if (!is_array($response['content']) || !isset($response['content']['notification_key'])) {
            throw new Exception('Response content is incorrect');
        }

        return $response['content']['notification_key'];
    }

    /**
     * 
     * @param integer $member_id
     * @param mixed $tokens
     * @return string
     * @throws Exception
     */
    public function groupRemoveToken($member_id, $tokens) {
        $notification_key = $this->groupGetKey($member_id);

        if (!$notification_key) {
            return true;
        }

        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }

        $curl_options = array(
            'request_method' => 'POST',
            'json' => array(
                'operation' => 'remove',
                'notification_key' => $notification_key,
                'notification_key_name' => 'member-' . $member_id,
                'registration_ids' => $tokens
            ),
            'headers' => array(
                'Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'),
                'project_id' => OSC::systemRegistry('FIREBASE_SENDER_ID')
            )
        );

        $response = OSC::core('network')->curl('https://android.googleapis.com/gcm/notification', $curl_options);

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        if (!is_array($response['content']) || !isset($response['content']['notification_key'])) {
            throw new Exception('Response content is incorrect');
        }

        return $response['content']['notification_key'];
    }

    /**
     * 
     * @param string $topic
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $options
     * @return array
     */
    public function topicSendMessage($topic, $title, $message, $url, $options = null) {
        return $this->_sendMessage(array('topic', $topic), $title, $message, $url, $options);
    }

    /**
     * 
     * @param string $topic
     * @param mixed $tokens
     */
    public function topicSubscribe($topic, $tokens) {
        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }

        $tokens = array_values($tokens);

        $response = OSC::core('network')->curl('https://iid.googleapis.com/iid/v1:batchAdd', array('request_method' => 'POST', 'json', 'data' => array('to' => $topic, 'registration_tokens' => '/topics/' . $tokens), 'headers' => array('Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'))));

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        if (!is_array($response['content']) || !isset($response['content']['results']) || !is_array($response['content']['results'])) {
            throw new Exception('Response content is incorrect');
        }

        $results = array();

        foreach ($response['content']['results'] as $idx => $row) {
            $results[$tokens[$idx]] = isset($row['error']) ? $row['error'] : true;
        }

        return $results;
    }

    /**
     * 
     * @param string $topic
     * @param mixed $tokens
     */
    public function topicUnsubscribe($topic, $tokens) {
        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }

        $tokens = array_values($tokens);

        $response = OSC::core('network')->curl('https://iid.googleapis.com/iid/v1:batchRemove', array('request_method' => 'POST', 'json', 'data' => array('to' => $topic, 'registration_tokens' => '/topics/' . $tokens), 'headers' => array('Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY'))));

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        if (!is_array($response['content']) || !isset($response['content']['results']) || !is_array($response['content']['results'])) {
            throw new Exception('Response content is incorrect');
        }

        $results = array();

        foreach ($response['content']['results'] as $idx => $row) {
            $results[$tokens[$idx]] = isset($row['error']) ? $row['error'] : true;
        }

        return $results;
    }

    /**
     * 
     * @param array $send_to
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function _sendMessage($send_to, $title, $message, $url, $options = null) {
        if (!is_array($options)) {
            $options = array();
        }

        $client = new Google_Client();

        $client->useApplicationDefaultCredentials();

        $client->setAuthConfig(OSC_SITE_PATH . '/firebase-service-account.json');

        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $httpClient = $client->authorize();

        $message = array(
            "message" => array(
                $send_to[0] => $send_to[1],
                'webpush' => array(
                    "data" => array(
                        'title' => $title,
                        'body' => $message,
                        'url' => $url,
                        'icon' => $options['icon']
                    )
                )
//                , 'android' => array(
//                    'notification' => array(
//                        "title" => $title,
//                        "body" => $message,
//                        "icon" => $options['icon'],
//                        "color" => '#dddddd',
//                        "tag" => OSC_SITE_KEY,
//                        "click_action" => $url
//                    )
//                )
            )
        );
        
        if(isset($options['verify_code'])) {
            $message['message']['webpush']['data']['verify_code'] = $options['verify_code'];
        }

        $response = $httpClient->post('https://fcm.googleapis.com/v1/projects/' . OSC::systemRegistry('FIREBASE_PROJECT_ID') . '/messages:send', array('json' => $message));

        if ($response->getStatusCode() !== 200) {
            throw new Exception($response->getReasonPhrase());
        }

        return array(
            'success' => null,
            'failure' => null
        );
    }

    /**
     * 
     * @param array $send_to
     * @param string $title
     * @param string $message
     * @param string $url
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function _legacySendMessage($send_to, $title, $message, $url, $options = null) {
        if (!is_array($options)) {
            $options = array();
        }

        $curl_options = array(
            'request_method' => 'POST',
            'headers' => array(
                'Authorization' => 'key=' . OSC::systemRegistry('FIREBASE_SERVER_KEY')
            ),
            'json' => array(
                $send_to[0] => $send_to[1],
                'data' => array(
                    'title' => $title,
                    'body' => $message,
                    'url' => $url,
                    'icon' => $options['icon']
                )
            )
        );
        
        if(isset($options['verify_code'])) {
            $curl_options['json']['data']['verify_code'] = $options['verify_code'];
        }

        $response = OSC::core('network')->curl('https://fcm.googleapis.com/fcm/send', $curl_options);

        if ($response['response_code'] != 200) {
            throw new Exception($response['response_reason_phrase']);
        }

        return $response['content'];
    }

}
