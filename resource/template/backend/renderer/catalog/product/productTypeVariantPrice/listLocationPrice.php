<?php
?>

<div class="block m25" id="stock-container">

    <div class="header-grid">
        <div class="flex--grow"></div>
        <div>
            <?php if ($this->checkPermission()) : ?>
                <a href="<?= $this->getUrl('*/*/index'); ?>" class="btn btn-outline btn-small">Go Back</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if (count($params['price_variant_group_by_location'])) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left; width: 200px">Location</th>
                <th style="text-align: left; width: 300px">Product Type Variant</th>
                <th style="text-align: left; width: 150px">Price</th>
                <th style="text-align: left; width: 150px">Compare At Price</th>
            </tr>
            <?php foreach ($params['price_variant_group_by_location'] as $location_data => $group_prices) : ?>
                <?php foreach ($group_prices as $key => $group_price) : ?>
                    <tr>
                        <?php if ($key === 0) : ?>
                        <td rowspan="<?= count($group_prices) ?>">
                            <?= $location_data === '*' ? 'All Location' : OSC::helper('core/country')->getNameByLocation($location_data) ?>
                        </td>
                        <?php endif; ?>
                        <td>
                            <?= $params['product_type_variant_titles'][$group_price['product_type_variant_id']] ?>
                        </td>
                        <td>
                            <?= OSC::helper('catalog/common')->formatPriceByInteger($group_price['price']) ?>
                        </td>
                        <td>
                            <?= OSC::helper('catalog/common')->formatPriceByInteger($group_price['compare_at_price']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="no-result">No data to display.</div>
    <?php endif; ?>
</div>
