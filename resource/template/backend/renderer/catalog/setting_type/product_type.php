<?php
$product_types = OSC::helper('catalog/common')->fetchProductTypes();

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
    <div class="styled-select<?php if ($multiple) : ?> styled-select--multiple<?php endif; ?>">
        <select name="config[<?= $params['key'] ?>]<?php if ($multiple) : ?>[]<?php endif; ?>"<?php if ($multiple) : ?> multiple="multiple" size="5"<?php endif; ?>>
            <?php if (!$multiple) : ?><option>Please select a product type</option><?php endif; ?>
            <?php foreach ($product_types as $product_type) : ?>
                <option value="<?= $this->safeString($product_type) ?>"<?php if (in_array($product_type, $params['value'], true)) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($product_type) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!$multiple) : ?><ins></ins><?php endif; ?>
    </div>
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>