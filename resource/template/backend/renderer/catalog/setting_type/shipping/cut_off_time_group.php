<?php
$this->push(['supplier/product_type_variant.scss'], 'css');
?>

<div class="block mt10 cut-off-time-group">
    <input type="hidden" class="cut_off_time_uniqid" value="<?= $params['uniqid'] ?>">
    <div class="p10">
        <div class="frm-grid">
            <div>
                <div class="display--inline-block" style="font-weight: 700;">Group #<span class="group-stt"><?= $params['count'] ?></span></div>
                <div class="btn-remove fright display--inline-block" data-insert-cb="removeCutOffTimeGroup">Delete Group #<?= $params['count'] ?></div>
                <div class="title" style="margin-top: 10px;">Product Type<span class="required font-weight-bold"></span></div>
                <select class="multiple-selection-options" name="<?= "config[{$params['key']}][{$params['uniqid']}][product_types][]" ?>" data-insert-cb="changeProductType" multiple>
                    <?php foreach ($params['product_types'] as $product_type) : ?>
                        <option value="<?= $product_type['ukey'] ?>"
                            <?= in_array($product_type['ukey'], $params['selected_product_type_id']) ? 'selected' : '' ?>
                        >
                            <?= $product_type['title'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>
        </div>
        <div class="cut-off-time-items mt10">
            <?php if (in_array('manual', $params['selected_product_type_id'])) :?>
                <?php foreach ($params['locations']['meta_data'] as $item) :?>
                    <?= $this->build('catalog/setting_type/shipping/cut_off_time_item', [
                        'product_ids' => $item['product_ids'],
                        'selected_location' => $item['location'],
                        'selected_datetime' => $item['time'],
                        'key' => $params['key'],
                        'uniqid' => $params['uniqid'],
                        'selected_product_type_id' => $params['selected_product_type_id']
                    ]); ?>
                <?php endforeach; ?>
            <?php else :?>
                <?php foreach ($params['locations'] as $location => $datetime) : ?>
                    <?= $this->build('catalog/setting_type/shipping/cut_off_time_item', [
                        'selected_location' => $location,
                        'selected_datetime' => $datetime,
                        'key' => $params['key'],
                        'uniqid' => $params['uniqid'],
                        'selected_product_type_id' => $params['selected_product_type_id']
                    ]); ?>
                <?php endforeach; ?>
            <?php endif;?>
        </div>
        <div class="btn btn-small btn-secondary-add mt15 btn-cut-off-time box-sizing-content"
         data-insert-cb="addCutOffTime"><?= $this->getIcon('plus', ['class' => 'mr5']) ?> Add new cut off time</div>
    </div>

    <script>
        $(document).ready(function () {
            $('.multiple-selection-options').select2({
                width: '100%',
                theme: 'default select2-container--custom',
                multiple: 'multiple'
            });
        });
    </script>

</div>
<div class="frm-line e20"></div>
