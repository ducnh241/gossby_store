<?php

class Controller_Filter_GiftFinder extends Abstract_Backend_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('filter/gift_finder');

        $this->getTemplate()
            ->setCurrentMenuItemKey('filter/gift_finder')
            ->setPageTitle('Gift Finder');
    }

    public function actionIndex()
    {
        $this->forward('*/*/config');
    }

    public function actionConfig()
    {
        $this->getTemplate()->setCurrentMenuItemKey('filter/gift_finder')
            ->addBreadcrumb('Gift Finder', $this->getUrl('index'));
        $this->getTemplate()->setPageTitle('Gift Finder');

        $this->checkPermission('filter/gift_finder');

        $default_step = [
            'parent_tag' => 0,
            'title' => '',
            'show_image' => 0,
            'children_tag' => []
        ];

        $filter_tags = OSC::helper('filter/common')->getParentTagsAndAllTheirChildren();

        $step_config = OSC::helper('core/setting')->get('filter_search/gift_finder/config') ?: [
            '1' => $default_step,
            '2' => $default_step
        ];
        $_step_config = [];
        $errors = [];
        $enable_gift_finder = OSC::helper('core/setting')->get('filter_search/gift_finder/enable');

        if ($this->_request->get('submit_form', null)) {

            if (is_array($this->_request->get('step_config'))) {

                $step_config = $this->_request->get('step_config');
                $count = 1;
                foreach ($step_config as $key => $step) {
                    $children_tags = [];
                    if (isset($filter_tags[$step['parent_tag']]) && is_array($step['children_tag'])) {
                        foreach ($step['children_tag'] as $tag_id) {
                            $_tag = [];
                            foreach ($filter_tags[$step['parent_tag']]['children'] as $children_tag) {
                                if ($children_tag['id'] == intval($tag_id)) {
                                    $_tag = $children_tag;
                                    break;
                                }
                            }

                            $children_tags[] = [
                                'id' => $tag_id,
                                'title' => !empty($_tag) ? $_tag['title'] : ''
                            ];
                        }
                    }
                    
                    $step['parent_tag'] = intval($step['parent_tag']);
                    $step['show_image'] = isset($step['show_image']) ? 1 : 0;
                    $step['children_tag'] = $children_tags;

                    $_step_config[$count++] =  $step;
                }
            }

            $errors = $this->_validateStepForm($_step_config);

            $enable_gift_finder = $this->_request->get('enable_gift_finder') ? 1 : 0;

            if (!count($errors)) {
                OSC::helper('core/setting')->set('filter_search/gift_finder/config', $_step_config);
                OSC::helper('core/setting')->set('filter_search/gift_finder/enable', $enable_gift_finder);
                $this->addMessage('Save Gift Finder successfully!');
            } else {
                $this->addErrorMessage($errors);
            }
        } else {
            $_step_config = $step_config;
        }

        $output_html = $this->getTemplate()->build('filter/giftFinder/list', [
            'step_config' => $_step_config,
            'filter_tags' => $filter_tags,
            'enable_gift_finder' => intval($enable_gift_finder),
        ]);

        $this->output($output_html);
    }

    protected function _validateStepForm($step_config) {
        $errors = [];

        foreach($step_config as $key => $step) {
            if ($step['parent_tag'] == 0 || count($step['children_tag']) == 0) {
                $errors[] = 'Please choose tag for Step #' . $key;
            }

            if (empty($step['title'])) {
                $errors[] = 'Please enter title for Step #' . $key;
            }
        }

        return $errors;
    }
}