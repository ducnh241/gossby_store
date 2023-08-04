<?php
?>
<?php if ($params['title']): ?><div class="title"><?= $params['title'] ?></div><?php endif; ?>
    <div>
        <div class="styled-select">
            <select name="config[<?= $params['key'] ?>]">
                <option value="disabled">Don't send review request email</option>
                <option value="purchase"<?php if ($params['value'] == 'purchase') : ?> selected="selected"<?php endif; ?>>After order is created</option>
				<option value="fulfillment"<?php if ($params['value'] == 'fulfillment') : ?> selected="selected"<?php endif; ?>>After order is fulfilled</option>
				<option value="delivered"<?php if ($params['value'] == 'delivered') : ?> selected="selected"<?php endif; ?>>After order is delivered</option>
            </select>
            <ins></ins>
        </div>
    </div>
<?php if ($params['desc']): ?><div class="input-desc"><?= $params['desc'] ?></div><?php endif; ?>
