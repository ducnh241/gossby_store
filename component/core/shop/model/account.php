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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Shop_Account extends Abstract_Core_Model
{
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';

    protected $_table_name = 'payout_accounts';
    protected $_pk_field = 'account_id';
    protected $_allow_write_log = true;
    const POST_PREVIEW_CODE = 'preview=1';

    public function getReferer()
    {
        $referer = OSC::cookieGet($this->_getRefererCookieKey());

        return $referer ? OSC::decode($referer) : '';
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if ($this->getActionFlag() == static::INSERT_FLAG) {
            $data['added_timestamp'] = time();
            $data['modified_timestamp'] = time();
        }

        if ($this->getActionFlag() == static::UPDATE_FLAG) {
            $data['modified_timestamp'] = time();
        }

        foreach (['email' => 'email'] as $key => $attr_name) {
            if (isset($data['account_info'][$key])) {
                $data['account_info'][$key] = trim($data['account_info'][$key]);
                if ($data['account_info'][$key] === '') {
                    $errors[] = $attr_name . ' is empty';
                } else {
                    try {
                        if ($key = 'email') {
                            OSC::core('validate')->validEmail($data['account_info'][$key]);
                            try {
                                OSC::model('shop/account')->setCondition("account_info like '%" . $data['account_info'][$key] . "%' AND account_type = '" . $data['account_type'] . "' AND account_id != " . $this->getId())->load();
                                $errors[] = 'Email already exists';
                            } catch(Exception $ex){

                            }
                        }
                    } catch (Exception $ex) {
                        $errors[] = $attr_name . ' :: ' . $ex->getMessage();
                    }
                }
            }
        }
        if (isset($data['account_info']['acc_id'])) {
            unset($data['account_info']['acc_id']);
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }

    }

    protected function _afterSave()
    {
        parent::_afterSave();
        $data = $this->_collectDataForSave();
        if ($data['default_flag'] == 1) {
            $this->getWriteAdapter()->update($this->getTableName(), ['default_flag' => 0], "account_type='" . $data['account_type'] . "' AND account_id != " . $this->getId(), null, 'account_id');
        }
    }

    protected function _preDataForSave(&$data)
    {
        parent::_preDataForSave($data);
        if (!empty($data['account_info'])) {
            $data['account_info'] = OSC::encode($data['account_info']);
        }
    }

    protected function _preDataForUsing(&$data)
    {
        parent::_preDataForUsing($data);

        if (isset($data['account_info'])) {
            $data['account_info'] = OSC::decode($data['account_info'], true);
        }
    }

    protected function _afterDelete()
    {
        parent::_afterDelete();
        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('alias', "destination='post/{$this->getId()}'", 1);
        } catch (Exception $ex) {

        }
    }

}
