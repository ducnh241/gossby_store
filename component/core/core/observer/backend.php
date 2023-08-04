<?php

class Observer_Core_Backend {

    protected static $_image_to_save = [];
    protected static $_image_to_delete = [];

    public static function collectSettingSection() {
        return [
            [
                'key' => 'general',
                'icon' => 'cog',
                'title' => 'General',
                'description' => 'View and update your store details'
            ],
            [
                'key' => 'tracking',
                'priority' => 2,
                'icon' => 'setting-location',
                'title' => 'Tracking',
                'description' => 'Manage your tracking services'
            ]
        ];
    }

    public static function collectPermKey($params) {
        $params['permission_map']['settings'] = [
            'label' => 'Settings',
            'items' => [
                'feed_config' => 'Feed Configuration',
            ]
        ];
    }

    public function collectSettingType() {
        return [
            [
                'key' => 'text',
                'template' => 'core/setting/type/text'
            ],
            [
                'key' => 'number',
                'template' => 'core/setting/type/number'
            ],
            [
                'key' => 'color',
                'template' => 'core/setting/type/color'
            ],
            [
                'key' => 'textarea',
                'template' => 'core/setting/type/textarea'
            ],
            [
                'key' => 'address',
                'template' => 'core/setting/type/address',
                'validator' => [Observer_Core_Backend, 'validateAddress']
            ],
            [
                'key' => 'editor',
                'template' => 'core/setting/type/editor'
            ],
            [
                'key' => 'select',
                'template' => 'core/setting/type/select'
            ],
            [
                'key' => 'switcher',
                'template' => 'core/setting/type/switcher',
                'validator' => [Observer_Core_Backend, 'validateSwitcher']
            ],
            [
                'key' => 'timezone',
                'template' => 'core/setting/type/timezone'
            ],
            [
                'key' => 'image',
                'template' => 'core/setting/type/image',
                'validator' => [Observer_Core_Backend, 'validateImage']
            ],
            [
                'key' => 'date',
                'template' => 'core/setting/type/date',
                'validator' => [Observer_Core_Backend, 'validateDate']
            ],
            [
                'key' => 'datetime',
                'template' => 'core/setting/type/datetime',
                'validator' => [Observer_Core_Backend, 'validateDateTime']
            ],
            [
                'key' => 'date_range',
                'template' => 'core/setting/type/date_range',
                'validator' => [Observer_Core_Backend, 'validateDateRange']
            ],
            [
                'key' => 'datetime_range',
                'template' => 'core/setting/type/datetime_range',
                'validator' => [Observer_Core_Backend, 'validateDateTimeRange']
            ],
            [
                'key' => 'button',
                'template' => 'core/setting/type/button'
            ]
        ];
    }

