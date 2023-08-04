<?php

class Helper_Master_Common extends OSC_Object {

    public function callApi($request_path, $request_params = []) {
        $store_info = OSC::getStoreInfo();

        $response = OSC::core('network')->curl(trim($store_info['master_store_url']) . '/' . $request_path, ['timeout' => 10, 'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_params), $store_info['secret_key'])], 'json' => $request_params]);

        if (!is_array($response['content']) || !isset($response['content']['result'])) {
            throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
        }

        if ($response['content']['result'] != 'OK') {
            $exception_object = new Exception($response['content']['message'], $response['response_code'] ?? 0);
            $exception_object->response_content = $response['content'];

            throw $exception_object;
        }

        return $response['content']['data'];
    }

}
