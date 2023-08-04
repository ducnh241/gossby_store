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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package OSC_Controller_Alias_Model
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Controller_Alias_Model extends OSC_Database_Model {

    protected $_table_name = 'alias';
    protected $_pk_field = 'item_id';

    public function loadBySlug($slug) {
        return $this->setCondition(array('field' => 'slug', 'value' => $slug, 'operator' => OSC_Database::OPERATOR_EXACT))->load();
    }

    public function loadByDestination($destination) {
        return $this->setCondition(array('field' => 'destination', 'value' => $destination, 'operator' => OSC_Database::OPERATOR_EXACT))->load();
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        $slug = strtolower(str_replace(' ', '-', $data['slug']));

        if (preg_match('/[^\w-]/', $slug)) {
            $errors[] = 'Slug not validate!';
        }

        $data['slug'] = $slug;

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
