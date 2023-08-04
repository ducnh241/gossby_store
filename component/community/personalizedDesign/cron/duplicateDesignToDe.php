<?php

use Aws\CommandPool;
use Aws\S3\S3Client;

class Cron_PersonalizedDesign_DuplicateDesignToDe extends OSC_Cron_Abstract {
    protected $_s3_product_images = [];

    /**
     * @throws Exception
     */
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $target_store_id = $params['target_store_id'];

        $store_id = OSC::getStoreInfo()['store_id'];

        if (is_array($params['design_ids'])) {
            foreach ($params['design_ids'] as $design_id) {
                try {
                    $design = OSC::model('personalizedDesign/design')->load($design_id);
                    $this->executeCopy($design, $target_store_id, $store_id);
                } catch (Exception $ex) {}
            }
        }
    }

    /**
     * @param $design Model_PersonalizedDesign_Design
     * @return void
     */
    private function executeCopy(Model_PersonalizedDesign_Design $design, $target_store_id, $store_id) {
        $data = $design->data;

        foreach (array_column($data['design_data']['image_data'], 'url') as $value) {
            $thumb = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $value);
            $preview = preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $value);
            $this->_s3_product_images[$store_id . '/storage/' . $value] = $target_store_id . '/storage/' . $value;
            $this->_s3_product_images[$store_id . '/storage/' . $thumb] = $target_store_id . '/storage/' . $thumb;
            $this->_s3_product_images[$store_id . '/storage/' . $preview] = $target_store_id . '/storage/' . $preview;
        }

        if (preg_match_all('#"image":\s*"(https://gossby.com/storage[^"]+)"#is', json_encode($data['design_data'], JSON_UNESCAPED_SLASHES), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $source = str_replace('https://gossby.com', $store_id, $match[1]);
                $target = str_replace('https://gossby.com', $target_store_id, $match[1]);
                $this->_s3_product_images[$source] = $target;
            }
        }

        if (preg_match_all('#"image":\s*"(personalizedDesign[^"]+)"#is', json_encode($data['design_data'], JSON_UNESCAPED_SLASHES), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (strpos($match[1], '\/') !== false) {
                    $match[1] = str_replace('\/', '/', $match[1]);
                }

                $source = $store_id . '/storage/' . $match[1];
                $target = $target_store_id . '/storage/' . $match[1];
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
                    $target = $target_store_id . '/storage/personalizedDesign/fonts/' . $font_key . '/' . $font_key . $ext;
                    $this->_s3_product_images[$source] = $target;
                }
            }
        }
        OSC::logFile(OSC::encode($this->_s3_product_images));

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
