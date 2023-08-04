<?php
    $this->push(['[core]community/moment.min.js', 'catalog/setting_type/bypass_minsold_product.js'], 'js');

    $products = OSC::decode(OSC::helper('core/setting')->get($params['additional_key']));
?>

<style>
    .container {
        width: 49%; 
        display: flex;
    }
    .sub-text {
        background-color: #188DFF;
        width: 50%;
        color: #fff;
        padding: 7px;
        text-align: center;
    }
    .sub-input {
        width: 40%;
        border-right: none;
    }
</style>

<?php
$min = isset($params['min']) ? "min=\"$params[min]\"" : '';
$max = isset($params['max']) ? "max=\"$params[max]\"" : '';
$required = isset($params['required']) ? "required" : '';
?>
<?php if ($params['title']): ?>
    <div class="title"><?= $params['title'] ?></div>
<?php endif; ?>
    <div class="container">
        <input type="number" name="config[<?= $params['key'] ?>]" class="styled-input sub-input"
               value="<?= $this->safeString($params['value']) ?>" <?= $required ?> <?= $min ?> 
        />
        <div class="sub-text" id="excludeByPassQuantity">
            <span>Exclude <?= count($products); ?> product(s)</span>
        </div>
        <div class="btn btn-small btn-icon" data-insert-cb="initEditTableByPassProducts">
            <?= $this->getIcon('pencil') ?>
        </div>
    </div>
<?php if ($params['desc']): ?>
    <div class="input-desc"><?= $params['desc'] ?></div>
<?php endif; ?>