    public static function collectSettingItem() {
        return [
            [
                'key' => 'format',
                'section' => 'general',
                'type' => 'group',
                'title' => 'Timezone',
                'description' => 'Timezone is used to calculate pricing, shipping weight and delivery date.'
            ],
            [
                'key' => 'core/timezone',
                'section' => 'general',
                'group' => 'format',
                'title' => 'Timezone',
                'type' => 'timezone',
                'default' => 'Asia/Ho_Chi_Minh'
            ],
            [
                'section' => 'tracking',
                'key' => 'tracking_utm',
                'type' => 'group',
                'title' => 'UTM Tracking',
                'description' => "<b>Example:</b><br>utm_source=Repolist<br>utm_medium=ER<br>utm_campaign=Repo"
            ],
            [
                'section' => 'tracking',
                'group' => 'tracking_utm',
                'key' => 'tracking_utm/best_selling',
                'type' => 'textarea',
                'title' => 'UTM Tracking Best Selling Params',
                'validator' => [Observer_Core_Backend, 'validateQueryString']
            ],
            [
                'section' => 'tracking',
                'group' => 'tracking_utm',
                'key' => 'tracking_utm/recommend_product',
                'type' => 'textarea',
                'title' => 'UTM Tracking Recommend Product Prams',
                'validator' => [Observer_Core_Backend, 'validateQueryString']
            ],
            [
                'section' => 'tracking',
                'key' => 'facebook_pixel',
                'type' => 'group',
                'title' => 'Facebook Pixel',
                'description' => 'Tracking with Facebook Pixel'
            ],
            [
                'section' => 'tracking',
                'group' => 'facebook_pixel',
                'key' => 'tracking/facebook_pixel/code',
                'type' => 'textarea',
                'title' => 'Pixels',
                'full_row' => true,
                'desc' => 'Format: [pixel id]:[event 1]:[event 2]:....<br />Events: PageView, ViewContent, Search, AddToCart, InitiateCheckout, AddPaymentInfo, Lead, CompleteRegistration, Purchase, AddToWishlist'
            ],
            [
                'section' => 'tracking',
                'group' => 'facebook_pixel',
                'key' => 'tracking/facebook_pixel',
                'type' => 'switcher',
                'title' => 'Enable Facebook Pixel',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'group' => 'facebook_pixel',
                'key' => 'tracking/facebook_pixel/skip_pixel_in_campaign',
                'type' => 'switcher',
                'title' => 'Skip Facebook Pixel In Campaign',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'group' => 'facebook_pixel',
                'key' => 'tracking/facebook_pixel_api/pixel_id',
                'type' => 'textarea',
                'title' => 'Pixel ID',
                'row_before_title' => 'API Facebook Pixel',
                'line_before' => true
            ],
            [
                'section' => 'tracking',
                'group' => 'facebook_pixel',
                'key' => 'tracking/facebook_pixel_api/access_token',
                'type' => 'textarea',
                'title' => 'Access token',
            ],
            [
                'section' => 'tracking',
                'group' => 'facebook_pixel',
                'key' => 'tracking/facebook_pixel_api/enable',
                'type' => 'switcher',
                'title' => 'Enable Facebook Pixel API',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'key' => 'tiktok',
                'type' => 'group',
                'title' => 'TikTok Pixel',
                'description' => 'Tracking with TikTok Pixel'
            ],
            [
                'section' => 'tracking',
                'group' => 'tiktok',
                'key' => 'tracking/tiktok/pixels',
                'type' => 'textarea',
                'title' => 'Pixels',
                'full_row' => true,
                'desc' => '<b>Format:</b> <br>pixel 1<br>pixel 2<br>...<br /><b>Events:</b> PageView, ViewContent, AddToCart, InitiateCheckout, Purchase'
            ],
            [
                'section' => 'tracking',
                'group' => 'tiktok',
                'key' => 'tracking/tiktok/enable',
                'type' => 'switcher',
                'title' => 'Enable TikTok Pixel',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'key' => 'snapchat',
                'type' => 'group',
                'title' => 'Snapchat Pixel',
                'description' => 'Tracking with Snapchat Pixel'
            ],
            [
                'section' => 'tracking',
                'group' => 'snapchat',
                'key' => 'tracking/snapchat/pixels',
                'type' => 'textarea',
                'title' => 'Pixels',
                'full_row' => true,
                'desc' => '<b>Format:</b> <br>pixel 1<br>pixel 2<br>...<br /><b>Events:</b> PAGE_VIEW, VIEW_CONTENT, ADD_CART, START_CHECKOUT, PURCHASE'
            ],
            [
                'section' => 'tracking',
                'group' => 'snapchat',
                'key' => 'tracking/snapchat/enable',
                'type' => 'switcher',
                'title' => 'Enable Snapchat Pixel',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'key' => 'bing',
                'type' => 'group',
                'title' => 'Bing Ads',
                'description' => 'Universal Event Tracking'
            ],
            [
                'section' => 'tracking',
                'group' => 'bing',
                'key' => 'tracking/bing/id',
                'type' => 'text',
                'title' => 'Universal Event Tracking ID',
                'full_row' => true,
                'desc' => '<b>Events:</b> page_view, view_item, add_to_cart, begin_checkout, purchase'
            ],
            [
                'section' => 'tracking',
                'group' => 'bing',
                'key' => 'tracking/bing/enable',
                'type' => 'switcher',
                'title' => 'Enable Universal Event Tracking',
                'full_row' => true
            ],
            [
                'section' => 'tracking',
                'key' => 'spotify_api',
                'type' => 'group',
                'title' => 'Spotify API'
            ],
            [
                'section' => 'tracking',
                'group' => 'spotify_api',
                'key' => 'spotify_api/client_id',
                'type' => 'text',
                'title' => 'Client ID'
            ],
            [
                'section' => 'tracking',
                'group' => 'spotify_api',
                'key' => 'spotify_api/client_secret',
                'type' => 'text',
                'title' => 'Client Secret'
            ]
        ];
    }

    public static function collectMenu() {
        if (!OSC::helper('user/authentication')->getMember()->getGroup()->isAdmin() && !OSC::controller()->checkPermission('settings', false)) {
            return null;
        }

        return [
            [
                'key' => 'core/setting',
                'position' => 1000,
                'icon' => 'cog',
                'title' => 'Settings',
                'url' => OSC::getUrl('core/backend_setting/index', [], true)
            ]
        ];
    }

    public static function processQueuedImage() {
        if (count(static::$_image_to_delete) > 0) {
            foreach (static::$_image_to_delete as $file) {
                try {
                    OSC::core('aws_s3')->deleteStorageFile($file);
                } catch (Exception $ex) {

                }
            }
        }

        if (count(static::$_image_to_save) > 0) {
            foreach (static::$_image_to_save as $file) {
                try {
                    $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($file);
                    $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($file);
                    OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
                } catch (Exception $ex) {

                }
            }
        }
    }

    public static function validateDate($value, $setting_item) {
        return $value;
    }

    public static function validateQueryString($values, $setting_item)
    {
        $values = trim($values);
        if (trim($values) == '') {
            return '';
        }
        $values = explode("\n", $values);

        $values = array_map(function ($value) {
            $query_string = explode('=', trim($value));
            if (!is_array($query_string) || count($query_string) != 2) {
                throw new Exception('Query string invalid');
            }
            return trim($value);
        }, $values);

        $values = array_filter($values, function ($value) {
            return $value != '';
        });

        return implode("\n", $values);
    }

