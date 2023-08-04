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
OSC_Controller::registerBind('backend', 'backend');

OSC_Observer::registerObserver('collect_backend_dashboard_widget', array('Observer_Backend_Backend', 'collectWidget'));
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Backend_Backend', 'collectPermKey'));

OSC_Template::registerComponent(array('key' => 'backend', 'depends' => array('backend_formuipackage')));
OSC_Template::registerComponent(
        array(
    'key' => 'scroller',
    'depends' => array('dragger')
        ), array('type' => 'js', 'data' => 'backend/UI/scroller.js'), array('type' => 'css', 'data' => 'backend/UI/scroller.css')
);
OSC_Template::registerComponent(
        'backend_uiforminput', array('type' => 'js', 'data' => 'backend/UI/form/input.js'), array('type' => 'css', 'data' => 'backend/UI/form/input.css')
);
OSC_Template::registerComponent(
        array(
    'key' => 'backend_uiformselect',
    'depends' => array('scroller', 'togglemenu')
        ), array('type' => 'js', 'data' => 'backend/UI/form/select.js'), array('type' => 'css', 'data' => 'backend/UI/form/select.css')
);
OSC_Template::registerComponent(
        array(
    'key' => 'backend_uiformdatepicker',
    'depends' => array('togglemenu', 'uiforminput')
        ), array('type' => 'js', 'data' => 'backend/UI/form/datePicker.js'), array('type' => 'css', 'data' => 'backend/UI/form/datePicker.css')
);
OSC_Template::registerComponent(array('key' => 'backend_uiformeditor', 'depends' => 'editor'));
OSC_Template::registerComponent(
        array(
            'key' => 'backend_formuipackage',
            'depends' => array('backend_uiforminput', 'backend_uiformselect', 'backend_uiformdatepicker', 'backend_uiformeditor')
        )
);
OSC_Template::registerComponent(
        array(
    'key' => 'backend_grid',
    'depends' => array('togglemenu', 'dragger')
        ), array('type' => 'css', 'data' => 'backend/UI/grid.css'), array('type' => 'js', 'data' => 'backend/UI/grid/filter.js')
);
OSC_Template::registerComponent(
        array(
    'key' => 'backend_form',
    'depends' => array('formuipackage')
        ), array('type' => 'css', 'data' => 'backend/UI/form.css'), array('type' => 'js', 'data' => 'backend/UI/form.js')
);

OSC_Search::registerSearchInstance(
        'backend', array(
    'adapter' => 'database',
    'read_adapter' => 'search',
    'write_adapter' => 'search',
    'tblname' => 'backend_index',
    'keyword_field' => 'keywords'
        )
);
OSC_Template::registerComponent('location_group', ['type' => 'css', 'data' => ['/template/backend/style/elements/select2_custom_location.scss', '/template/backend/style/vendor/flag-icon/css/flag-icon.min.css']], ['type' => 'js', 'data' => '/template/backend/script/core/location.js'], ['type' => 'template', 'data' => '/country/group/addForm']);
OSC_Template::registerComponent('select2', ['type' => 'css', 'data' => '/template/backend/style/common/select2.min.css'], ['type' => 'js', 'data' => '/template/backend/script/common/select2.min.js']);

OSC_Search::registerSearchBind('backend', 'backend');
OSC_Database::registerDBBind('search', 'default');
/*
OSC_Template::registerComponent(
        'uiformcheckbox',
        array('type' => 'js', 'data' => 'core/core/UI/form/checkbox.js'),
        array('type' => 'css', 'data' => 'core/UI/form/checkbox.css')
);
OSC_Template::registerComponent(
        'uiformradio',
        array('type' => 'js', 'data' => 'core/core/UI/form/radio.js'),
        array('type' => 'css', 'data' => 'core/UI/form/radio.css')
);*/

OSC_Template::registerComponent('location_group', ['type' => 'css', 'data' => ['/template/backend/style/elements/select2_custom_location.scss', '/template/backend/style/vendor/flag-icon/css/flag-icon.min.css']], ['type' => 'js', 'data' => '/template/backend/script/core/location.js'], ['type' => 'template', 'data' => '/country/group/addForm']);
