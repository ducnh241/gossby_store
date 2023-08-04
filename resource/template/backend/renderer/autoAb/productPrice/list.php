<?php

$this->push([
    'autoAb/productPrice.js',
], 'js');
$config_collection = $params['config_collection'];
$pager = $this->buildPager(
    $config_collection->getCurrentPage(),
    $config_collection->collectionLength(),
    $config_collection->getPageSize(),
    'page'
);

$has_beta_product_permission = $this->checkPermission('autoAb/productPrice/ab_semi_product');

?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('autoAb/super|autoAb/productPrice/add')) : ?>
                <a href="<?= $this->getUrl('*/*/getPostForm', ['config_type' => 1]); ?>" class="btn btn-primary btn-small mr10">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add new AutoAB Beta Product
                </a>
            <?php endif; ?>
            <?php if ($has_beta_product_permission) : ?>
                <a href="<?= $this->getUrl('*/*/getPostForm'); ?>" class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add new AutoAB Campaign
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($config_collection->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 50px; text-align: center">ID</th>
                <th style="text-align: left">Title</th>
                <th style="width: 70px; text-align: right"></th>
            </tr>
            <?php foreach ($config_collection as $config) : ?>
                <tr>
                    <td style="width: 50px; text-align: center"><?= $config->getId() ?></td>
                    <td style="text-align: left">
                        <div>
                            <?= $config->data['title'] ?>
                        </div>
                    </td>
                    <td style="text-align: right; width: 150px">
                        <?php if ($this->checkPermission('autoAb/super|autoAb/productPrice/view_tracking') ||
                            ($config->data['config_type'] === Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST && $has_beta_product_permission)
                        ) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?= $this->getUrl('*/*/viewTracking', ['id' => $config->getId()]); ?>">
                                <?= $this->getIcon('analytics') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('autoAb/super|autoAb/productPrice/edit') ||
                        ($config->data['config_type'] === Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST && $has_beta_product_permission)
                        ) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?= $this->getUrl('*/*/getPostForm', [
                                   'id' => $config->getId(),
                                   'config_type' => $config->data['config_type']
                               ]); ?>">
                                <?= $this->getIcon('pencil') ?>
                            </a>
                        <?php endif; ?>
                        <?php if (
                            $config->data['status'] === Model_AutoAb_ProductPrice_Config::STATUS_ALLOW &&
                            ($this->checkPermission('autoAb/super|autoAb/productPrice/edit') || ($config->data['config_type'] === Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST && $has_beta_product_permission))
                        ) : ?>
                            <div class='btn btn-small btn-icon'
                                 data-insert-cb="initStopABTestPrice"
                                 data-config-id="<?= $config->getId() ?>"
                                 data-condition-type="<?= $config->data['condition_type'] ?>"
                                 data-price-range="<?= OSC::encode($config->data['price_range']) ?>"
                            >
                                <?= $this->getIcon('warning') ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('autoAb/super|autoAb/productPrice/delete') || ($config->data['config_type'] === Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST && $has_beta_product_permission)) : ?>
                            <?php $confirm_message = "Do you want to delete the group \"{$config->data['title']}\"?"?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString($confirm_message) ?>', '<?= $this->getUrl('*/*/delete', ['id' => $config->getId()]) ?>')">
                                <?= $this->getIcon('trash-alt-regular') ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($pager) : ?>
            <div class="pagination-bar p20">
                <?= $pager ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="no-result">No auto ab test campaign was found to display</div>
    <?php endif; ?>
</div>
