<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('select2');
$product_attribute_options = $params['product_attribute_options'] ?? [];
$variants = $params['variants'] ?? [];
?>
<div class="m25">
    <div class="filter-options-title">Filter options:</div>
    <select id="filter-options-selector"
            name="filter_options[]"
            multiple="multiple"
            data-insert-cb="handleProductAttributeSelector"
    ></select>
</div>
<div class="block m25">
    <table id="product-report-table"
           class="grid grid-borderless"
           data-table-data="<?= htmlspecialchars(OSC::encode($variants)); ?>"
    ></table>
</div>

<script>
    $(document).ready(function () {
        const filterOptionsSelector = $('#filter-options-selector');

        // handle default table config
        const reportTableConfig = localStorage.getItem('report_detail_product_table_config');
        if (!reportTableConfig) {
            localStorage.setItem(
                'report_detail_product_table_config',
                JSON.stringify({
                    'sort': {
                        'sales': 'DESC'
                    }
                })
            );
        }

        filterOptionsSelector.select2({
            width: '412px',
            data: [
                {
                    id: 'variant',
                    text: 'Product Variant'
                },
                {
                    id: 'billing_country',
                    text: 'Billing Country'
                },
                {
                    id: 'sref',
                    text: 'Sref'
                }
            ]
        });
    });
</script>
