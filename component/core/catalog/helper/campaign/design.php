<?php

class Helper_Catalog_Campaign_Design extends OSC_Object {

    public function getSegmentSources($segment_sources, $personalized_design_options = [], $option = []) {
        static $cached = ['image' => [], 'personalized' => []];

        $image_ids = [];
        $personalized_design_ids = [];

        foreach ($segment_sources as $segment) {
            if ($segment['source']['type'] == 'personalizedDesign') {
                $personalized_design_ids[] = $segment['source']['design_id'];
            } else if ($segment['source']['type'] == 'image') {
                $image_ids[] = $segment['source']['image_id'];
            }
        }

        foreach (['personalized' => $personalized_design_ids, 'image' => $image_ids] as $key => $value) {
            if (count($value) > 0) {
                $need_to_load = [];

                foreach ($value as $design_id) {
                    if (!isset($cached[$key][$design_id])) {
                        $need_to_load[$key][] = $design_id;
                    }
                }

                foreach ($need_to_load as $_key => $_value){
                    $collection = null;
                    if ($key == 'personalized') {
                        $collection = OSC::model('personalizedDesign/design')->getCollection()->setLimit(count($_value))->load($_value);
                    }elseif ($key == 'image'){
                        $collection = OSC::model('catalog/campaign_imageLib_item')->getCollection()->setLimit(count($_value))->load($_value);
                    }

                    if ($collection != null) {
                        foreach ($collection as $model) {
                            $cached[$_key][$model->getId()] = $model;
                        }
                    }
                }
            }
        }

        foreach ($segment_sources as $segment_key => $segment) {
            if ($segment['source']['type'] == 'personalizedDesign') {
                $personalized_design =  $cached['personalized'][$segment['source']['design_id']];

                if (!($personalized_design instanceof Model_PersonalizedDesign_Design) || intval($personalized_design->getId())< 1) {
                    try {
                        $personalized_design = OSC::model('personalizedDesign/design')->load($segment['source']['design_id']);
                    } catch (Exception $ex) {
                        throw new Exception('Personalized design #' . $segment['source']['design_id'] . ' ' . $ex->getMessage());
                    }

                    $cached['personalized'][$segment['source']['design_id']] = $personalized_design;
                }

                $personalized_options = [];

                if (isset($segment['source']['option_default_values']['options'])) {
                    $personalized_options = is_array($segment['source']['option_default_values']['options']) ? $segment['source']['option_default_values']['options'] : OSC::decode($segment['source']['option_default_values']['options']);
                }

                unset($segment['source']['option_default_values']);

                if (isset($personalized_design_options[$segment['source']['design_id']])) {
                    $personalized_options = $personalized_design_options[$segment['source']['design_id']];
                }

                if (in_array('render_mockup', $option, true)) {
                    OSC::register('default_spotify' , 1);
                }

                $segment['source']['svg'] = OSC::helper('personalizedDesign/common')->renderSvg($personalized_design, $personalized_options, $option) ;
                $segment['source']['config'] = $personalized_options;
                $segment['source']['config_preview'] = OSC::helper('personalizedDesign/common')->fetchConfigPreview($personalized_design, $personalized_options);
            } else if ($segment['source']['type'] == 'image') {
                /* @var $image Model_Catalog_Campaign_ImageLib_Item */
                $image = $cached['image'][$segment['source']['image_id']];

                if (!($image instanceof Model_Catalog_Campaign_ImageLib_Item) || intval($image->getId()) < 1) {
                    try {
                        $image = OSC::model('catalog/campaign_imageLib_item')->load($segment['source']['image_id']);
                    } catch (Exception $ex) {
                        throw new Exception('Image #' . $segment['source']['image_id'] . ' ' . $ex->getMessage());
                    }

                    $cached['image'][$segment['source']['image_id']] = $image;
                }

                $segment['source']['file_name'] = $image->data['filename'];
            }

            $segment_sources[$segment_key] = $segment;
        }

        return $segment_sources;
    }

