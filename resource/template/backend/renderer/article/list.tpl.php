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
    <div class="block">
        <h3>
            <i class="fa fa-folder-o"></i> Thư mục con
            <div class="action-area">
                <a class="btn" data-insert-cb="initCopyLink" href="<?php echo $params['cat_info']['url']; ?>" onclick="return false;"><i class="fa fa-link"></i>Copy link URL</a>
                <?php if ($this->checkPermission("article/global/cat/add|article/section/{$params['section']->getId()}/cat/add")) : ?><a href="<?php echo $this->getUrl('*/*/postCategory', array('section_id' => $params['section']->getId(), 'parent_id' => $params['cat_info']['id'])); ?>" class="btn"><i class="fa fa-plus"></i>Tạo thư mục mới</a><?php endif; ?>
            </div>
        </h3>        
        <?php if (count($params['cat_children']) > 0) : ?>
            <ul class="cat-list">
                <?php foreach ($params['cat_children'] as $cat_child) : ?>
                    <li<?php if ($cat_child->data['system_item_flag']) : ?> system-flag="1"<?php endif; ?>>
                        <a href="<?php echo $this->getUrl('*/*/*', array('section_id' => $cat_child->data['section_id'], 'cat' => $cat_child->getId())); ?>"><i class="fa fa-folder"></i></a>
                        <div class="title"><?php echo $cat_child->data['name']; ?></div>
                        <div class="action-bar">
                            <?php if ($this->checkPermission("article/global/cat/delete|article/section/{$cat_child->data['section_id']}/cat/delete")) : ?><a href="javascript:$.confirmAction('<?php echo htmlentities("Bạn có muốn xóa thư mục \"{$cat_child->data['name']}\" không?", ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>', '<?php echo $this->getUrl('*/*/deleteCategory', array('section_id' => $cat_child->data['section_id'], 'id' => $cat_child->getId())); ?>')"><i class="fa fa-trash-o"></i></a><?php endif; ?>
                            <?php if ($this->checkPermission("article/global/cat/edit|article/section/{$cat_child->data['section_id']}/cat/edit")) : ?><a href="<?php echo $this->getUrl('*/*/postCategory', array('section_id' => $cat_child->data['section_id'], 'id' => $cat_child->getId())); ?>" class="mrk-cat-edit"><i class="fa fa-pencil"></i></a><?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div class="no-item">Không có thư mục con nào trong thư mục hiện hành</div>
        <?php endif; ?>
    </div>
    <div class="block">
        <h3>
            <i class="fa fa-file-text"></i> Bài viết trong thư mục
            <div class="action-area">
                <?php if ($this->checkPermission("article/global/item/add|article/section/{$params['section']->getId()}/item/add")) : ?><a href="<?php echo $this->getUrl('*/*/post', array('section_id' => $params['section']->getId(), 'cat' => $params['cat_info']['id'])); ?>" class="btn"><i class="fa fa-plus"></i>Tạo bài viết mới</a><?php endif; ?>
            </div>
        </h3>    
        <?php if ($params['article_collection']->length() > 0) : ?>        
            <table class="grid">
                <tr>
                    <th style="width: 10px; text-align: center">ID</th>
                    <th style="width: 10px; text-align: left">&nbsp;</th>
                    <th style="text-align: left">Tiêu đề</th>
                    <th style="width: 130px; text-align: center">Bài viết nổi bật</th>
                    <th style="width: 100px; text-align: center">Trang chủ</th>
                    <th style="width: 100px; text-align: right"></th>
                </tr>
                <?php /* @var $article Model_Article_Item */ ?>
                <?php foreach ($params['article_collection'] as $article) : ?>        
                    <tr>
                        <td style="text-align: center"><?php echo $article->getId(); ?></td>
                        <td style="text-align: left"><?php $thumbnail_url = $article->getThumbnailUrl(); ?><?php if ($thumbnail_url) : ?><img src="<?php echo $thumbnail_url ?>?t=<?php echo time(); ?>" height="20" /><?php else : ?>&nbsp;<?php endif; ?></td>
                        <td style="text-align: left"><?php echo $article->data['title']; ?></td>
                        <td style="text-align: center"><i class="fa fa-check<?php if ($article->data['highlight_flag'] == 1) : ?> checked<?php endif; ?>"></i></td>
                        <td style="text-align: center"><i class="fa fa-check<?php if ($article->data['homepage_flag'] == 1) : ?> checked<?php endif; ?>"></i></td>
                        <td style="text-align: right">
                            <a data-insert-cb="initCopyLink" href="<?php echo $article->getDetailUrl(); ?>" onclick="return false;"><i class="fa fa-link"></i></a>
                            <?php if ($this->checkPermission("article/global/item/edit|article/section/{$params['section']->getId()}/item/edit")) : ?><a href="<?php echo $this->getUrl('*/*/post', array('section_id' => $article->data['section_id'], 'id' => $article->getId())); ?>"><i class="fa fa-btn fa-pencil"></i></a><?php endif; ?>
                            <?php if ($this->checkPermission("article/global/item/delete|article/section/{$params['section']->getId()}/item/delete")) : ?><a href="javascript:$.confirmAction('<?php echo htmlentities("Bạn có muốn xóa bài viết \"{$article->data['title']}\" không?", ENT_COMPAT | ENT_HTML401, 'UTF-8'); ?>', '<?php echo $this->getUrl('*/*/delete', array('section_id' => $article->data['section_id'], 'id' => $article->getId())); ?>')"><i class="fa fa-btn fa-trash-o"></i></a><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php $pager = $this->buildPager($params['article_collection']->getCurrentPage(), $params['article_collection']->getTotalItem(), $params['article_collection']->getPageSize(), 'page'); ?>
            <?php if ($pager) : ?><div class="pagination-bar"><?php echo $pager; ?></div><?php endif; ?>
        <?php else : ?>
            <div class="no-item">Không có bài viết nào trong thư mục hiện hành</div>
        <?php endif; ?>
    </div>
</div>