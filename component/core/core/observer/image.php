<?php

class Observer_Core_Image {

    public static function collectOptimizedImages(&$params) {
        return;

        $list_images_mapping = static::handleDataOptimizeImagesMapping();

        if (count($list_images_mapping) < 1) {
            return;
        }

        if (is_array($params['content'])) {
            $content = json_encode($params['content'], JSON_UNESCAPED_SLASHES);
            $params['content'] = OSC::decode(static::replaceContent($content, $list_images_mapping));
        } else {
            $params['content'] = static::replaceContent($params['content'], $list_images_mapping);
        }
    }

    protected static function handleDataOptimizeImagesMapping() {
        $output_cache_key = OSC::registry('output_cache_key');
        $result = OSC::helper('core/image')->getOptimizeImagesMapping();

        if ($output_cache_key) {
            $list_images_mapping_cache_key = 'getOptimizeImagesMapping|' . $output_cache_key['key'];

            if ($output_cache_key['flag_set']) {
                OSC::core('cache')->set($list_images_mapping_cache_key, $result, $output_cache_key['ttl']);
            } else {
                $result = OSC::core('cache')->get($list_images_mapping_cache_key);
            }
        }

        return $result;
    }

    protected function replaceContent($content, $list_images_mapping) {
        $not_optimized_images = [];

        $optimized_images_url = OSC::core('cache')->getMulti(array_keys($list_images_mapping));
        foreach ($list_images_mapping as $optimized_image_path => $value) {
            /* Check image optimized */
            $is_optimized = !empty($optimized_images_url[$optimized_image_path]) && file_exists(OSC_SITE_PATH . '/' . $optimized_image_path);

            if (!$is_optimized) {
                $not_optimized_images[$optimized_image_path] = $value;
            }
        }

        static::addQueueOptimizeImages($not_optimized_images);

        return $content;
    }

    protected function addQueueOptimizeImages($images) {
        $process_key = OSC::makeUniqid(null, true);

        $queries = [];

        $added_timestamp = time();

        foreach ($images as $optimize_path => $value) {
            $config = $value['config'];

            $queries[] = "('{$process_key}', '{$value['original_path']}', '{$optimize_path}', '{$config['extension']}', {$config['width']}, {$config['height']}, {$config['crop']}, 0, {$added_timestamp})";
        }

        $queries = implode(',', $queries);
        $queries = <<<EOF
INSERT IGNORE
INTO osc_core_image_optimize (process_key, original_path, optimized_path, extension, width, height, crop_flag, webp_flag, added_timestamp)
VALUES {$queries};                        
EOF;

        /* @var $DB OSC_Database */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        try {
            $DB->query($queries, null, 'insert_optimize_record');

            if ($DB->getNumAffected('insert_optimize_record') > 0) {
                OSC::core('cron')->addQueue('core/imageOptimize', ['process_key' => $process_key], ['skip_realtime', 'requeue_limit' => -1, 'estimate_time' => 60*30]);
            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();
        }
    }

}