<?php
$this->push([
    'common/select2.min.js',
    'catalog/setting_type/place_of_manufacture.js'
], 'js');
$this->push(['common/select2.min.css'], 'css');

$collection = $this->registry('CATALOG_SETTING_COLLECTION');

if (!($collection instanceof Model_Catalog_Collection_Collection)) {
    $collection = OSC::model('catalog/collection')->getCollection()->load();

    $this->register('CATALOG_SETTING_COLLECTION', $collection);
}

$multiple = isset($params['multiple']) && $params['multiple'] ? true : false;

if (!is_array($params['value'])) {
    $params['value'] = [$params['value']];
}

if (!$multiple) {
    $params['value'] = $params['value'][0];
}
?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div>
    <div class="styled-select styled-select--multiple">
        <select  name="config[<?= $params['key'] ?>]<?php if ($multiple) : ?>[]<?php endif; ?>"<?php if ($multiple) : ?> multiple="multiple" size="5"<?php endif; ?> class="js-multi-select-collection">
            <option>Please select a collection</option>
            <?php foreach ($collection as $model) : ?>
                <option value="<?= $this->safeString($model->getId()) ?>"<?php if (in_array($this->safeString($model->getId()), $params['value'], true)) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($model->data['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>

<script>
    $(document).ready(function () {
        $('.js-multi-select-collection').select2({width: '100%'});
    });
</script>
