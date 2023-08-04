<?php

class Helper_Frontend_Common extends OSC_Object
{
    protected function _indexGetModel($lang_key, $module_key, $item_group, $item_id)
    {
        /* @var $model Model_Frontend_Index */
        $model = OSC::model('frontend/index');

        $lang_key = $model->processLangKey($lang_key);

        if ($lang_key === false) {
            throw new Exception('Lang key is incorrect');
        }

        try {
            $model->loadByUKey($model->generateUkey($module_key, $item_group, $item_id, $lang_key));
        } catch (Exception $ex) {
            if ($ex->getCode() !== 404) {
                throw new Exception($ex->getMessage());
            }
        }

        return $model;
    }

    /**
     *
     * @param mixed $lang_key
     * @param string $module_key
     * @param string $item_group
     * @param int $item_id
     * @param string $keywords
     * @param mixed $index_data
     * @return boolean
     */
    public function indexAdd($lang_key, $module_key, $item_group, $item_id, $keywords, $options = array())
    {
        try {
            $model = $this->_indexGetModel($lang_key, $module_key, $item_group, $item_id);
        } catch (Exception $ex) {
            return false;
        }

        $default = array(
            'item_data' => array(),
            'filter_data' => array()
        );

        if (!is_array($options)) {
            $options = array();
        }

        foreach ($default as $k => $v) {
            if (!isset($options[$k])) {
                $options[$k] = $v;
            }
        }

        try {
            $model->setData(array(
                'lang_key' => $lang_key,
                'module_key' => $module_key,
                'item_group' => $item_group,
                'item_id' => $item_id,
                'item_data' => $options['item_data'],
                'filter_data' => $options['filter_data'],
                'keywords' => $keywords
            ))->save();
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param mixed $lang_key
     * @param string $module_key
     * @param string $item_group
     * @param int $item_id
     * @return boolean
     */
    public function indexDelete($lang_key, $module_key, $item_group, $item_id)
    {
        try {
            $model = $this->_indexGetModel($lang_key, $module_key, $item_group, $item_id);

            if ($model->getId() > 0) {
                $model->delete();
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    protected $_common_layout = null;

    public function getCommonLayout()
    {
        if ($this->_common_layout === null) {
            $common_layout = [];

            OSC::core('observer')->dispatchEvent('reactjs_collect_common_layout', ['data' => &$common_layout]);
            $shop = OSC::getShop()->data;

            $common_layout = array_merge($common_layout, [
                'timezone' => $this->_getSetting('core/timezone'),
                'currency' => [
                    'enable_convert_currency' => $this->_getSetting('catalog/convert_currency/enable'),
                    'currency_rate_list' => OSC::helper('catalog/common')->getPopularPriceExchangeRate(),
                ],
                'theme_config' => [
                    'logo' => OSC::decode(OSC::encode(OSC::helper('frontend/template')->getLogo())),
                    'logo_small' => OSC::decode(OSC::encode(OSC::helper('frontend/template')->getLogo(false, true))),
                    'favicon' => OSC::decode(OSC::encode(OSC::helper('frontend/template')->getFavicon())),
                    'meta_data' => [
                        'base_url' => OSC_FRONTEND_BASE_URL,
                        'title' => $this->_getSetting('theme/metadata/title'),
                        'keywords' => $this->_getSetting('theme/metadata/keyword'),
                        'description' => $this->_getSetting('theme/metadata/description'),
                        'seo_image' => OSC::helper('frontend/template')->getMetaImage()->url,
                        'google_site_verification' => $this->_getSetting('tracking/google/verification')
                    ],
                    'social_link' => [
                        [
                            'channel' => 'facebook',
                            'link' => $this->_getSetting('theme/social/facebook'),
                        ],
                        [
                            'channel' => 'facebook_group',
                            'link' => $this->_getSetting('theme/social/facebook_group'),
                        ],
                        [
                            'channel' => 'twitter',
                            'link' => $this->_getSetting('theme/social/twitter'),
                        ],
                        [
                            'channel' => 'youtube',
                            'link' => $this->_getSetting('theme/social/youtube'),
                        ],
                        [
                            'channel' => 'instagram',
                            'link' => $this->_getSetting('theme/social/instagram'),
                        ],
                        [
                            'channel' => 'pinterest',
                            'link' => $this->_getSetting('theme/social/pinterest'),
                        ]
                    ],
                    'live_chat' => [
                        'enable' => OSC::helper('core/setting')->get('theme/live_chat/enable')
                    ],
                    'announcement_bar_v2' => $this->_getSetting('theme/homepage_v2/announcement_bar_content'),
                    'footer' => [
                        'info' => [
                            'title' => $this->_getSetting('theme/footer/column1/title'),
                            'content' => $this->_getSetting('theme/footer/column1/content')
                        ],
                        'trustpilot' => [
                            'enable' => $this->_getSetting('theme/footer/trustpilot/enable'),
                            'template_id' => $this->_getSetting('theme/footer/trustpilot/template_id'),
                            'businessunit_id' => $this->_getSetting('theme/footer/trustpilot/businessunit_id'),
                            'url_store' => $this->_getSetting('theme/footer/trustpilot/url_store'),
                        ],
                        'dmca' => [
                            'enable' => $this->_getSetting('theme/footer/enable_widget_dmca'),
                            'embed_code' => $this->_getSetting('theme/footer/widget_dmca'),
                        ],
                        'phone_support' => [ 
                            OSC::helper('frontend/template')->getPhoneSupport()
                        ], 
                        'email_support' => [ 
                            'email' =>  trim($this->_getSetting('theme/contact/email')),
                            'help' =>  trim($this->_getSetting('theme/contact/help_email'))
                        ], 
                        'contact_us' => [                            
                            'is_enable_noti' => intval($this->_getSetting('contact_us/is_enable_noti')),
                            'is_enable_live_chat' => intval($this->_getSetting('contact_us/is_enable_live_chat')),
                            'is_enable_email' => intval($this->_getSetting('contact_us/is_enable_email')),
                            'is_enable_phone' => intval($this->_getSetting('contact_us/is_enable_phone')),
                        ]
                    ],
                    'copyright' => $this->_getSetting('theme/footer/copyright'),
                    'company_info' => [
                        'name' => $this->_getSetting('theme/contact/name'),
                        'phone_support' => OSC::helper('frontend/template')->getPhoneSupport(),
                        'fax' => $this->_getSetting('theme/contact/fax'),
                        'address' => $this->_getSetting('theme/contact/address'),
                        'email' => $this->_getSetting('theme/contact/email'),
                        'about' => $this->_getSetting('theme/about')
                    ]
                ],
                'ab_test' => [
                ],
                'shop_id' => $shop['shop_id'],
                'shop_name' => $shop['shop_name'],
                'shop_domain' => $shop['shop_domain'],
                'enable_v4' => intval($this->_getSetting('catalog/product/enable_product_detail_v4')),
                'osc_env_development' => OSC_ENV != 'production',
                'cdn' => OSC::systemRegistry('CDN_CONFIG'),
                'storage_base_url' => OSC::core('aws_s3')->getS3CDNUrl(),
                'trending_keywords' => OSC::helper('filter/search')->getTrendingKeywords(),
                'popular_collections' => OSC::helper('filter/search')->getPopularCollections(),
                'pages' => $this->_getCommonPage(),
                'enable_gift_finder' => intval($this->_getSetting('filter_search/gift_finder/enable'))
            ]);
            $this->_common_layout = $common_layout;
        }

        return $this->_common_layout;
    }

    protected $_common_layout_v1 = null;

    public function getCommonLayoutV1()
    {
        if ($this->_common_layout_v1 === null) {
            $this->_common_layout_v1 = [
                'logo' => OSC::decode(OSC::encode(OSC::helper('frontend/template')->getLogo())),
                'logo_small' => OSC::decode(OSC::encode(OSC::helper('frontend/template')->getLogo(false, true))),
                'favicon' => OSC::decode(OSC::encode(OSC::helper('frontend/template')->getFavicon())),
                'navigation' => [
                    'top_menu' => OSC::helper('frontend/template')->getNavigation('theme/header/top_menu'),
                    'main_menu' => OSC::helper('frontend/template')->getNavigation('theme/header/main_menu'),
                    'amp_menu' => OSC::helper('frontend/template')->getNavigation('theme/header/amp_menu'),
                    'footer' => [
                        'title' => $this->_getSetting('theme/footer/column3/title'),
                        'menu' => OSC::helper('frontend/template')->getNavigation('theme/footer/column3/content')
                    ],
                    'homepage_v2' => [
                        'top_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/top_menu'),
                        'main_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/main_menu'),
                        'mobile_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/mobile_menu'),
                        'footer_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/footer_menu'),
                    ]
                ],
                'meta_data' => [
                    'base_url' => OSC_FRONTEND_BASE_URL,
                    'title' => $this->_getSetting('theme/metadata/title'),
                    'keywords' => $this->_getSetting('theme/metadata/keyword'),
                    'description' => $this->_getSetting('theme/metadata/description'),
                    'seo_image' => OSC::helper('frontend/template')->getMetaImage()->url,
                    'google_site_verification' => $this->_getSetting('tracking/google/verification')
                ],
                'social_link' => [
                    [
                        'channel' => 'facebook',
                        'link' => $this->_getSetting('theme/social/facebook'),
                    ],
                    [
                        'channel' => 'facebook_group',
                        'link' => $this->_getSetting('theme/social/facebook_group'),
                    ],
                    [
                        'channel' => 'twitter',
                        'link' => $this->_getSetting('theme/social/twitter'),
                    ],
                    [
                        'channel' => 'youtube',
                        'link' => $this->_getSetting('theme/social/youtube'),
                    ],
                    [
                        'channel' => 'instagram',
                        'link' => $this->_getSetting('theme/social/instagram'),
                    ],
                    [
                        'channel' => 'pinterest',
                        'link' => $this->_getSetting('theme/social/pinterest'),
                    ]
                ],
                'live_chat' => [
                    'enable' => OSC::helper('core/setting')->get('theme/live_chat/enable')
                ],
                'announcement_bar_v2' => $this->_getSetting('theme/homepage_v2/announcement_bar_content'),
                'footer' => [
                    'info' => [
                        'title' => $this->_getSetting('theme/footer/column1/title'),
                        'content' => $this->_getSetting('theme/footer/column1/content')
                    ],
                    'trustpilot' => [
                        'enable' => $this->_getSetting('theme/footer/trustpilot/enable'),
                        'template_id' => $this->_getSetting('theme/footer/trustpilot/template_id'),
                        'businessunit_id' => $this->_getSetting('theme/footer/trustpilot/businessunit_id'),
                        'url_store' => $this->_getSetting('theme/footer/trustpilot/url_store'),
                    ],
                    'dmca' => [
                        'enable' => $this->_getSetting('theme/footer/enable_widget_dmca'),
                        'embed_code' => $this->_getSetting('theme/footer/widget_dmca'),
                    ],
                    'phone_support' => [
                        OSC::helper('frontend/template')->getPhoneSupport()
                    ],
                    'email_support' => [
                        'email' => trim($this->_getSetting('theme/contact/email')),
                        'help' => trim($this->_getSetting('theme/contact/help_email'))
                    ],
                    'contact_us' => [
                        'is_enable_noti' => intval($this->_getSetting('contact_us/is_enable_noti')),
                        'is_enable_live_chat' => intval($this->_getSetting('contact_us/is_enable_live_chat')),
                        'is_enable_email' => intval($this->_getSetting('contact_us/is_enable_email')),
                        'is_enable_phone' => intval($this->_getSetting('contact_us/is_enable_phone')),
                    ]
                ],
                'copyright' => $this->_getSetting('theme/footer/copyright'),
                'company_info' => [
                    'name' => $this->_getSetting('theme/contact/name'),
                    'phone_support' => OSC::helper('frontend/template')->getPhoneSupport(),
                    'fax' => $this->_getSetting('theme/contact/fax'),
                    'address' => $this->_getSetting('theme/contact/address'),
                    'email' => $this->_getSetting('theme/contact/email'),
                    'about' => $this->_getSetting('theme/about')
                ]
            ];
        }

        return $this->_common_layout_v1;
    }

    protected $_navigation_bar = null;

    public function getNavigationBar() {
        if ($this->_navigation_bar === null) {
            $this->_navigation_bar = [
                'top_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/top_menu'),
                'main_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/main_menu'),
                'mobile_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/mobile_menu'),
                'footer_menu' => OSC::helper('frontend/template')->getNavigation('theme/homepage_v2/footer_menu'),
            ];
        }

        return $this->_navigation_bar;
    }

    protected function _getCommonPage() {
        $result = [];

        try {
            $page_collection = OSC::model('page/page')
                ->getCollection()
                ->addField('page_id', 'page_key', 'slug')
                ->addCondition('page_key', NULL, OSC_Database::OPERATOR_NOT_EQUAL)
                ->load();

            if (!empty($page_collection)) {
                foreach ($page_collection as $item) {
                    $result[$item->data['page_key']] = [
                        'page_id' => $item->data['page_id'],
                        'slug' => $item->data['slug']
                    ];
                }
            }
        } catch (Exception $ex) {}

        return $result;
    }

    protected function _getSetting($key)
    {
        return OSC::helper('core/setting')->get($key);
    }
}