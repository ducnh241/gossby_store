<?php
/*
 * Copyright (c) 2022. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/* @var $this Helper_Backend_Template */

$filter_tags = $params['filter_tags'];

$this
    ->push(['filter/tag.scss'], 'css')
    ->push(['filter/tag.js'], 'js');

?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
        </div>
        <div>
            <?php if ($this->checkPermission('filter/tag/list/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>"
                   class="btn btn-primary btn-small"><?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New
                    Tag</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if (count($filter_tags) > 0) : ?>
        <form method="post" action="<?php echo $this->getUrl('*/*/*'); ?>">
            <table class="grid grid-borderless tag-table">
                <tr>
                    <th style="text-align: center;" width="1%">ID</th>
                    <th style="text-align: left; width: 400px">Tag title</th>
                    <th style="text-align: left;">Image</th>
                    <th style="text-align: left;">Other title</th>
                    <th style="width: 100px; text-align: center">Show</th>
                    <th style="width: 100px; text-align: center">Break down</th>
                    <th style="width: 100px; text-align: center">Type</th>
                    <th style="width: 100px; text-align: center">Status</th>
                    <th style="width: 50px; text-align: center">Action</th>
                </tr>
                <?php /* @var $product Model_Catalog_Product */ ?>
                <?php foreach ($filter_tags as $filter_tag) : ?>
                    <?php
                    if ($filter_tag['type'] == Model_Filter_Tag::TYPE_ONE_CHOICE) {
                        $tag_type = '<span class="badge badge-blue"> One choice </span>';
                    } else {
                        $tag_type = '<span class="badge badge-yellow"> Multiple choice </span>';
                    }

                    $tag_status = $filter_tag['lock_flag'] == Model_Filter_Tag::STATE_TAG_UNLOCK ? '<span class="badge badge-green"> Unlock </span>' : '<span class="badge badge-gray"> Lock </span>';
                    ?>
                    <tr class="<?php if ($filter_tag['image']): ?>tag-table-has-image<?php endif; ?> <?php if ($filter_tag['parent_id']): ?>tag-table-children<?php endif; ?> <?php if ($filter_tag['is_last_item']): ?>tag-table-last-item<?php endif; ?> <?= $filter_tag['add_class'] ?> <?= 'tag-table-level-' . $filter_tag['level'] ?>" >
                        <td style="text-align: center">
                            <?= $filter_tag['id'] ?>
                        </td>
                        <td>
                            <div class="tag-table-hierarchy">
                                <?php if ($filter_tag['level']): ?>
                                    <div>
                                        <?php
                                        for ($i = 0; $i < $filter_tag['level']; $i++) {
                                            echo '<span></span>';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="tag-table-title">
                                <?php if ($filter_tag['level']): ?>
                                    <div class="tag-table-level">
                                        <?php
                                        for ($i = 0; $i < $filter_tag['level']; $i++) {
                                            echo '<span></span>';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <input type="number" class="tag-table-position styled-input" min="0"
                                       name="data[<?= $filter_tag['id'] ?>][position]" id="input-title" width="70px"
                                       value="<?= $this->safeString($filter_tag['position']) ?>"/>
                                <input type="hidden"
                                       name="data[<?= $filter_tag['id'] ?>][id]"
                                       value="<?= $filter_tag['id'] ?>"/>
                                <span class="tag-list-title"><?= $filter_tag['title'] ?></span>
                            </div>
                        </td>
                        <td width="1%">
                            <div class="tag-list-image <?php if (!$filter_tag['image']): ?>hidden<?php endif; ?>" <?php if ($filter_tag['image']): ?>style="background-image: url(<?= OSC::core('aws_s3')->getStorageUrl($filter_tag['image']) ?>)" data-image="<?= OSC::core('aws_s3')->getStorageUrl($filter_tag['image']) ?>"<?php endif; ?> ></div>
                        </td>
                        <td>
                            <?= str_replace(',', ', ', trim($filter_tag['other_title'], ', '))?>
                        </td>
                        <td style="text-align: center">
                            <?php if ($filter_tag['parent_id'] == 0) : ?>
                                <div class="styled-checkbox mr5" style="display: none" >
                                    <input type="checkbox" value="1"
                                           name="data[<?= $filter_tag['id'] ?>][show]" checked/>
                                    <ins><?= $this->getIcon('check-solid') ?></ins>
                                </div>
                            <?php else: ?>
                                <div class="styled-checkbox mr5">
                                    <input type="checkbox" value="1" name="data[<?= $filter_tag['id'] ?>][show]"
                                           <?php if ($filter_tag['is_show_filter'] == Model_Filter_Tag::SHOW_FILTER): ?>checked<?php endif; ?> />
                                    <ins><?= $this->getIcon('check-solid') ?></ins>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center">
                            <?php if ($filter_tag['parent_id'] == 0 && $filter_tag['is_break_down_keyword']) {
                                echo "<span class='badge badge-green'>YES</span>";
                            }?>
                        </td>
                        <td style="text-align: center">
                            <?php if ($filter_tag['parent_id'] == 0) {
                                echo $tag_type;
                            } else {
                                echo '—';
                            } ?>
                        </td>
                        <td style="text-align: center">
                            <?= $tag_status ?>
                        </td>
                        <td style="text-align: center">
                            <div style="display: flex; justify-content: center">
                                <?php if ($this->checkPermission('filter/tag/list/edit')) : ?>
                                    <a class="btn btn-small btn-icon"
                                       data-insert-cb="initUploadTagImage"
                                       data-id="<?= $filter_tag['id'] ?>"
                                       data-title="<?= $filter_tag['title'] ?>"
                                       data-image-value="<?= $filter_tag['image'] ?>"
                                       data-image-url="<?= $filter_tag['image'] ? OSC::core('aws_s3')->getStorageUrl($filter_tag['image']) : '' ?>"
                                       data-upload-url="<?= $this->getUrl('filter/tag/uploadImage') ?>"
                                    ><?= $this->getIcon('add-image') ?></a>
                                    <a class="btn btn-small btn-icon"
                                       href="<?php echo $this->getUrl('*/*/post', ['id' => $filter_tag['id']]); ?>"
                                    ><?= $this->getIcon('pencil') ?></a>
                                <?php endif ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="action-bar">
                <input type="hidden" name="action" value="post_form"/>
                <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
            </div>
        </form>
    <?php else : ?>
        <div class="no-result">No tags added yet.</div>
    <?php endif; ?>
</div>
