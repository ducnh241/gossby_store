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
/* @var $this Helper_Backend_Template */
?>
<?php

$this->addComponent('backend');

$lang = OSC::core('language')->get();

$this->push(['[frontend_template]base.scss'], 'css');
        
$params['content'] = <<<EOF
<div class="body-wrap">
    <div class="content-wrap">
        {$this->build('backend/html/header')}
        {$this->getMessage()}
        {$params['content']}
        {$this->build('backend/html/footer')}
    </div>
</div>
EOF;

if (!$this->registry('EMPTY_PAGE')) {
    $this->addBodyClass('sidebar-toggled');

    $params['content'] = $this->build('backend/html/sidebar') . $this->build('backend/html/topbar') . $params['content'];
}

$SPRITE_URL = OSC::core('template')->getFile('image/sprites.svg');

$this->push(array('backend/style.scss'), 'css')
        ->push(array('/script/core/core/UI/window.js', 'backend/common.js', '[core]core/search.js'), 'js')
        ->push(array('backend/UI/window.css'), 'css')
        ->push(<<<EOF
if(typeof $.OSC_UI_Form_Select_Menu_Collect_Additional_Height != 'undefined') {
    $.OSC_UI_Form_Select_Menu_Collect_Additional_Height = function() {
        return $('.dock').height();
    }
}
                
$.loadSVGIconSprites('{$SPRITE_URL}');
$.loadSVGIconSprites('{$this->getImage('sprites.svg')}');
EOF
                , 'js_code');

$hash = OSC::getHash();

$params['url_js_init'] = <<<EOF
var OSC_BACKEND_TPL_BASE_URL = '{$this->tpl_base_url}';
var OSC_BACKEND_TPL_CSS_BASE_URL = OSC_BACKEND_TPL_BASE_URL + '/style';
var OSC_BACKEND_TPL_JS_BASE_URL = OSC_BACKEND_TPL_BASE_URL + '/script';
var OSC_BACKEND_TPL_IMG_BASE_URL = OSC_BACKEND_TPL_BASE_URL + '/image';   
var OSC_HASH = '{$hash}';
EOF;

echo OSC::core('template')->build('html', $params, true, true);
