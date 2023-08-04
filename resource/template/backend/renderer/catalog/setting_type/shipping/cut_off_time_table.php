<?php
/* @var $this Helper_Backend_Template */

$this->addComponent('daterangepicker');
$this->addComponent('select2');
$this->addComponent('location_group');

$this->push('catalog/setting_type/shipping/cut_off_time.js', 'js');
$this->push('vendor/bootstrap/bootstrap-grid.min.css', 'css');

$product_types = OSC::model('catalog/productType')
    ->getCollection()
    ->addField('ukey')
    ->addField('title')
    ->load()
    ->toArray();

array_unshift($product_types, 
    [
        'ukey' => '*',
        'title' => 'All Product Types',
    ],
    [
        'ukey' => 'manual',
        'title' => 'Manual Product',
    ]
);

?>
<div class="setting-table" data-name="config[<?= $params['key'] ?>]" id="cut-off-time-location">
    <input type="hidden" class="cut_off_time_key" value="<?= $params['key'] ?>">
    <?= $this->getJSONTag($product_types, 'product_types') ?>
    <div class="frm-line e20"></div>
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col" id="cut-off-time-post-frm">
            <?php
                $count = 1;
                foreach ($params['value'] as $locations) : 
                $selected_product_types_id = $locations['product_types'];
                unset($locations['product_types']);
            ?>
                <?=
                    $this->build('catalog/setting_type/shipping/cut_off_time_group', [
                    'selected_product_type_id' => $selected_product_types_id,
                    'locations' => $locations,
                    'product_types' => $product_types,
                    'key' => $params['key'],
                    'uniqid' => OSC::makeUniqid(),
                    'count' => $count
                ]) ?>
            <?php
                ++$count;
                endforeach; 
            ?>
        </div>
    </div>
    <button type="button" class="btn btn-primary" data-insert-cb="addCutOffTimeGroup">
        <?= $this->getIcon('icon-plus', ['class' => 'mr5']) ?>Add New Group
    </button>
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
<script>
    $(document).ready(function () {
        $('.multiple-selection-options').select2({
            theme: 'default select2-container--custom',
            width: '100%'
        });
    });
</script>
