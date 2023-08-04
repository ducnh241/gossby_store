<?php
    $page_size = 5;
    $product_data = [];
    $bypass_min_sold_products = OSC::decode(OSC::helper('core/setting')->get($params['key'])) ?? [];

    $product_info = [];

    foreach ($bypass_min_sold_products as $product) {
        $product_info[$product['product_id']] = $product;
    }

    $product_ids = array_column($bypass_min_sold_products, 'product_id');

    if (count($product_ids) > 0) {
        $product_data = OSC::helper('catalog/product')->getListProductInfo($product_ids, $product_info)['products'];
    }
?>
<div id="container_table_by_pass_products" style="display: none; margin-top: 20px">
    <input 
        type="hidden"
        name="config[<?= $params['key'] ?>]"
        value="<?= $this->safeString(OSC::helper('core/setting')->get($params['key'])); ?>"
    />
    <div class="title" style="border-bottom: 1px solid #e6e6e6; font-weight: bold">Page listing bypass minsold product</div>
    <div class="mt20 mb20" style="display: flex; justify-content: space-between;">
        <div 
            type="button" 
            class="btn btn-primary mb5" 
            data-insert-cb="initMinSoldProduct"
            data-create-minsold-url="<?= $this->getUrl('*/*/post') ?>"
        >
            <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add product
        </div>
        <div>
            <lable>Search:</label>
            <input type="text" data-insert-cb="initSearchTable" style="padding: 4px"/>
        </div>

        <label style="margin: auto 20px;" id="table_info">Showing 1 to <?= min($page_size, count($product_data)) ?> of <?= count($product_data); ?> entries</label>
    </div>
    <table class="grid" style="text-align:left" id="list_minsold_product_table">    
        <?= $this->getJSONTag([
            'product_data' => $product_data,
            'page_size' => $page_size,
            'additional_key_config' => $params['key']
        ], 'meta-data') ?>
        <thead>
            <tr>
                <th>Thumbnail</th>
                <th>ID</th>
                <th>SKU</th>
                <th>Product Title</th>
                <th>Sold</th>
                <th>Added By</th>
                <th>Date added</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(array_slice($product_data, 0, $page_size) as $product) : ?>
                <tr>
                    <td style="text-align: center">
                        <div data-insert-cb="initQuickLook" data-image="<?= $product['image'] ?>" class="thumbnail-preview" style="background-image: url(<?= $product['image'] ?>)"></div>
                    </td>
                    <td><?= $product['product_id']; ?></td>
                    <td><?= $product['sku']; ?></td>
                    <td style="word-break: break-word"><?= $product['product_title']; ?></td>
                    <td><?= number_format($product['solds']); ?></td>
                    <td><?= $product['added_by']; ?></td>
                    <td><?= $product['added_date_format']; ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?= $product['product_url'] ?>" target="_blank"><?= $this->getIcon('eye-regular') ?></a>
                        <a class="btn btn-small btn-icon" href="<?= $product['analytic_url'] ?>" target="_blank"><?= $this->getIcon('analytics') ?></a>
                        <div 
                            class="btn btn-small btn-icon" 
                            data-insert-cb="initRemoveMinsoldProduct"
                            data-product-id="<?=$product['product_id']?>"
                        >
                            <?= $this->getIcon('trash-alt-regular') ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination-bar p20">
        <ul class="pagination" id="pagination">
            <?php for( $i = 1; $i <= ceil(count($product_ids) / $page_size); $i++) : ?>
                <li 
                    class="<?= $i == 1 ? 'current' : '' ?>"
                    data-insert-cb="initChangePageMinSoldProduct"
                    data-page="<?= $i ?>"
                >
                    <div><?= $i ?></div>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
</div>