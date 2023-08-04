<?php
/* @var $this Helper_Backend_Template */
$this->push(['post/post.scss'], 'css');
$this->push(['shop/request_payout.js', 'post/common.js'],'js');
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('post/post/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>"
                   class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Post</a>
            <?php endif; ?>
            <?php if ($this->checkPermission('shop/account/delete/bulk')) : ?>
                <div class="btn btn-danger btn-small ml5" data-insert-cb="initAccBulkDeleteBtn"
                     data-link="<?= $this->getUrl('*/*/bulkDelete') ?>"
                     data-confirm="<?= $this->safeString('Do you want to delete selected posts?') ?>">
                    Delete
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">
                    <div class="styled-checkbox">
                        <input type="checkbox" data-insert-cb="initCheckboxSelectAll"
                               data-checkbox-selector="input[name='post_id']"/>
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </div>
                </th>
                <th style="width: 50px;">Image</th>
                <th style="text-align: left;">Title</th>
                <th style="text-align: left;">Slug</th>
                <th style="text-align: left;">Author</th>
                <th style="text-align: left; width: 100px">Visibility</th>
                <th style="text-align: left; width: 100px">Views</th>
                <th style="text-align: left; width: 100px">Unique Views</th>
                <th style="text-align: left">Date Added</th>
                <th style="text-align: left">Date Modified</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $navigation Model_Post_Post */ ?>
            <?php foreach ($params['collection'] as $post) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="post_id" value="<?= $post->getId() ?>"/>
                            <ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center">
                        <div class="post-image-preview"
                             style="background-image: url(<?= $this->imageOptimize($post->getImageUrl(), 300, 300, false) ?>)"></div>
                    </td>
                    <td style="text-align: left"><?= $post->data['title'] ?></td>
                    <td style="text-align: left"><?= !empty($post->data['meta_tags']['slug']) ? $post->data['meta_tags']['slug'] : $post->data['slug'] ?></td>
                    <td style="text-align: left">
                        <?php if (isset($post->data['author'])): ?>
                            <?=$post->data['author']?>
                        <?php endif;?>
                    <td style="text-align: left">
                        <?php if ($post->data['published_flag'] == 1): ?>
                            <span class="badge badge-green">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-danger">No</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: left"><?= $post->data['visits'] ?></td>
                    <td style="text-align: left"><?= $post->data['unique_visits'] ?></td>
                    <td style="text-align: left"><?= date('d/m/Y H:i:s', $post->data['added_timestamp']);?></td>
                    <td style="text-align: left">
                        <?php if ($post->data['modified_timestamp']): ?>
                            <?= date('d/m/Y H:i:s', $post->data['modified_timestamp'])?>
                        <?php endif;?>
                    </td>
                    <td style="text-align: right">
                        <?php if ($post->data['visits'] > 0) : ?>
                        <a class="btn btn-small btn-icon" data-insert-cb="initPostDetailTracking" data-title="Tracking referer: <?= $post->data['title']; ?>" data-url="<?= $this->getUrl('*/*/trackingPost', array('post_id' => $post->getId())) ?>"><?= $this->getIcon('analytics') ?></a>
                        <?php endif; ?>

                        <a class="btn btn-small btn-icon" href="<?= $post->getDetailUrl() . '?token=' . base64_encode(Model_Post_Post::POST_PREVIEW_CODE) ?>"
                           target="_blank"><?= $this->getIcon('eye-regular') ?></a>

                        <?php if ($this->checkPermission('post/post/edit')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?php echo $this->getUrl('*/*/post', array('id' => $post->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('post/post/delete')) : ?>
                            <?php $post_title = addslashes($post->data['title']); ?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the post \"{$post_title}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $post->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No account was found to display</div>
    <?php endif; ?>
</div>