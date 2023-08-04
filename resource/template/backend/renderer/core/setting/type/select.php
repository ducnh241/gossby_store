<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
<div>
    <div class="styled-select">
        <select name="config[<?= $params['key'] ?>]">
            <option>Please select an option</option>
            <?php if (isset($params['options']) && is_array($params['options'])) : ?>
                <?php foreach ($params['options'] as $k => $v) : ?>
                    <option value="<?= $this->safeString($k) ?>"<?php if ($params['value'] == $k) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($v) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <ins></ins>
    </div>
</div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>