    public function getSegmentSourcesSemitest($segment_sources, $personalized_design_options = [], $option = []) {
        static $cached_semi = ['image' => [], 'personalized' => []];

        $image_ids = [];
        $personalized_design_ids = [];

        foreach ($segment_sources as $segment) {
            if ($segment['type'] == 'personalizedDesign') {
                $personalized_design_ids[] = $segment['design_id'];
            }
        }

        foreach (['personalized' => $personalized_design_ids, 'image' => $image_ids] as $key => $value) {
            if (count($value) > 0) {
                $need_to_load = [];

                foreach ($value as $design_id) {
                    if (!isset($cached_semi[$key][$design_id])) {
                        $need_to_load[$key][] = $design_id;
                    }
                }

                foreach ($need_to_load as $_key => $_value) {
                    $collection = null;
                    if ($key == 'personalized') {
                        $collection = OSC::model('personalizedDesign/design')->getCollection()->setLimit(count($_value))->load($_value);
                    }

                    if ($collection != null) {
                        foreach ($collection as $model) {
                            $cached_semi[$_key][$model->getId()] = $model;
                        }
                    }
                }
            }
        }

        foreach ($segment_sources as $segment_key => $segment) {
            if ($segment['type'] == 'personalizedDesign') {
                $personalized_design = $cached_semi['personalized'][$segment['design_id']];

                if (!($personalized_design instanceof Model_PersonalizedDesign_Design) || intval($personalized_design->getId()) < 1) {
                    try {
                        $personalized_design = OSC::model('personalizedDesign/design')->load($segment['design_id']);
                    } catch (Exception $ex) {
                        throw new Exception('Personalized design #' . $segment['design_id'] . ' ' . $ex->getMessage());
                    }

                    $cached_semi['personalized'][$segment['design_id']] = $personalized_design;
                }

                $personalized_options = [];

                if (isset($segment['option_default_values']['options'])) {
                    $personalized_options = is_array($segment['option_default_values']['options']) ? $segment['option_default_values']['options'] : OSC::decode($segment['option_default_values']['options']);
                }

                unset($segment['option_default_values']);

                if (isset($personalized_design_options[$segment['design_id']])) {
                    $personalized_options = $personalized_design_options[$segment['design_id']];
                }

                try {
                    Observer_Catalog_Campaign::validatePersonalizedDesign($personalized_design, $personalized_options);
                } catch (Exception $ex) {
                    return [
                        'design_ids' => null,
                        'message' => $ex->getMessage(),
                        'status' => false
                    ];
                }

                $segment['svg'] = OSC::helper('personalizedDesign/common')->renderSvg($personalized_design, $personalized_options, $option);
                $segment['config'] = $personalized_options;
                $segment['config_preview'] = OSC::helper('personalizedDesign/common')->fetchConfigPreview($personalized_design, $personalized_options);
                $segment['design_last_update'] = $personalized_design->data['modified_timestamp'];
            }

            $segment_sources[$segment_key] = $segment;
        }

        return $segment_sources;
    }

    public function getSegmentSourcesCrossSell($segment_sources, $image_url) {
        foreach ($segment_sources as $segment_key => $segment) {
            if ($segment['source']['type'] == 'image') {
                $segment['source']['file_name'] = $image_url;
            }

            $segment_sources[$segment_key] = $segment;
        }

        return $segment_sources;
    }

    public function getSegmentRenderData($print_template, $segment_sources) {
        $segments = $print_template->data['config']['segments'];

        $segment_counter = 0;

        foreach($segments as $segment_key => $segment) {
            unset($segment['builder_config']);

            if(isset($segment_sources[$segment_key])) {
                $segment_counter ++;
                $segment['source'] = $segment_sources[$segment_key]['source'];
            }

            $segments[$segment_key] = $segment;
        }

        if($segment_counter < 1) {
            throw new Exception('Missing source for all segments');
        }

        return $segments;
    }

