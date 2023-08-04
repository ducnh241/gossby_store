<?php
$pack = $params['pack'];
?>
<tr id="pack-<?= $pack->getId() ?>">
    <td align="left"><?= $pack->data['title'] ?></td>
    <td align="left"><?= $pack->data['quantity'] ?></td>
    <td align="left">
        <?= $pack->data['discount_type'] === Model_Catalog_Product_Pack::PERCENTAGE ? 'Percentage' : 'Fixed Amount' ?>
    </td>
    <td align="left">
        <?= $pack->data['discount_value'] ?>
        <?= $pack->data['discount_type'] === Model_Catalog_Product_Pack::PERCENTAGE ? ' %' : ' $' ?>
    </td>
    <td align="left">
        <?= $pack->data['marketing_point_rate'] ?>
    </td>
    <td align="left">
        <?= $pack->data['note'] ?>
    </td>
    <td align="right">
        <a class="btn btn-small btn-icon" href="javascript:void(0)"
            data-insert-cb="initSettingPack"
            data-product-pack="<?= $pack->getId() ?>"
            data-product-type="<?= $pack->data['product_type_id'] ?>"
        >
            <?= $this->getJSONTag([
                'id' => $pack->data['id'],
                'title' => $pack->data['title'],
                'quantity' => $pack->data['quantity'],
                'discount_type' => $pack->data['discount_type'],
                'discount_value' => $pack->data['discount_value'],
                'marketing_point_rate' => $pack->data['marketing_point_rate'],
                'note' => $pack->data['note']
            ], 'pack-data') ?>
            <?=$this->getIcon('pencil')?>
        </a>
    </td>
</tr>
