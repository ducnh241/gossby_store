<?php
/* @var $this Helper_Backend_Template */

$this->push([
        'd2/product.js',
        'core/cron.js'
],'js')

?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">

            <?php if ($this->checkPermission('d2/product/add')) : ?>
                <div class="btn btn-primary btn-small"
                     data-insert-cb="initD2CreateProductBtn"
                     data-create-product-url="<?= $this->getUrl('*/*/post') ?>"
                     data-title="Create Product"
                ><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add Products</div>
            <?php endif; ?>

            <?php if ($this->checkPermission('d2/product/delete')) : ?>
                <div class="btn btn-danger btn-small ml5"
                     data-insert-cb="initD2ProductBulkDeleteBtn"
                     data-process-url="<?= $this->getUrl('*/*/bulkDelete') ?>"
                     data-confirm="<?= $this->safeString('Do you want to delete selected products?') ?>"
                >Delete</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="header-grid">
        <?= $this->build('backend/UI/search_form', [
            'process_url' => $this->getUrl('*/*/search'),
            'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']
        ]) ?>
    </div>

    <?php if ($params['collection']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 50px; text-align: center"><div class="styled-checkbox"><input type="checkbox" data-insert-cb="initCheckboxSelectAll" data-checkbox-selector="input[name='product_id']" /><ins><?= $this->getIcon('check-solid') ?></ins></div></th>
                <th style="text-align: center;">Product ID</th>
                <th style="text-align: center;">Title</th>
                <th style="text-align: center;">Added By</th>
                <th style="text-align: center;">Modified By</th>
                <th style="text-align: center;">Date Added</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>

            <?php /** @var Model_D2_Product $product * */?>
            <?php foreach ($params['collection'] as $product) : ?>
                <tr>
                    <td style="text-align: center">
                        <div class="styled-checkbox">
                            <input type="checkbox" name="product_id" value="<?= $product->getId() ?>" /><ins><?= $this->getIcon('check-solid') ?></ins>
                        </div>
                    </td>
                    <td style="text-align: center"><?= $product->data['product_id'] ?></td>
                    <td style="text-align: center"><?= $product->getCampaignProduct() ? $product->getCampaignProduct()->getProductTitle(false, false) : ($product->data['title']. '<span class="text-danger"> (Deleted)</span>') ?></td>
                    <td style="text-align: center"><?= OSC::model('user/member')->load($product->data['added_by'])->data['username'] ?></td>
                    <td style="text-align: center"><?= OSC::model('user/member')->load($product->data['modified_by'])->data['username'] ?></td>
                    <td style="text-align: center"><?= date('d/m/Y H:i:s', $product->data['added_timestamp']) ?></td>
                    <td style="text-align: right">
                        <?php if ($product->getCampaignProduct()) :?>
                            <a class="btn btn-small btn-icon" href="<?= $product->getCampaignProduct()->getDetailUrl() ?>" target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                        <?php endif; ?>

                        <?php if ($this->checkPermission('d2/product/edit')) : ?>
                            <div class="btn btn-small btn-icon"
                                 data-insert-cb="initD2CreateProductBtn"
                                 data-create-product-url="<?php echo $this->getUrl('*/*/post', array('id' => $product->getId())); ?>"
                                 data-id="<?= $product->data['product_id'] ?>"
                                 data-title="Update Product"
                            ><?= $this->getIcon('pencil') ?></div>
                        <?php endif; ?>

                        <?php if ($this->checkPermission('d2/product/delete')) : ?>
                            <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete product \"{$product->data['product_id']}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $product->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>
        </table>
        <?php $pager = $this->buildPager($params['collection']->getCurrentPage(), $params['collection']->collectionLength(), $params['collection']->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>
