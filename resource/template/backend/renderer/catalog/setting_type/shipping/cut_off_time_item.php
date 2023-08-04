<?php
$this->push(['supplier/product_type_variant.scss'], 'css');

?>

<div class="cut-off-time-block mt10">

    <div class="cut-off-time-title p10">
        <b>Config</b>
        <div class="close-btn" style="right: 22px; margin-top: 5px;" data-insert-cb="removeCutOffTime"></div>
    </div>
        <div class="cut-off-time-config p10">
        <div 
            class="frm-grid" 
            <?php if (!in_array('manual', $params['selected_product_type_id'])):?> 
                style="display:none;" 
            <?php endif; ?>
        >
            <div>
                <div>Product ID<span class="required font-weight-bold"></div>
                <textarea 
                    type="text" 
                    class="styled-textarea" 
                    rows="2"
                    name="<?= "config[{$params['key']}][{$params['uniqid']}][product_ids][]" ?>" 
                ><?php if (in_array('manual', $params['selected_product_type_id'])):?><?= implode(', ', $params['product_ids']); ?><?php endif; ?></textarea>
            </div>
        </div>
        <div class="frm-grid">
            <div>
                <div>Location<span class="required font-weight-bold"></span></div>
                <div data-insert-cb="initSelectGroupLocation"
                     data-key="<?= "config[{$params['key']}][{$params['uniqid']}][location][]" ?>"
                     data-value="<?= $params['selected_location'] ?>">
                </div>
            </div>
        </div>
        <div class="frm-grid">
            <div>
                <div>Cut off time<span class="required font-weight-bold"></span></div>
                <div class="styled-date-time-input">
                    <div class="date-input" data-insert-cb="initDateTimeFrm">
                        <?= $this->getIcon('calendar-alt') ?>
                        <input type="text" value="<?= $params['selected_datetime'] ?>"
                               name="<?= "config[{$params['key']}][{$params['uniqid']}][date][]" ?>"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>