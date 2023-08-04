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
<div class="p25">
    <form action="<?php echo $this->getUrl('*/*/*', array('section_id' => $params['section']->getId(), 'parent_id' => $params['cat_info']['id'], 'id' => $params['model']->getId())); ?>" method="post" enctype="multipart/form-data">        
        <div class="tab-system" data-insert-cb="initTabSystem" selected-tab="general">
            <ul class="tabs">
                <li tab-key="general">Thông tin chung</li>
                <li tab-key="seo">Cài đặt SEO</li>
            </ul>
            <div class="tabs-content">
                <div tab-key="general">
                    <div class="block">
                        <div class="plr15">
                            <table class="frm-grid">
                                <tr>
                                    <td><label for="input-position">Section</label></td>
                                    <td><?php echo $params['section']->data['name']; ?></td>
                                </tr>
                                <tr>
                                    <td><label for="input-name">Tên thư mục</label></td>
                                    <td><input type="text" name="name" id="input-title" value="<?php echo htmlentities($params['model']->data['name'], ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-position">Thư mục cha</label></td>
                                    <td><?php echo $params['cat_info']['name']; ?></td>
                                </tr>
                                <tr>
                                    <td><label for="input-redirect-url">Chuyển hướng sang URL</label></td>
                                    <td><input type="text" name="redirect_url" id="input-redirect-url" value="<?php echo $this->safeString($params['model']->data['redirect_url']); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-position">Chỉ mục sắp xếp</label></td>
                                    <td><input type="text" name="position" id="input-position" value="<?php echo htmlentities($params['model']->data['position'], ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="switcher-highlight">Hiển thị danh sách bài viết</label></td>
                                    <td><input type="checkbox" class="mrk-switcher" name="listing_flag" value="1" id="switcher-highlight"<?php if ($params['model']->data['listing_flag'] == 1) : ?> checked="checked"<?php endif; ?> /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div tab-key="seo">
                    <div class="block">
                        <div class="plr15">
                            <table class="frm-grid">
                                <tr>
                                    <td><label for="input-seo-title">SEO :: Title</label></td>
                                    <td><input type="text" name="seo_title" id="input-seo-title" value="<?php echo htmlentities($params['model']->data['seo_title'], ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-seo-keywords">SEO :: Keywords</label></td>
                                    <td><input type="text" name="seo_keywords" id="input-seo-keywords" value="<?php echo htmlentities($params['model']->data['seo_keywords'], ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>" /></td>
                                </tr>
                                <tr>
                                    <td><label for="input-seo-description">SEO :: Description</label></td>
                                    <td><input type="text" name="seo_description" id="input-seo-description" value="<?php echo htmlentities($params['model']->data['seo_description'], ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="frm-action-bar">
            <button type="submit" class="btn btn-large btn-red"><i class="fa fa-save"></i>Cập nhật</button>
        </div>
    </form>
</form>
</div>