    public static function validateDateTime($value, $setting_item) {
        return $value;
    }

    public static function validateSwitcher($value, $setting_item) {
        return intval($value) == 1 ? 1 : 0;
    }

    public static function validateAddress($value, $setting_item) {
        if (!is_array($value)) {
            $value = [];
        } else {
            try {
                $value = OSC::helper('core/country')->verifyAddress($value);
            } catch (Exception $ex) {
                $value = [];
            }
        }

        return $value;
    }

    public static function validateImage($value, $setting_item) {
        $old_value = OSC::helper('core/setting')->get($setting_item['key']);

        if (!is_array($old_value)) {
            $old_value = [
                'file' => '',
                'alt' => ''
            ];
        }

        $value = OSC::decode($value, true);

        if (!is_array($value)) {
            $value = [
                'file' => '',
                'alt' => ''
            ];
        }

        if (!$value['file']) {
            if ($old_value['file']) {
                if (count(static::$_image_to_delete) < 1 && count(static::$_image_to_save) < 1) {
                    OSC::core('observer')->addObserver('setting_updated', function() {
                        Observer_Core_Backend::processQueuedImage();
                    });
                }

                static::$_image_to_delete[] = $old_value['file'];
            }

            return null;
        }

        $value['alt'] = trim($value['alt']);

        if ($old_value['file'] == $value['file']) {
            return $value;
        }
        if (!preg_match('/^setting\/\d+\./', $value['file'])) {
            throw new Exception('The image file is not matched . ' . $value['file']);
        }

        $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($value['file']);
        if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
            throw new Exception('The image is not exists');
        }

        $extension = preg_replace('/.+\.([^\.+]+)$/', '\\1', $value['file']);

        if (in_array($extension, ['png', 'jpg', 'gif'], true)) {
            $dim_config = [];

            foreach (['min_width', 'min_height', 'max_width', 'max_height'] as $k) {
                $dim_config[$k] = isset($setting_item[$k]) ? intval($setting_item[$k]) : 0;

                if ($dim_config[$k] < 0) {
                    $dim_config[$k] = 0;
                }
            }

            list($width, $height) = getimagesize(OSC::core('aws_s3')->getObjectUrl($tmp_image_path_s3));

            if ($dim_config['min_width'] > 0 && $width < $dim_config['min_width']) {
                if (isset($dim_config['max_width'])) {
                    throw new Exception('The width of uploaded image must be between ' . $dim_config['min_width'] . 'px and ' . $dim_config['max_width'] . 'px');
                }
                throw new Exception('The image width is need greater than or equal to ' . $dim_config['min_width'] . 'px');
            }

            if ($dim_config['min_height'] > 0 && $height < $dim_config['min_height']) {
                if (isset($dim_config['max_height'])) {
                    throw new Exception('The height of uploaded image must be between ' . $dim_config['min_height'] . 'px and ' . $dim_config['max_height'] . 'px');
                }
                throw new Exception('The image height is need greater than or equal to ' . $dim_config['min_height'] . 'px');
            }

            if ($dim_config['max_width'] > 0 && $dim_config['max_height'] > 0) {
                if ($dim_config['max_width'] > 0 && $dim_config['max_height'] > 0 && $setting_item['trim']) {
                    if ($dim_config['max_width'] != $width) {
                        throw new Exception('The width of uploaded image must be ' . $dim_config['max_width'] . 'px');
                    }

                    if ($dim_config['max_height'] != $height) {
                        throw new Exception('The height of uploaded image must be ' . $dim_config['max_height'] . 'px');
                    }
                } else {
                    if ($dim_config['max_width'] < $width) {
                        if (isset($dim_config['min_width'])) {
                            throw new Exception('The width of uploaded image must be between ' . $dim_config['min_width'] . 'px and ' . $dim_config['max_width'] . 'px');
                        }
                        throw new Exception('The width of image is need greater than or equal to ' . $dim_config['max_width'] . 'px');
                    }

                    if ($dim_config['max_height'] < $height) {
                        if (isset($dim_config['min_height'])) {
                            throw new Exception('The height of uploaded image must be between ' . $dim_config['min_height'] . 'px and ' . $dim_config['max_height'] . 'px');
                        }
                        throw new Exception('The height of image is need greater than or equal to ' . $dim_config['max_height'] . 'px');
                    }
                }
            }
        }

        if (count(static::$_image_to_delete) < 1 && count(static::$_image_to_save) < 1) {
            OSC::core('observer')->addObserver('setting_updated', function() {
                Observer_Core_Backend::processQueuedImage();
            });
        }

        if ($old_value['file']) {
            static::$_image_to_delete[] = $old_value['file'];
        }

        static::$_image_to_save[] = $value['file'];

        return [
            'file' => $value['file'],
            'alt' => $value['alt']
        ];
    }

}
