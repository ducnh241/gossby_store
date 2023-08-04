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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
/* @var $this Helper_Frontend_Template */
?>
<?php
$this->push(
    [
        'base/_normalize.scss',
        'base/_typography.scss',
        'base.scss',
        "main.scss"
    ],
    'css'
)
    ->push(
        [
            '[core]catalog/common.js',
            '/script/core/core/UI/window.js'
        ],
        'js'
    );

$params['url_js_init'] = <<<EOF
var OSC_FRONTEND_TPL_BASE_URL = '{$this->tpl_base_url}';
var OSC_FRONTEND_TPL_CSS_BASE_URL = OSC_FRONTEND_TPL_BASE_URL + '/style';
var OSC_FRONTEND_TPL_JS_BASE_URL = OSC_FRONTEND_TPL_BASE_URL + '/script';
var OSC_FRONTEND_TPL_IMG_BASE_URL = OSC_FRONTEND_TPL_BASE_URL + '/image';  
var OSC_IS_MOBILE = false;
EOF;

if (!isset($params['metadata_tags'])) {
    $params['metadata_tags'] = array();
}

$params['metadata_tags']['viewport'] = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0';
$params['metadata_tags']['theme-color'] = '#ffffff';

$params['content'] = '<div id="mobile-checker"></div>' . $params['content'];

echo OSC::core('template')->build('html', $params, true, true);
