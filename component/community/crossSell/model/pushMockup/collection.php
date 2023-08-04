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
class Model_CrossSell_PushMockup_Collection extends Abstract_Core_Model_Collection {
    public function getItemsByUkeys($ukeys) {
        return $this->addCondition('ukey', $ukeys, OSC_Database::OPERATOR_IN)->load();
    }

    public function checkFullMockups($design_id) {
        $collection = $this
            ->addField('design_id', 'count_mockup', 'total_mockup')
            ->addCondition('design_id', $design_id, OSC_Database::OPERATOR_EQUAL)
            ->load();

        foreach ($collection as $model) {
            if ($model->data['count_mockup'] < $model->data['total_mockup']) {
                return false;
            }
        }

        return true;
    }

    public function setQueueFlagRunning($design_id) {
        try {
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->update('cross_sell_push_mockup_queue', ['queue_flag' => Model_CrossSell_PushMockup::QUEUE_TYPE_BEGIN], 'design_id =' . $design_id, null, 'update_queue');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getTotalVariantByDesignId($design_id) {
        return $this
            ->addField('id')
            ->addCondition('design_id', $design_id, OSC_Database::OPERATOR_EQUAL)
            ->load()->length();
    }
}
