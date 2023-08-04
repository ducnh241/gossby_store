<?php

class Controller_Page_React_Frontend extends Abstract_Frontend_ReactApiController
{
    public function __construct() {
        parent::__construct();
    }

    public function actionGetPageContent() {
        $id = intval($this->_request->get('id'));
        $slug = trim($this->_request->get('slug'));

        if ($id < 1 && empty($slug)) {
            $this->sendError('Page ID is incorrect', $this::CODE_BAD_REQUEST);
        }

        $cache_key = $id > 0 ? ['id' => $id] : ['slug' => $slug];
        $this->apiOutputCaching($cache_key, 0, ['ignore_location']);

        try {
            $page = $id > 0 ? OSC::model('page/page')->load($id) : OSC::helper('page/common')->loadPageBySlug($slug);
            $page_children = OSC::model('page/page')->getPageByParentId($page->data['page_id']);

            $meta_data = [
                'canonical'=> $page->getDetailUrl(),
                'url' => $page->getDetailUrl(),
                'seo_title' => $page->data['meta_tags']['title'] ?: $page->data['title'],
                'seo_image' =>  $page->getOgImageUrl(),
                'seo_description' => $page->data['meta_tags']['description'],
                'seo_keywords' => $page->data['meta_tags']['keywords']
            ];

            if ($page_children->collectionLength() > 0) {
                $result = [
                    'title' => $page->data['title'],
                    'meta_title' => $page->data['meta_tags']['title'] ?: $page->data['title'],
                    'meta_data' => $meta_data,
                    'breadcrumb' => [
                        [
                            'title' => $page->data['title'],
                            'link' => $page->getDetailUrl(),
                        ]
                    ],
                    'children' => OSC::helper('page/common')->formatPageApi($page_children)
                ];
            } else {
                try {
                    $parent = $page->getPageParent();

                    if ($parent) {
                        $breadcrumb[] = [
                            'title' => $parent->data['title'],
                            'link' => $parent->getDetailUrl(),
                        ];
                    }

                    $breadcrumb[] = [
                        'title' => $page->data['title'],
                        'link' => $page->getDetailUrl(),
                    ];

                } catch (Exception $ex) {
                    $breadcrumb = [
                        'title' => '',
                        'link' => '',
                    ];
                }

                $page_same_parents =  $page->data['parent_id'] != 0 ? OSC::model('page/page')->getPageByParentId($page->data['parent_id']) : null;

                $additional_data = OSC::decode($page->data['additional_data'], true);

                foreach ($additional_data as $key => $item) {
                    $additional_data[$key]['content'] = OSC::helper('page/common')->replaceInfoStore($item['content']);
                    $additional_data[$key]['image'] = OSC::core('aws_s3')->getStorageUrl($item['image']);
                }

                $result = [
                    'title' => $page->data['meta_tags']['title'] ?: $page->data['title'],
                    'link' => $page->getDetailUrl(),
                    'heading_tag' => $page->data['heading_tag'] ?: 'h4',
                    'meta_data' => $meta_data,
                    'image' => OSC::wrapCDN($page->getImageUrl()),
                    'content' => OSC::helper('page/common')->replaceInfoStore($page->data['content']),
                    'addition_data' => $additional_data ?? [],
                    'breadcrumb' => $breadcrumb,
                    'type' => $page->data['type']
                ];

                if($page_same_parents){
                    $result['list_page_same_parents'] = (OSC::helper('page/common')->formatPageApi($page_same_parents));
                }

                if($page->data['type'] == 'contact_us'){
                    $result['phone_support'] = OSC::helper('frontend/template')->getPhoneSupport();
                    $result['email_support'] = [
                        'email' =>  trim(OSC::helper('core/setting')->get('theme/contact/email')),
                        'help' =>  trim(OSC::helper('core/setting')->get('theme/contact/help_email'))
                    ];
                    $result['contact_us'] = [
                        'is_enable_noti' => intval(OSC::helper('core/setting')->get('contact_us/is_enable_noti')),
                        'is_enable_live_chat' => intval(OSC::helper('core/setting')->get('contact_us/is_enable_live_chat')),
                        'is_enable_email' => intval(OSC::helper('core/setting')->get('contact_us/is_enable_email')),
                        'is_enable_phone' => intval(OSC::helper('core/setting')->get('contact_us/is_enable_phone')),
                    ];
                }
            }

            $options = [];

            if ($page->data['meta_tags']['description']) {
                $options['sref_desc'] = 1;
            }

            $this->sendSuccess($result, $options);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetAllPages() {
//        $this->apiOutputCaching('getAllPages', 0, ['ignore_location']);

        try {
            $result = OSC::model('page/page')
                ->getCollection()
                ->addField('page_id', 'slug')
                ->addCondition('published_flag', 1)
                ->load()
                ->toArray();

            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}