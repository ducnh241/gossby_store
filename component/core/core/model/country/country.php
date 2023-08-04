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
class Model_Core_Country_Country extends OSC_Database_Model {

    /**
     *
     * @var string 
     */
    protected $_table_name = 'location_country';

    /**
     *
     * @var string 
     */
    protected $_pk_field = 'id';

    protected $_ukey_field = 'country_code';


    public function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['zip_formats'])) {
            $data['zip_formats'] = OSC::encode($data['zip_formats']);
        }
        if (isset($data['phone_prefix'])) {
            $data['phone_prefix'] = OSC::encode($data['phone_prefix']);
        }

    }

    public function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['zip_formats'])) {
            $data['zip_formats'] = OSC::decode($data['zip_formats'], true);
        }

        if (isset($data['phone_prefix'])) {
            $data['phone_prefix'] = OSC::decode($data['phone_prefix'], true);
        }
    }
}
