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
 * @copyright    Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

class Controller_ContactUs_Backend_Index extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('page/contact_us');

        $this->getTemplate()
            ->setPageTitle('Contact Us Config')
            ->setCurrentMenuItemKey('page/contact_us')
            ->addBreadcrumb(array('user', 'Contact Us Config'), $this->getCurrentUrl());
    }

    public function actionIndex()
    {
        $key_contact_us = [];
        if (OSC::isPrimaryStore()) {
            $key_contact_us = [
                'contact_us/is_enable_noti' => 'Enable Temporary Live Chat Error Display',
                'contact_us/is_enable_live_chat' => 'Display Live Chat Information',
            ];
        }
        $key_contact_us = array_merge($key_contact_us, [
            'contact_us/is_enable_email' => 'Display Email Contact Details',
            'contact_us/is_enable_phone' => 'Display Phone Contact Details',
        ]);
        if ($this->_request->get('save') == 1) {
            $setting_values = $this->_request->get('config');
            $new_setting_values = [];
            foreach ($key_contact_us as $setting_key => $setting_title) {
                $new_setting_values[$setting_key] = array_key_exists($setting_key, $setting_values) ? $setting_values[$setting_key] : 0;
            }
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                foreach ($new_setting_values as $key => $new_setting_value) {
                    OSC::helper('core/setting')->set($key, intval($new_setting_value), true);
                }

                $DB->commit();

                OSC::helper('core/setting')->loadFromDB();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                OSC::helper('core/setting')->removeCache();
                static::redirect($this->rebuildUrl(['save' => 0]));
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                $this->addErrorMessage($ex->getMessage());
            }
        }

        $this->output($this->getTemplate()->build('contactUs/post', ['key_contact_us' => $key_contact_us]));
    }
}