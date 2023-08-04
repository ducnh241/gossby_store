<?php
/* @var $this Helper_Backend_Template */

$this->addComponent('itemSelector')
    ->push(<<<EOF
let PRODUCT_SELECTOR_BROWSE_URL = '{$this->getUrl('catalog/backend_product/browseDB')}';
EOF
        , 'js_code');

$this->push([
    'common/select2.min.js',
    'marketing/setting_type/add_point.js'
], 'js');
$this->push([
    'common/select2.min.css',
    'marketing/setting_type/add_point.scss',
], 'css');

$product_types = OSC::helper('catalog/common')->fetchProductTypes();

asort($product_types, SORT_NATURAL | SORT_FLAG_CASE);

$_product_types = [
    'ALL_BETA_PRODUCTS' => 'ALL_BETA_PRODUCTS',
    'ALL_REAL_PRODUCTS' => 'ALL_REAL_PRODUCTS'
];

foreach ($product_types as $product_type) {
    $_product_types[$product_type] = $product_type;
}

$collections = OSC::model('catalog/collection')->getCollection()->addField('collection_id', 'title')->load();

foreach ($collections as $collection) {
    $catalog_collections[$collection->data['collection_id']] = $collection->data['title'];
}
?>

<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" data-insert-cb="marketingAddPoint">
    <?php
    $data = [];
    if (is_array($params['value'])):
        foreach ($params['value'] as $key => $val):
            $data[$key]['name'] = $val['name'];
            $data[$key]['product_type'] = $val['product_type'];
            $data[$key]['collection'] = $val['collection'];
            $data[$key]['product_ids'] = $val['product_ids'];
            $data[$key]['products'] = [];

            if (count($val['product_ids'])) {
                $products_selected_collection = OSC::model('catalog/product')->getCollection()->load($val['product_ids']);
                foreach ($products_selected_collection as $product) {
                    $data[$key]['products'][] = [
                        'id' => intval($product->getId()),
                        'title' => $product->getProductTitle(),
                        'url' => $product->getDetailUrl(),
                        'image' => $product->getFeaturedImageUrl()
                    ];
                }
            }

            if (count($val['value']) > 0):
                foreach ($val['value'] as $date_key => $date_value):
                    $data[$key]['value'][$date_key] = $date_value;
                    $data[$key]['value'][$date_key]['point'] = OSC::helper('catalog/common')->integerToFloat($date_value['point']);
                endforeach;
            endif;
        endforeach;
        ?>
    <?php endif; ?>
    <?= $this->getJSONTag([
        'data' => $data,
        'product_types' => $_product_types,
        'collections' => is_array($catalog_collections) ? $catalog_collections : []],
        'add_marketing_point') ?>
</div>

