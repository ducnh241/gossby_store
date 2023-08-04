<?php

class Cron_PersonalizedDesign_RenderDesignSvgBeta extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 200;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'renderDesignSvgBeta'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {

                $queue_data = $model->data['queue_data'];

                $line_item_id = $queue_data['line_item_id'];
                $design_id = $queue_data['design_id'];
                $svg_content = $queue_data['svg_content'];
                $svg_clip_art = $queue_data['svg_clip_art_content'];
                $svg_preview_content = $queue_data['svg_preview_content'];

                $request_data = [
                    'svg_content' => $svg_content,
                    'svg_clip_art_content' => $svg_clip_art,
                    'svg_preview_content' => $svg_preview_content,
                    'sync_image_storage' => 1,
                    'line_item_id' => $line_item_id,
                    'design_id' => $design_id
                ];

                if (!empty($queue_data['width']) && !empty($queue_data['height'])) {
                    $request_data['is_resize'] = 1;
                    $request_data['width'] = $queue_data['width'];
                    $request_data['height'] = $queue_data['height'];
                }

                $store_info = OSC::getStoreInfo();

                $response = OSC::core('network')->curl(
                    OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/renderDesignSvgBetaOrderItem', [
                    'timeout' => 900,
                    'connect_timeout' => 10,
                    'headers' => [
                        'Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])
                    ],
                    'json' => $request_data
                ]);

                if ($response['content']['result'] == 'ERROR') {
                    throw new Exception($response['content']['message']);
                }

                $model->delete();

            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'modified_timestamp' => time()])->save();
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
