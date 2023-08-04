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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC Backend Abstract Controller
 *
 * @package Abstract_Backend_Controller
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class Abstract_Frontend_Controller extends Abstract_Core_Controller {

    protected static $_initialized = false;

    public function __construct() {
        parent::__construct();

        if (!Abstract_Frontend_Controller::$_initialized) {
            Abstract_Frontend_Controller::$_initialized = true;

            OSC::core('observer')->dispatchEvent('frontend_initialize');

            static::_setTrafficSource();
        }

        $request_by_master = $this->_request->get('request_by_master');

        $location = OSC::helper('core/common')->getClientLocation();

        if (!defined('OSC_SKIP_BLOCK_COUNTRY') &&
            array_key_exists($location['country_code'], OSC::helper('core/setting')->get('list/block_ip_countries')) &&
            $this->getAccount()->getId() < 1 && (!isset($request_by_master) || $request_by_master != 1)
        ) {
            static::redirect(OSC::$base_url . '/frontend/authentication/login');
            die;
        }

        /* Clear last order address customer if IP location diff last order address*/
        if (is_array($location) &&
            $location['country_code'] &&
            OSC::cookieGet(OSC_SITE_KEY . '-LOA') &&
            $location['country_code'] != base64_decode(OSC::cookieGet(OSC_SITE_KEY . '-LOA'))['country_code']
        ) {
            OSC::cookieRemoveCrossSite(OSC_SITE_KEY . '-LOA');
        }

        $discount_code = $this->_request->get('discount');
        if ($discount_code) {
            OSC::helper('catalog/common')->applyDiscountCodeParamsUrl(trim($discount_code));
        }

        $this->getTemplate()->setPageTitle($this->setting('theme/metadata/title'));
        $this->getTemplate()->addComponent('catalog_frontend');

        /* ToanMV Comment To Optimize */
//        Helper_Core_AntiCrawler::checkBlocked();
    }

    protected function _setTrafficSource() {
        if(OSC::isCrawlerRequest()) {
            return;
        }

        $sref = OSC::registry('DLS-SALE-REF');

        $sref_type = 'organic_traffic';

        if (isset($_REQUEST['sref']) || isset($_REQUEST['adref'])) {
            if (is_numeric($sref)) {
                $sale_ref_id = $sref;
            } elseif (is_string($sref)) {
                $sale_ref_id = intval($sref);
            } elseif (is_array($sref)) {
                $sale_ref_id = intval(end($sref));
            }

            if (OSC::$current_controller == 'catalog' && OSC::$current_router == 'frontend' && OSC::$current_action == 'detail') {
                try {
                    $options = isset($_REQUEST['ukey']) && !empty($_REQUEST['ukey']) ? ['ukey' => $_REQUEST['ukey']] : ['id' => $_REQUEST['id']];

                    $product = OSC::helper('catalog/product')->getProduct($options, true);

                    if ($product->getId() < 1) {
                        throw new Exception('Not have product id to tracking');
                    }

                    // handle sref source, sref dest product
                    Observer_Catalog_Common::handleSrefSourceDestByProduct($product);

                } catch (Exception $ex) { }

                try {
                    // handle abtest tab
                    OSC::helper('frontend/frontend')->handleAbtestTab($product);
                } catch (Exception $ex) { }
            }

            if ($sale_ref_id > 0) {
                try {
                    $member = OSC::model('user/member')->load($sale_ref_id);
                    Observer_Catalog_Common::setSrefCookie($member);
                    OSC::register('ENABLE-SREF-BY-DESCRIPTION', 1);
                } catch (Exception $ex) {

                }
            }
        } else {
            if (!is_array($sref)) {
                try {
                    $collection = OSC::model('user/member')->getCollection()->addField('member_id', 'username', 'sref_type', 'password_hash', 'email', 'added_timestamp')->addCondition('sref_type', $sref_type, OSC_Database::OPERATOR_EQUAL)->setLimit(1)->load();
                    Observer_Catalog_Common::setSrefCookie($collection->getItem());
                    OSC::register('ENABLE-SREF-BY-DESCRIPTION', 1);
                } catch (Exception $ex) {

                }
            }
        }

        OSC::register('DLS-ADD-SREF-URL', 1);
    }

    public static function getTrackingKey(): string {
        return strval(OSC::cookieGet(Controller_Report_Frontend::getTrackingCookieKey()));
    }

    /**
     *
     * @var Helper_Frontend_Template
     */
    protected $_template = null;

    /**
     *
     * @return Helper_Frontend_Template
     */
    public function getTemplate() {
        if ($this->_template === null) {
            $this->_template = OSC::helper('frontend/template');
            $this->_template->addComponent('frontend');
        }

        return $this->_template;
    }

    /**
     * 
     * @param string $buffer
     */
    public function output($content, $layout = 'frontend/html/layout/default', $blocks = array()) {
        $content = $this->getTemplate()->setContent($this->getTemplate()->build($layout, array('content' => $content, 'blocks' => $blocks)))->render();
        parent::output($content);
    }

    public function outputRaw($content) {
        parent::output($content);
    }

}
