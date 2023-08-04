<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package Helper_Core_Session
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Core_Common extends OSC_Object {

    protected $_image_optimaze_queues = [];

    public function __construct() {
        parent::__construct();
    }

    public function getClientLocation() {
        return $this->getIPLocation(OSC::getClientIP());
    }

    public function getIPLocation($ip_address) {
        if (defined('OSC_LOCATION') && is_array(OSC_LOCATION) && OSC_ENV !== 'production') {
            return [
                'city' => OSC_LOCATION['geoplugin_city'],
                'region' => OSC_LOCATION['geoplugin_region'],
                'region_code' => OSC_LOCATION['geoplugin_regionCode'],
                'country_code' => OSC_LOCATION['geoplugin_countryCode'],
                'country_name' => OSC_LOCATION['geoplugin_countryName'],
                'latitude' => OSC_LOCATION['geoplugin_latitude'],
                'longitude' => OSC_LOCATION['geoplugin_longitude'],
                'currency_code' => OSC_LOCATION['geoplugin_currencyCode'],
                'currency_symbol' => OSC_LOCATION['geoplugin_currencySymbol'],
                'currency_rate' => OSC_LOCATION['geoplugin_currencyConverter']
            ];
        }

        if (!isset($_SESSION['ip_info'])) {
            $_SESSION['ip_info'] = [];
        }

        if (isset($_SESSION['ip_info'][$ip_address])) {
            return $_SESSION['ip_info'][$ip_address];
        }

        try {
            $cache_key = 'GEOPLUGIN:' . $ip_address;
            $adapter = OSC::core('cache')->getAdapter();

            $cache_data = $adapter->get($cache_key);
            if ($cache_data && !empty($cache_data['country_code'])) {
                $location = $cache_data;
            } else {
                $location = OSC::core('network')->curl('http://www.geoplugin.net/json.gp?ip=' . $ip_address, ['timeout' => 1, 'connect_timeout' => 1]);
                $location = OSC::decode($location['content'], true);

                $location = [
                    'city' => $location['geoplugin_city'],
                    'region' => $location['geoplugin_region'],
                    'region_code' => $location['geoplugin_regionCode'],
                    'country_code' => $location['geoplugin_countryCode'],
                    'country_name' => $location['geoplugin_countryName'],
                    'latitude' => $location['geoplugin_latitude'],
                    'longitude' => $location['geoplugin_longitude'],
                    'currency_code' => $location['geoplugin_currencyCode'],
                    'currency_symbol' => $location['geoplugin_currencySymbol'],
                    'currency_rate' => $location['geoplugin_currencyConverter']
                ];

                $adapter->set($cache_key, $location, 86400);
            }

            $_SESSION['ip_info'][$ip_address] = $location;
        } catch (Exception $ex) {
            $location = null;
        }

        return $location;
    }

    public function loadCountrySelectorData($country_codes) {
        $countries = [];

        try {
            foreach (OSC::helper('core/country')->getCountries() as $country_code => $country_title) {
                if (in_array($country_code, $country_codes, true)) {
                    $countries[] = ['id' => $country_code, 'title' => $country_title];
                }
            }
        } catch (Exception $ex) {
            
        }

        return $countries;
    }

    public function imageOptimaze(string $path, int $width, int $height, bool $crop = false, bool $keep_extension = false, $skip_check_crawler_request = false) {
        if (OSC::isCrawlerRequest() && !$skip_check_crawler_request) {
            return '';
        }

        if (!class_exists('Imagick', false) || isset($_REQUEST['skip_optimize_img'])) {
            return $path;
        }

        if ($path == '') {
            return $path;
        }

        $image_path = OSC::unwrapCDN($path);

        if (substr($image_path, 0, strlen(OSC::$base_url)) == OSC::$base_url) {
            $image_path = substr($image_path, strlen(OSC::$base_url));
        } else if (substr($image_path, 0, strlen(OSC_SITE_PATH)) == OSC_SITE_PATH) {
            $image_path = substr($image_path, strlen(OSC_SITE_PATH));
        }

        $image_path = OSC_SITE_PATH . $image_path;

        if (!file_exists($image_path) || !is_file($image_path)) {
            return $path;
        }

        if (!$keep_extension) {
            $extension = 'jpg';
        } else {
            $extension = strtolower(preg_replace('/^.+\.([^\.]+)$/i', '\\1', $image_path));
        }

        if (!in_array($extension, ['png', 'gif', 'jpg'])) {
            return $path;
        }

        $mtime = filemtime($image_path);

        $optimazed_image_path = '/opt_images/' . md5($image_path) . '.' . $width . 'x' . $height . '.' . ($crop ? 1 : 0) . '.{{mtime}}.' . $extension;
        $optimazed_image_url = OSC::$base_url . '/var' . $optimazed_image_path;
        $optimazed_image_path = OSC_VAR_PATH . $optimazed_image_path;

        if (!file_exists(str_replace('{{mtime}}', $mtime, $optimazed_image_path))) {
            if (count($this->_image_optimaze_queues) < 1) {
                OSC::core('observer')->addObserver('shutdown', function() {
                    $images = OSC::helper('core/common')->getImageOptimazeQueues();

                    if (count($images) < 1) {
                        return;
                    }

                    $process_key = OSC::makeUniqid(null, true);

                    $queries = [];

                    $params = [];

                    $path_counter = 0;
                    $added_timestamp = time();

                    foreach ($images as $original_path => $image) {
                        $path_counter ++;

                        $params['path' . $path_counter] = str_replace(OSC_SITE_PATH . '/', '', $original_path);
                        $image['optimized_path'] = str_replace(OSC_SITE_PATH . '/', '', $image['optimized_path']);

                        $queries[] = "('{$process_key}', :path{$path_counter}, '{$image['optimized_path']}', '{$image['extension']}', {$image['width']}, {$image['height']}, {$image['crop']}, 0, {$added_timestamp})";
                    }

                    $queries = implode(',', $queries);
                    $queries = <<<EOF
INSERT IGNORE INTO osc_core_image_optimize (process_key, original_path, optimized_path, extension, width, height, crop_flag, webp_flag, added_timestamp) VALUES {$queries};                        
EOF;

                    /* @var $DB OSC_Database */
                    $DB = OSC::core('database')->getWriteAdapter();

                    $DB->begin();

                    try {
                        $DB->query($queries, $params, 'insert_optimize_record');

                        if ($DB->getNumAffected('insert_optimize_record') > 0) {
                            OSC::core('cron')->addQueue('core/imageOptimize', ['process_key' => $process_key], ['skip_realtime', 'requeue_limit' => -1, 'estimate_time' => 60*30]);
                        }

                        $DB->commit();
                    } catch (Exception $ex) {
                        $DB->rollback();
                    }
                });
            }

            $this->_image_optimaze_queues[$image_path] = [
                'optimized_path' => $optimazed_image_path,
                'width' => max(0, $width),
                'height' => max(0, $height),
                'crop' => $crop ? 1 : 0,
                'extension' => $extension
            ];

            return $path;
        }

        return OSC::wrapCDN(str_replace('{{mtime}}', $mtime, $optimazed_image_url));
    }

    public function getImageOptimazeQueues() {
        return $this->_image_optimaze_queues;
    }

    /**
     * @param int $product_type_id
     * @param string $country_code
     * @param string $province_code
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getTaxValueByLocation(
        int $product_type_id,
        $country_code = '',
        $province_code = '',
        $preload_tax_settings = null
    ) {
        $tax_value = null;

        $collection = $preload_tax_settings ?? OSC::model('catalog/tax')->getCollection()
            ->addCondition('product_type_id', [$product_type_id, 0], OSC_Database::OPERATOR_IN)
            ->load();

        foreach ($collection as $tax_setting_model) {
            if ($tax_setting_model->data['product_type_id'] === 0 &&
                in_array($product_type_id, $tax_setting_model->data['exclude_product_type_ids'])
            ) {
                continue;
            }

            if ($tax_setting_model->data['product_type_id'] !== 0 &&
                $tax_setting_model->data['product_type_id'] !== $product_type_id
            ) {
                continue;
            }

            $flag_location = OSC::helper('core/country')->checkCountryProvinceInLocation(
                $country_code,
                $province_code,
                $tax_setting_model->data['destination_location_data']
            );

            if ($tax_setting_model->data['destination_location_data'] === '*') {
                $tax_value = $tax_setting_model->data['tax_value'];
            }

            if ($flag_location) {
                $tax_value = $tax_setting_model->data['tax_value'];
                break;
            }
        }

        return $tax_value;
    }

    public function sendNotifyHiddenProduct($product_id, $country_code, $province_code) {
        $telegram_group_id  = PRODUCT_HIDDEN_TELEGRAM_GROUP_ID;

        if ($telegram_group_id) {
            $client_ip = OSC::getClientIP();

            $message = OSC::helper('core/setting')->get('theme/site_name') . "\n" .
                'Product ID #' . $product_id . ' blocked in country: ' . $country_code . ', province: ' . $province_code . "\n" .
                'IP Customer: ' . $client_ip . '. Remote Address: ' . $_SERVER['REMOTE_ADDR'] . '. Sref ID: ' . intval($_REQUEST['sref']) . '. Adref ID:'. intval($_REQUEST['adref']) . "\n" .
                'Request URI: ' . $_SERVER['REQUEST_URI'] . "\n" .
                'Referer: ' . $_SERVER['HTTP_REFERER'];

            OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id);
        }
    }

    public function sendNotifyEmptyProduct($collection_id, $collection_url) {
        try {
            $telegram_group_id = PRODUCT_HIDDEN_TELEGRAM_GROUP_ID;

            if ($telegram_group_id) {
                $client_ip = OSC::getClientIP();
                $ip_location = OSC::helper('catalog/common')->getCustomerIPLocation();
                $shipping_location = OSC::helper('catalog/common')->getCustomerShippingLocation();

                $message = OSC::helper('core/setting')->get('theme/site_name') . "\n" .
                    'Collection ID #' . $collection_id . '. Collection Url: ' . $collection_url . "\n" .
                    'IP Customer: ' . $client_ip . '. Remote Address: ' . $_SERVER['REMOTE_ADDR'] . '. Sref ID: ' . intval($_REQUEST['sref']) . '. Adref ID:'. intval($_REQUEST['adref']) . "\n" .
                    'IP Location: ' . OSC::encode($ip_location) . "\n" .
                    'Shipping Location: ' . OSC::encode($shipping_location) . "\n" .
                    'Request URI: ' . $_SERVER['REQUEST_URI'] . "\n" .
                    'Referer: ' . $_SERVER['HTTP_REFERER'];

                OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id);
            }
        } catch (Exception $ex) {

        }
    }

    public function setSrefCookieByDescription($meta_description) {
        if ($meta_description != '' && OSC::registry('REWRITE-URL') && OSC::registry('ENABLE-SREF-BY-DESCRIPTION')) {
            $sale_ref = OSC::registry('DLS-SALE-REF');

            if ($sale_ref['sref_type'] == 'organic_traffic') {
                $sref_type = 'organic_traffic';
                $collection = OSC::model('user/member')->getCollection()->addField('member_id', 'username', 'sref_type', 'password_hash', 'email', 'added_timestamp')->addCondition('sref_type', $sref_type, OSC_Database::OPERATOR_EQUAL)->setLimit(1)->load();
                Observer_Catalog_Common::setSrefCookie($collection->getItem());
            }
        }
    }

    public function parseProductTypeVariantIds($variant_data) {
        $result = [];

        foreach ($variant_data as $value) {
            if (count($value['variants']) === 0) {
                $selected_product_type_ids[] = $value['product_type_id'];
                continue;
            }

            foreach ($value['variants'] as $product_type_variant_id) {
                $result[] = $product_type_variant_id;
            }
        }

        $collection = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id')
            ->addCondition('product_type_id', $selected_product_type_ids, OSC_Database::OPERATOR_IN)
            ->load();

        foreach ($collection as $model) {
            $result[] = $model->getId();
        }

        return array_unique($result);
    }

    public function getBaseCostConfigs(Model_Catalog_ProductType_Variant $product_type_variant, $country_code) {
        $default_base_cost_configs = $product_type_variant->data['base_cost_configs'];

        $location_datas = OSC::helper('catalog/common')->getGroupLocationCustomer($country_code);

        $location_variant = OSC::model('catalog/productType_variantLocationPrice')
            ->getCollection()
            ->addField('product_type_variant_id', 'location_data', 'base_cost_configs')
            ->addCondition('product_type_variant_id', $product_type_variant->getId(), OSC_Database::OPERATOR_EQUAL)
            ->addCondition('location_data', $location_datas, OSC_Database::OPERATOR_IN)
            ->load()
            ->first();

        $base_cost_configs = $default_base_cost_configs;
        if ($location_variant instanceof Model_Catalog_ProductType_VariantLocationPrice &&
            count($location_variant->data['base_cost_configs']) > 0
        ) {
            $base_cost_configs = $location_variant->data['base_cost_configs'];
        }

        foreach ($base_cost_configs as $value) {
            $result[$value['quantity']] = intval($value['base_cost']);
        }

        return $result;
    }

    public function getMemberCookieIDKey() {
        return OSC_SITE_KEY . '-member_id';
    }

    public function isLoggedMember() {
        return intval(OSC::cookieGet($this->getMemberCookieIDKey())) > 0;
    }

    public function isGuest() {
        return !$this->isLoggedMember();
    }

    public function writeLog($title = '', $content = '', $extra = '') {

        try {
            $user = OSC::helper('user/authentication')->getMember();
        } catch (Exception $ex) {

        }

        $data = [
            'debug_backtrace' => !empty(debug_backtrace()) ?  (debug_backtrace()[1]['class'] . '.' . debug_backtrace()[1]['function'] . '-' . gettype($content)) : null,
            'user_id' => ($user instanceof Model_User_Member) ? $user->data['member_id'] : '',
            'user_name' => ($user instanceof Model_User_Member) ? $user->data['username'] : '',
            'email' => ($user instanceof Model_User_Member) ? $user->data['email'] : '',
            'title' => is_string($title) ? $title : OSC::encode($title),
            'content' => is_string($content) ? $content : OSC::encode($content),
            'extra' => is_string($extra) ? $extra : OSC::encode($extra),
            'created_at' => date('Y-m-d H:i:s'),
            'added_timestamp' => time()
        ];

        $mongodb = OSC::core('mongodb');
        $mongodb->insert('debug_log', $data, 'product');
    }

    /**
     * @param $last_time_used_memory_usage
     * @param $last_time_allocated_memory_usage
     * @param $current_used_memory_usage
     * @param $current_allocated_memory_usage
     * @param $destination
     * @return void
     */
    public function insertHighMemoryLog(
        $last_time_used_memory_usage,
        $last_time_allocated_memory_usage,
        $current_used_memory_usage,
        $current_allocated_memory_usage,
        $destination
    ) {
        try {
            $high_memory_log = [
                'process_id' => getmypid(),
                'last_time_used_memory_usage' => $last_time_used_memory_usage,
                'last_time_allocated_memory_usage' => $last_time_allocated_memory_usage,
                'current_used_memory_usage' => $current_used_memory_usage,
                'current_allocated_memory_usage' => $current_allocated_memory_usage,
                'destination' => $destination,
                'added_timestamp' => time()
            ];

            OSC::core('mongodb')->insert('high_memory_log', $high_memory_log, 'product');
        } catch (Exception $exception) {}
    }

    /**
     * @param $country_code
     * @return false|int
     */
    public function validateCountryCode($country_code) {
        return preg_match('/^[a-zA-Z]{2}$/', $country_code);
    }

    /**
     * @return string
     */
    public function getCustomerCountryCodeCookie() {
        $customer_country_code = OSC::cookieGet('customer_country_code') ?: '';

        $is_valid_country_code = $this->validateCountryCode($customer_country_code);

        if (!$is_valid_country_code) {
            $customer_country_code = '';
        }

        return $customer_country_code;
    }

    /**
     * @param $province_code
     * @return false|int
     */
    public function validateProvinceCode($province_code) {
        return preg_match('/^[a-zA-Z]+$/', $province_code);
    }

    /**
     * @return string
     */
    public function getCustomerProvinceCodeCookie() {
        $customer_province_code = OSC::cookieGet('customer_province_code') ?: '';

        $is_valid_province_code = $this->validateProvinceCode($customer_province_code);

        if (!$is_valid_province_code) {
            $customer_province_code = '';
        }

        return $customer_province_code;
    }
}
