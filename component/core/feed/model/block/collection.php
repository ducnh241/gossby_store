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
class Model_Feed_Block_Collection extends Abstract_Core_Model_Collection {

    public function collectionLength() {
        if ($this->_collection_total_item === false) {
            $DB = $this->getReadAdapter();

            $db_transaction_key = 'collection_count__' . $this->_table_name;

            $this->_collection_total_item = $DB->select("COUNT(distinct (country_code)) AS `total`", $this->_table_name, $this->_getCondition(), null, null, $db_transaction_key)->fetch($db_transaction_key)->total;

            $DB->free($db_transaction_key);
        }

        return $this->_collection_total_item;
    }

}