    /**
     * @param Model_Catalog_Order_Item $line_item
     * @param Model_Catalog_PrintTemplate $print_template
     * @param $segment_sources
     * @param $option_values
     * @return mixed
     * @throws OSC_Database_Model_Exception
     */
    public function getDesignRenderData(Model_Catalog_Order_Item $line_item, Model_Catalog_PrintTemplate $print_template, $segment_sources, $option_values) {
        $data = $print_template->data['config'];

        $data['segments'] = $this->getSegmentRenderData($print_template, $segment_sources);

        $mockup_rel = $print_template->getDefaultMockupRel();

        if ($mockup_rel) {
            $map_mockups = [];

            foreach ($segment_sources as $key => $segment) {
                if (is_array($segment['source']) && count($segment['source']) > 0) {
                    $map_mockups[$key] = 'system';
                }
            }

            $data['mockup_file_name'] = 'catalog/campaign/order/' . date('Ymd', $line_item->data['added_timestamp']) . '/' . $line_item->data['order_id'] . '/' . $line_item->data['item_id'] . '/' . $line_item->data['added_timestamp'] . '.jpg';

            $additional_data = $mockup_rel->data['additional_data'];

            //data in mockups is set as "{"map_mockups":{"42":{"front":"system"}}}"
            if (isset($additional_data['map_mockups'][$print_template->getId()]) && $map_mockups[array_key_first($additional_data['map_mockups'][$print_template->getId()])] != array_values($additional_data['map_mockups'][$print_template->getId()])[0]) {
                $mockup_rel = OSC::model('catalog/printTemplate_mockupRel')->getCollection()
                    ->addCondition('print_template_id', $print_template->getId())
                    ->addCondition('is_default_mockup', 1)
                    ->addCondition('status', 1)
                    ->addCondition('id', $mockup_rel->getId(), OSC_Database::OPERATOR_NOT_EQUAL)
                    ->setLimit(1)
                    ->load()
                    ->getItem();
            }

            if ($mockup_rel instanceof Model_Catalog_PrintTemplate_MockupRel && $mockup_rel->getId() > 0) {
                $data['mockup_commands'] = OSC::helper('catalog/campaign_mockup_command')->parse($mockup_rel->getMockup()->data['config'], OSC::helper('catalog/campaign_mockup')->preCommandParams($data['segments'], $option_values));
            }
        }

        if (!isset($data['mockup_file_name'])) {
            $data['mockup_file_name_default'] = 'catalog/campaign/order/' . date('Ymd', $line_item->data['added_timestamp']) . '/' . $line_item->data['order_id'] . '/' . $line_item->data['item_id'] . '/' . $line_item->data['added_timestamp'] . '.jpg';
        }

        unset($data['preview_config']);

        return $data;
    }

    protected $_personalized_designs = [];

    /**
     * @param $personalized_design_ids
     * @return Model_PersonalizedDesign_Design_Collection|OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */
    public function loadPersonalizedDesigns($personalized_design_ids) {
        if (count($personalized_design_ids) < 1) {
            return OSC::model('personalizedDesign/design')->getNullCollection();
        }

        $key = implode('_', $personalized_design_ids);

        if (isset($this->_personalized_designs[$key]) &&
            $this->_personalized_designs[$key] instanceof Model_PersonalizedDesign_Design_Collection
        ) {
            return $this->_personalized_designs[$key];
        }

        $this->_personalized_designs[$key] = OSC::model('personalizedDesign/design')
            ->getCollection()
            ->load($personalized_design_ids);

        return $this->_personalized_designs[$key];
    }

    protected $_last_update_personalized_designs = [];

    /**
     * @param $personalized_design_ids
     * @return Model_PersonalizedDesign_Design_Collection|OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getLastUpdatePersonalizedDesigns($personalized_design_ids) {
        if (empty($this->_last_update_personalized_designs)) {
            $this->_last_update_personalized_designs = OSC::model('personalizedDesign/design')
                ->getCollection()
                ->addCondition('design_id', $personalized_design_ids, OSC_Database::OPERATOR_IN)
                ->addField('design_id', 'modified_timestamp')
                ->load()
                ->toArray();
        }

        return $this->_last_update_personalized_designs;
    }

    public function checkValidateByLastUpdateDesign($design_ids, $compare_data) {
        $last_update_personalized_designs = $this->getLastUpdatePersonalizedDesigns($design_ids);

        if (empty($last_update_personalized_designs)) {
            return false;
        }

        foreach ($last_update_personalized_designs as $design_data) {
            if (!isset($compare_data[$design_data['design_id']]) ||
                $compare_data[$design_data['design_id']] < $design_data['modified_timestamp']
            ) {
                return false;
            }
        }

        return true;
    }
}
