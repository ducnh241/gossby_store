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
 * OSC Backend Controller
 *
 * @package Controller_Backend_Index
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Controller_Backend_Index extends Abstract_Backend_Controller {

    protected $_check_hash = false;

    public function __construct() {
        parent::__construct();
        OSC::core('language')->load('user/common');
    }

    public function actionIndex() {
        $this->actionDashboard();
    }

    public function actionDashboard() {
        if ($this->checkPermission('report', false)) {
//            static::redirect($this->getUrl('report/backend/index'));
        }

        $columns = array();

        OSC::core('observer')->dispatchEvent('collect_backend_dashboard_widget', array('columns' => &$columns));

        $this->output($this->getTemplate()->build('backend/dashboard', array('columns' => $columns)), false);
    }

    public function actionAuthentication() {
        $this->forward('user/backend_authentication/index');
    }

}
