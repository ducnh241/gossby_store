<?php

use Aws\CommandPool;
use Aws\S3\S3Client;

class Cron_PersonalizedDesign_DuplicateDesignToD3 extends OSC_Cron_Abstract {
    protected $_s3_product_images = [];

    /**
     * @throws Exception
     */
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $amz_d3_id = URL_AMZ_D3_ID;
        $amz_d3_url = URL_AMZ_D3_SERVICE;
        $amz_d3_secret_key = URL_AMZ_D3_SECRET_KEY;
        $store_id = OSC::getStoreInfo()['store_id'];

        try {
            $model = OSC::model('personalizedDesign/design')->load($params['design_id']);
            $data = $model->data;
            unset($data[$model->getPkFieldName()]);
            unset($data[$model->getUkeyFieldName()]);
            /* Unset because feature upload PSD to Personalized Design */
            unset($data['meta_data']);
//            unset($data['design_data']);
            $data['locked_flag'] = 0;
            $data['is_draft'] = 1;
            $data['added_timestamp'] = time();
            $data['modified_timestamp'] = time();
            $data['member_id'] = $params['member_id'];
            $data['type_flag'] = Model_PersonalizedDesign_Design::TYPE_DESIGN_AWZ;
            $data['design_cloned_id'] = $params['design_id'];

            $request_params = ['data' => $data];
            OSC::core('network')->curl($amz_d3_url . '/personalizedDesign/api/duplicateDesign', [
                'timeout' => 600,
                'headers' => [
                    'Osc-Api-Token' => OSC_Controller::makeRequestChecksum(OSC::encode($request_params), $amz_d3_secret_key)
                ],
                'json' => $request_params
            ]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        foreach (array_column($data['design_data']['image_data'], 'url') as $value) {
            $thumb = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $value);
            $preview = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $value);
            $this->_s3_product_images[$store_id . '/storage/' . $value] = $amz_d3_id . '/storage/' . $value;
            $this->_s3_product_images[$store_id . '/storage/' . $thumb] = $amz_d3_id . '/storage/' . $thumb;
            $this->_s3_product_images[$store_id . '/storage/' . $preview] = $amz_d3_id . '/storage/' . $preview;
        }

        if (preg_match_all('#"image":\s*"(https://gossby.com/storage[^"]+)"#is', json_encode($data['design_data'], JSON_UNESCAPED_SLASHES), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $source = str_replace('https://gossby.com', $store_id, $match[1]);
                $target = str_replace('https://gossby.com', $amz_d3_id, $match[1]);
                $this->_s3_product_images[$source] = $target;
            }
        }

        if (preg_match_all('#"image":\s*"(personalizedDesign[^"]+)"#is', json_encode($data['design_data'], JSON_UNESCAPED_SLASHES), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (strpos($match[1], '\/') !== false) {
                    $match[1] = str_replace('\/', '/', $match[1]);
                }

                $source = $store_id . '/storage/' . $match[1];
                $target = $amz_d3_id . '/storage/' . $match[1];
                $this->_s3_product_images[$source] = $target;
            }
        }

        if (preg_match_all('#"font_name":\s*"([^"]+)"#is', json_encode($data['design_data'], JSON_UNESCAPED_SLASHES), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $font_name = $match[1];

                $font_key = preg_replace('/^(.+)\.[a-zA-Z0-9]+$/', '\\1', $font_name);
                $font_key = preg_replace('/([^a-z0-9]|_{2,})/i', '_', $font_key);
                $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
                $font_key = strtolower($font_key);

                foreach (['.ttf', '.woff2', '.svg', '.css'] as $ext) {
                    $source = $store_id . '/storage/personalizedDesign/fonts/' . $font_key . '/' . $font_key . $ext;
                    $target = $amz_d3_id . '/storage/personalizedDesign/fonts/' . $font_key . '/' . $font_key . $ext;
                    $this->_s3_product_images[$source] = $target;
                }
            }
        }

        $this->_batchCopyS3Objects($this->_s3_product_images);
    }

    protected $_client = null;
    protected function _getS3Client() {
        if ($this->_client !== null) {
            return $this->_client;
        }

        $config = OSC::systemRegistry('aws_s3');
        $this->_client = new S3Client([
            'region' => $config['region'],
            'version' => $config['version'],
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret']
            ]
        ]);

        return $this->_client;
    }

    protected $_s3_bucket = null;
    protected function _getS3Bucket() {
        if ($this->_s3_bucket !== null) {
            return $this->_s3_bucket;
        }

        $config = OSC::systemRegistry('aws_s3');
        $this->_s3_bucket = $config['bucket'];

        return $this->_s3_bucket;
    }

    protected function _batchCopyS3Objects($params) {
        $batch = [];
        $s3_client = $this->_getS3Client();
        $s3_bucket = $this->_getS3Bucket();

        foreach ($params as $source => $target) {
            $batch[] = $s3_client->getCommand('CopyObject', [
                'Bucket' => $s3_bucket,
                'Key' => $target,
                'CopySource' => "{$s3_bucket}/{$source}",
            ]);
        }

        try {
            CommandPool::batch($s3_client, $batch);
        } catch (Exception $ex) {
        }
    }

}
