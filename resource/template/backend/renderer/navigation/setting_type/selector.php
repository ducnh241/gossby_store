<?php
$collection = $this->registry('NAVIGATION_SETTING_COLLECTION');

if (!($collection instanceof Model_Navigation_Navigation_Collection)) {
    $collection = OSC::model('navigation/navigation')->getCollection()->load();

    $this->register('NAVIGATION_SETTING_COLLECTION', $collection);
}
?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div>
    <div class="styled-select">
        <select name="config[<?= $params['key'] ?>]">
            <option>Please select a navigation</option>
            <?php foreach ($collection as $model) : ?>
                <option value="<?= $this->safeString($model->getId()) ?>"<?php if ($params['value'] == $model->getId()) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($model->data['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <ins></ins>
    </div>
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>