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
$keyword_min_length = OSC_Search::KEYWORD_MIN_LENGTH;

$this->push('backend/search.js', 'js')
    ->push(<<<EOF
jQuery.BACKEND_SEARCH_URI = '{$this->getUrl('backend/search/index')}';
jQuery.BACKEND_SEARCH_KEYWORD_MIN_LENGTH = {$keyword_min_length};
EOF
, 'js_code');

$lang = OSC::core('language')->get();
?>
<li class="quick-search">
    <form action="#" id="backend-search-frm" method="post" autocomplete="off">
        <div class="wrap clearfix mrk-toggle-menu" data-tm-menu="#backend-search-result" data-tm-divergent-y="7" data-tm-toggle-mode="br" data-tm-auto-toggle="1">
            <div class="input">
                <input type="text" name="keyword" placeholder="<?php echo $lang['core.enter_here_to_search']; ?>" />
                <img src="<?php echo $this->getImg('core/icon/loading_1.gif'); ?>" />
            </div>
            <button type="submit"><?= $this->getIcon('search') ?></button>
        </div>
        <div id="backend-search-result">
            <div class="no-result"><?php echo $lang['backend.search_no_result']; ?></div>
        </div>
    </form>
</li>