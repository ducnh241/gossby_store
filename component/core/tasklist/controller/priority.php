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
 * OSC_Framework::Exception_Upload
 *
 * @package OSC_Frontend_Controller
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Controller_Tasklist_Priority extends Abstract_Tasklist_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->getAccount()->isAdmin()) {
            if ($this->_request->isAjax()) {
                $this->_ajaxError($this->_('core.err_no_permission'));
            }

            $this->addErrorMessage($this->_('core.err_no_permission'));

            static::redirect(OSC::$base_url);
        }
    }

    public function actionIndex() {
        $this->forward('list');
    }

    public function actionList() {
        $collection = OSC::model('tasklist/priority')->getCollection()->sort('priority_value', OSC_Database::ORDER_ASC)->load();

        $this->output($this->getTemplate()->build('tasklist/priority/list', array('collection' => $collection)));
    }

    public function actionPost() {
        $model = OSC::model('tasklist/priority');

        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
                static::redirect($this->getUrl('tasklist/priority/list'));
            }
        }

        if ($this->_request->get('title')) {
            try {
                $data = array();

                $data['title'] = trim($this->_request->get('title'));

                if (OSC::core('string')->strlen($data['title']) < 1) {
                    throw new Exception('Priority title is empty');
                }

                $data['priority_value'] = intval($this->_request->get('priority_value'));

                $model->setData($data)->save();

                $this->addMessage('The priority label has saved successfully');
                static::redirect($this->getUrl('tasklist/priority/list'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $this->output($this->getTemplate()->build('tasklist/priority/postForm', array('model' => $model)));
    }

    public function actionDelete() {
        $model = OSC::model('tasklist/priority');

        $id = intval($this->_request->get('id'));

        if ($id < 1) {
            $this->addErrorMessage($this->_('core.err_data_incorrect'));
        } else {
            try {
                $model->load($id)->delete();
                $this->addMessage('Priority [' . $model->data['title'] . '] has deleted successfully');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl('tasklist/priority/list'));
    }

}
