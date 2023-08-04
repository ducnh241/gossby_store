<?php
/* @var $this Helper_Backend_Template */

$this->push(['catalog/pack.js', 'vendor/tooltipster/tooltipster.bundle.min.js'], 'js');
$this->push(['catalog/pack.scss', 'vendor/tooltipster/tooltipster.bundle.min.css'], 'css');

$this->addComponent('select2');
$this->addComponent('location_group');

?>
<form action="<?= $this->getUrl('*/*/*', ['id' => $params['id']]); ?>" method="post" class="post-frm product-post-frm p25">
    <div class="block p20">
        <div class="header-grid">
            <div class="flex--grow">Pack Of <?= $params['product_type_title'] ?></div>
        </div>
        <div>
            <div>
                <table class="grid grid-borderless" id="pack_list_tbl">
                    <tr>
                        <th align="left" style="width: 150px">Title</th>
                        <th align="left" style="width: 150px">Quantity</th>
                        <th align="left" style="width: 200px">Discount Type</th>
                        <th align="left" style="width: 200px">Discount Value</th>
                        <th align="left" style="width: 200px">Marketing Point Rate</th>
                        <th align="left" style="width: 350px">Note</th>
                        <th>&nbsp;</th>
                    </tr>

                    <?php foreach($params['collection'] as $pack): ?>
                        <?= $this->build('catalog/pack/item', [
                            'pack' => $pack,
                            'product_type_title' => $params['product_type_title']
                        ]) ?>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php if (!$params['collection']->length()) : ?>
                <div class="no-result">No product pack created yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            Go Back
        </a>
    </div>
</form>

<script>
    $(document).ready(function () {
        $('.multiple-selection-options').select2({
            width: '100%',
            theme: 'default select2-container--custom',
        });
    });
</script>
