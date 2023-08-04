<?php

class Cron_Core_ImageOptimize extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        OSC::makeDir(OSC_VAR_PATH . '/opt_images');
        $input = [
            'root_path' => OSC_SITE_PATH,
            'process_key' => $params['process_key'],
            'object_prefix' => OSC::core('aws_s3')->getObjectPrefix(),
            'cache_prefix' => OSC_SITE_KEY
        ];
        $command = "python " . dirname(__FILE__) . "/imageOptimize.py -i '" . json_encode($input, JSON_UNESCAPED_SLASHES) . "'";

        exec($command);
    }

}
