<?php
/* @var $this Helper_Backend_Template */
$this->push(['post/post.scss'], 'css');
$this->push('shop/request_payout.js','js');
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('shop/account/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>"
                   class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Account</a>
            <?php endif; ?>
            <?php if ($this->checkPermission('shop/account/delete/bulk')) : ?>
                <div class="btn btn-danger btn-small ml5" data-insert-cb="initAccBulkDeleteBtn"
                     data-link="<?= $this->getUrl('*/*/bulkDelete') ?>"
                     data-confirm="<?= $this->safeString('Do you want to delete selected account?') ?>">
                    <?= $this->getIcon('trash-alt-regular', ['class' => 'mr5']) ?>Delete
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
                <th style="text-align: center; 20%">Title</th>
                <th style="text-align: center; min-width: 15%">Payment Provider</th>
                <th style="text-align: center; width: 15%">Account Info</th>
                <th style="text-align: center; min-width: 15%">Total Withdraw</th>
                <th style="text-align: center; min-width: 15%">Total Transaction</th>
                <th style="text-align: center; min-width: 15%">Activated Flag</th>
                <th style="text-align: center; min-width: 15%">Default Flag</th>
                <th style="width: 100px; text-align: center"></th>
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
                    <td style="text-align: left"><?= $post->data['title'] ?></td>
                    <td style="text-align: center;text-transform: capitalize;"><?= !empty($post->data['account_type']) ? $post->data['account_type'] : '' ?></td>
                    <td style="text-align: center;"><?= !empty($post->data['account_info']['email']) ? $post->data['account_info']['email'] : '' ?></td>
                    <td style="text-align: center"><?= !empty($post->data['total_withdraw']) ? OSC::helper('catalog/common')->integerToFloat($post->data['total_withdraw']) : '$0' ?></td>
                    <td style="text-align: center"><?= !empty($post->data['total_transaction']) ? number_format($post->data['total_transaction']) : '0' ?></td>
                    <td style="text-align: center">
                        <?php if ($post->data['activated_flag'] == 1): ?>
                            <span class="badge badge-green">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-danger">No</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center">
                        <?php if ($post->data['default_flag'] == 1): ?>
                            <span class="badge badge-green">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-danger">No</span>
                        <?php endif; ?>
                    </td>

                    <td style="text-align: right">
                        <?php if ($this->checkPermission('shop/account/edit')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?php echo $this->getUrl('*/*/post', array('id' => $post->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('shop/account/delete')) : ?>
                            <?php $post_title = addslashes($post->data['title']); ?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the account \"{$post_title}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $post->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
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