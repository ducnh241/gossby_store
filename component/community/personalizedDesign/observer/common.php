<?php

class Observer_PersonalizedDesign_Common
{
    public static function orderCollectDesign($params) {
        foreach ($params['line_items'] as $idx => $line_item) {
            foreach ($line_item->data['custom_data'] as $custom_data) {
                if ($custom_data['key'] == 'personalized_design') {
                    unset($params['line_items'][$idx]);

                    if ($custom_data['data']['design_url']) {
                        $params['design_urls'][$line_item->data['item_id']] = [
                            'key' => OSC::makeUniqid(),
                            'url' => $custom_data['data']['design_url']
                        ];
                    }
                }
            }
        }
    }

    public function resetCache($params) {
        try {
            $id = $params['model'] instanceof Model_PersonalizedDesign_Design ? $params['model']->getId() : 0;
            if (!empty($id)) {
                OSC::helper('core/cache')->insertResetCacheQueue(Helper_Core_Cache::MODEL_PERSONALIZED_DESIGN, $id);
            }
        } catch (Exception $exception) {

        }
    }
}