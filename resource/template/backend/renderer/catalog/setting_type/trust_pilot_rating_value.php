<?php
$list_value = [
    '1' => '1 star',
    '15' => '1.5 star',
    '2' => '2 star',
    '25' => '2.5 star',
    '3' => '3 star',
    '35' => '3.5 star',
    '4' => '4 star',
    '45' => '4.5 star',
    '5' => '5 star',
];
?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
    <div>
        <div class="styled-select">
            <select name="config[<?= $params['key'] ?>]">
                <option>Please select a trust pilot rating value</option>
                <?php foreach ($list_value as $key => $value) : ?>
                    <option value="<?= $this->safeString($key) ?>"<?php if ($params['value'] == $key) : ?> selected="selected"<?php endif; ?>><?= $this->safeString($value) ?></option>
                <?php endforeach; ?>
            </select>
            <ins></ins>
        </div>
    </div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>