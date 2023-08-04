<?php
$min = isset($params['min']) ? "min=\"$params[min]\"" : '';
$max = isset($params['max']) ? "max=\"$params[max]\"" : '';
$required = isset($params['required']) ? "required" : '';
?>
<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div>
<?php endif; ?>
    <div>
        <input type="number" name="config[<?= $params['key'] ?>]" class="styled-input"
               value="<?= $this->safeString($params['value']) ?>" <?= $required ?> <?= $min ?> />
    </div>
<?php if ($params['desc']): ?>
    <div class="input-desc"><?= $params['desc'] ?></div>
<?php endif; ?>