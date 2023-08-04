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
 * OSECORE Abstract
 *
 * @package Abstract_Core_Model
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class Abstract_Core_Model extends OSC_Database_Model {

    /**
     *
     * @var boolean 
     */
    protected $_multi_lang = false;
    protected $_allow_empty_lang = false;

    /**
     *
     * @var string 
     */
    protected $_lang_key = null;

    /**
     * @var bool
     */
    protected $_allow_write_log = false;

    protected $_data_old = null;


    /**
     * 
     * @param integer $id
     * @return array
     */
    protected function _getCondition($id) {
        $condition = parent::_getCondition($id);

        if ($condition) {
            if ($this->_multi_lang) {
                $lang_key = $this->_lang_key;

                if (!$lang_key) {
                    $lang_key = OSC::core('language')->current_lang_key;
                }

                if ($lang_key) {
                    if ($this->_allow_empty_lang) {
                        $condition['condition'] = "({$condition['condition']}) AND (`lang_key` = '' OR `lang_key` LIKE '{$lang_key}')";
                    } else {
                        $condition['condition'] = "({$condition['condition']}) AND `lang_key` LIKE '{$lang_key}'";
                    }
                }
            }
        }

        return $condition;
    }

    /**
     * 
     * @param array &$params
     */
    protected function _beforeGetCollection(&$params) {
        parent::_beforeGetCollection($params);
        $params['multi_lang'] = $this->_multi_lang;
        $params['allow_empty_lang'] = $this->_allow_empty_lang;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        if ($this->getId() > 0) {
            $this->_data_old = $this->_orig_data;
        }

        if (!$this->_multi_lang) {
            return true;
        }

        $data = $this->_collectDataForSave();

        $lang = OSC::core('language')->get();

        if (isset($data['lang_key'])) {
            if ($data['lang_key'] === false || $data['lang_key'] === null || $data['lang_key'] === '') {
                if ($this->_allow_empty_lang) {
                    $data['lang_key'] = '';
                } else {
                    $errors[] = $lang['core.err_lang_key_incorrect'];
                }
            } else {
                $data['lang_key'] = strtolower(preg_replace('/[^a-zA-Z_]/', '', $data['lang_key']));

                if (strlen($data['lang_key']) != 2) {
                    $errors[] = $lang['core.err_lang_key_incorrect'];
                } else if (!isset(OSC::core('language')->lang_map[$data['lang_key']])) {
                    $errors[] = $lang['core.err_lang_key_not_exist'];
                }
            }
        } else if ($this->getActionFlag() == self::INSERT_FLAG) {
            $data['lang_key'] = OSC::core('language')->current_lang_key;
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }

        return true;
    }

    protected function _afterSave()
    {
        parent::_afterSave();
        if ($this->getLastActionFlag() == static::UPDATE_FLAG && $this->_allow_write_log && md5(json_encode($this->_data_old ?? [])) != md5(json_encode($this->data ?? []))) {
            OSC::core('observer')->dispatchEvent('log_model', [
                'action' => static::UPDATE_FLAG,
                'model' => $this
            ]);
        }

        if ($this->getLastActionFlag() == static::INSERT_FLAG && $this->_allow_write_log) {
            OSC::core('observer')->dispatchEvent('log_model', [
                'action' => static::INSERT_FLAG,
                'model' => $this
            ]);
        }
    }

    protected function _afterDelete()
    {
        parent::_afterDelete();

        if ($this->_allow_write_log) {

            OSC::core('observer')->dispatchEvent('log_model', [
                'action' => static::DELETE_FLAG,
                'model' => $this
            ]);
        }
    }

    public function generateSlug($text) {
        return OSC::core('string')->cleanAliasKey($text);
    }

}
