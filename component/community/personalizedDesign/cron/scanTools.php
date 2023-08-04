<?php

class Cron_PersonalizedDesign_ScanTools extends OSC_Cron_Abstract {
    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     * @throws Exception
     */
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        //$this->_convertDuplicateImageDesign();
        $this->_analyticHairsOfDesign();
    }

    protected function _convertDuplicateImageDesign() {
        try {
            $scanned_designs = [];
            $start_time = microtime(true);
            $convert_images = OSC::decode(file_get_contents(OSC_ROOT_PATH . '/convertGBImages.json'));

            $designs = OSC::model('personalizedDesign/design')
                ->getCollection()
                ->addField('design_id')
                ->load();

            foreach ($designs as $design) {
                $flag_update = false;
                $existed_image_url = [];
                $replace_image_keys = [];
                $new_design_data = null;

                $DB = OSC::core('database');

                $DB->select('design_id, design_data', 'personalized_design', "design_id = {$design->data['design_id']}", null , 1, 'fetch_design');

                $design_data = $DB->fetchArray('fetch_design');

                $DB->free('fetch_design');

                $design_id = intval($design_data['design_id']);
                $new_design_data = OSC::decode($design_data['design_data']);

                foreach ($new_design_data['image_data'] as $key => $value) {
                    if (array_key_exists($value['url'], $convert_images)) {
                        $flag_update = true;
                        $image_url = $convert_images[$value['url']];
                        if (empty($existed_image_url[$image_url])) {
                            $existed_image_url[$image_url] = $key;
                            $new_design_data['image_data'][$key]['url'] = $image_url;
                        } else {
                            $replace_image_keys[$existed_image_url[$image_url]][] = $key;
                            unset($new_design_data['image_data'][$key]);
                        }
                    }
                }

                if ($flag_update) {
                    /*$new_design_data = OSC::encode($new_design_data);
                    foreach($replace_image_keys as $replace => $searches) {
                        $new_design_data = str_replace($searches, $replace, $new_design_data);
                    }
                    $new_design_data = OSC::decode($new_design_data);*/
                    //Comment for first time run test
                    //$model->setData('design_data', $new_design_data)->save();
                    $scanned_designs[$design_id] = array_values($replace_image_keys);
                }
            }
            OSC::logFile(OSC::encode($scanned_designs), 'preTestConvertedDesign');

            $execute_time = microtime(true) - $start_time;
            $message = "ConvertDuplicateImageDesign run successfully in {$execute_time} seconds.";
            OSC::helper('core/telegram')->sendMessage($message, '-409036884');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _analyticHairsOfDesign() {
        try {
            $start_time = microtime(true);

            $designs = OSC::model('personalizedDesign/design')
                ->getCollection()
                ->addField('design_id')
                ->addCondition('design_id', 4112, OSC_Database::OPERATOR_GREATER_THAN)
                ->load();

            $mongodb = OSC::core('mongodb');
            foreach ($designs as $design) {
                $design_id = $design->data['design_id'];

                $DB = OSC::core('database');

                $DB->select('design_data', 'personalized_design', "design_id = {$design_id}", null , 1, 'fetch_design');
                $row = $DB->fetchArray('fetch_design');
                $DB->free('fetch_design');

                $output = [];
                $design_data = OSC::decode($row['design_data']);
                $data = OSC::helper('personalizedDesign/dataScanProcess')->extractPersonalizedFormData($design_data);

                $this->_analyticsHairData($data['components'], $output);

                $mongodb->insert('hair_analytics_v2', [
                    'design_id' => $design_id,
                    'hair_data' => array_values($output)
                ], 'product');
            }

            $execute_time = microtime(true) - $start_time;
            $message = "AnalyticHairsOfDesign run successfully in {$execute_time} seconds.";
            OSC::helper('core/telegram')->sendMessage($message, '-409036884');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _analyticsHairData($components, &$output) {
        foreach ($components as $component) {
            if ($component['layer'] === 'Quote') {
                continue;
            }

            if ($component['component_type'] === 'imageSelector' &&
                strpos(strtolower($component['title']), 'hair') !== false
            ) {
                $output[$component['title']]['title'] = $component['title'];
                foreach (array_column($component['images'], 'id') as $image_id) {
                    if (!in_array($image_id, $output[$component['title']]['image_ids'])) {
                        $output[$component['title']]['image_ids'][] = $image_id;
                    }
                }
            }

            foreach ($component['scenes'] as $scenes) {
                $this->_analyticsHairData($scenes['components'], $output);
            }
        }
    }

}
