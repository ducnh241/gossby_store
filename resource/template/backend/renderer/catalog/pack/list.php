<?php
$this->push([
    'core/location.js',
    'catalog/pack.js'
], 'js');
?>

<div class="block m25">
    <?php if (isset($params['configured_product_types']) && count($params['configured_product_types']) && $params['configured_product_types']->length()) : ?>
        <table class="grid grid-borderless" data-insert-cb="tooltipInit">
            <tr>
                <th style="width: 150px; text-align: center">ID</th>
                <th style="text-align: left; width: 300px">Product Type</th>
                <th style="text-align: left; width: 300px">Pack Auto</th>
                <th class="td_group_btn"></th>
            </tr>
            <?php foreach ($params['configured_product_types'] as $key => $model) : ?>
                <tr data-content="1">
                    <td style="text-align: center"><?= $key + 1 ?></td>
                    <td style="text-align: left"><?= $model->data['title'] ?></td>
                    <td style="text-align: left">
                        <?php if ($model->getPackAutoDefault()): ?><span class="badge badge-green mb5">ON</span><?php else: ?><span class="badge badge-gray mb5">OFF</span><?php endif; ?>
                    </td>
                    <td style="text-align: right">
                        <?php if ($this->checkPermission('catalog/super|catalog/product/full|catalog/product/pack')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?= $this->getUrl('*/*/detail', ['id' => $model->getId()]); ?>"
                            >
                                <?= $this->getIcon('pencil') ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="no-result">No product pack created yet.</div>
    <?php endif; ?>
</div>
