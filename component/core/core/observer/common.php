<?php

class Observer_Core_Common {

    public static function initialize() {
        $request = OSC::core('request');

        $ab_test_key = $request->get('__ab_test_k');
        if ($ab_test_key) {
            if (OSC::helper('user/authentication')->getMember()->getId()) {
                OSC::setABTestValue($ab_test_key, $request->get('__ab_test_v'));
            } else {
                throw new Exception('You need login to use AB TEST function');
            }
        }

        OSC::core('date')->setTimezone(OSC::helper('core/setting')->get('core/timezone'));

        if (OSC::helper('user/authentication')->getMember()->isRoot() && isset($_REQUEST['_write_debug_log']) && $_REQUEST['_write_debug_log'] == 1) {
            OSC::core('debug')->setWriteLogFlag(true);
        }

        if ($request->get('currency')) {
            OSC::cookieSetCrossSite('currency_code', $request->get('currency'));
        }

    }

    public static function parseRedirectUrl($request_string) {
        if (preg_match('/^(backend)$/i', $request_string, $matches)) {
            try {
                OSC_Controller::redirect(OSC::$base_url . '/backend/index');
            } catch (Exception $ex) {
            }
        }
    }

    public function logModelMongoDB(array $params = []) {

        try {
            $action = $params['action'];
            $model = $params['model'];

            if (!$model instanceof Abstract_Core_Model) {
                return;
            }

            try {
                $user = OSC::helper('user/authentication')->getMember();
            } catch (Exception $ex) {
                throw new Exception('User is not exist!');
            }

            if (!($user instanceof Model_User_Member) || $user->getId() < 1) {
                return;
            }

            $data = [
                'user_id' => $user->data['member_id'],
                'user_name' => $user->data['username'],
                'email' => $user->data['email'],
                'table_name' => $model->getTableName(true),
                'action' => $action,
                'old_data' => null,
                'new_data' => null,
                'record_id' => $model->getId(),
                'added_timestamp' => time(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            switch ($action) {
                case 'INSERT': {
                    $data['old_data'] = null;
                    $data['new_data'] = json_encode($model->data);
                } break;
                case 'UPDATE': {
                    $data['old_data'] = $model->data_old ? json_encode($model->data_old) : null;
                    $data['new_data'] = json_encode($model->data);
                } break;
                case 'DELETE': {
                    $data['old_data'] = json_encode($model->data);
                    $data['new_data'] = null;
                } break;

            }

            $mongodb = OSC::core('mongodb');
            $mongodb->insert('model_log', $data, 'product');
        } catch (Exception $ex) {}
    }
}