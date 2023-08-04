<?php
/* @var $this Helper_Backend_Template */
$this->push('shop/common.scss', 'css');
$this->push('shop/request_payout.js', 'js');
?>
<div class="refresh-top">
    Hit refresh to get lastest data about your profit
    <u class="refresh" style="color: #20B3B5;cursor: pointer;font-weight: bold;">Refresh</u>
</div>
<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('payoutAccount/request/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>"
                   class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Request</a>
            <?php endif; ?>

        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left;">Title</th>
                <th style="text-align: left;">Amount</th>
                <th style="text-align: left;">Status</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>

            <?php foreach ($params['collection'] as $post) : ?>
                <tr>
                    <td style="text-align: left"><?= $post->data['payout_account_title'] ?></td>
                    <td style="text-align: left"><?= OSC::helper('catalog/common')->formatPriceByInteger($post->data['amount']) ?></td>
                    <td style="text-align: left;text-transform: capitalize;"><?= $post->data['status'] ?></td>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('payoutAccount/account/edit')) : ?>
                            <?php if ($post->data['status'] == 'pending'): ?>
                                <div id="mdiv" class="cancel_payout_requeset" data-payout-request-id="<?= $post->getId() ?>" data-amount="<?= OSC::helper('catalog/common')->integerToFloat($post->data['amount']); ?>">
                                    <div class="mdiv">
                                        <div class="md"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No post was found to display</div>
    <?php endif; ?>
</div>