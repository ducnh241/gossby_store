<?php
/* @var $this Helper_Backend_Template */

$product_types = $params['product_types'];
$facebook_pixels = $params['facebook_pixels'];
$facebook_pixel_group_by_product_type = $params['facebook_pixel_group_by_product_type'];
$this->addComponent('select2');
$this->push('facebook/common.scss', 'css');
?>
<form action="<?php echo $this->getUrl('*/*/*') ?>" method="post"
      class="post-frm p25" style="width: 1000px">
    <div class="block">
        <div class="header">
            <div class="header__main-group">
                <div class="header__heading">Select the pixel corresponding to the product type</div>
            </div>
        </div>
        <div class="p20">
            <div class="frm-grid">
                <div>
                    <table class="table-choose-facebook-pixel">
                        <tr>
                            <th style="text-align: left; width: 40%">Product type</th>
                            <th style="text-align: left">Pixel ID</th>
                        </tr>
                        <?php foreach ($product_types as $product_type): ?>
                            <tr>
                                <td><?= $product_type->data['title'] ?></td>
                                <td>
                                    <div class="styled-select">
                                        <select name="product_type_rel[<?= $product_type->getId() ?>][]"
                                                class="select_pixel_id_for_product_type" multiple style="display: none">
                                            <?php $pixel_ids_selected = isset($facebook_pixel_group_by_product_type[$product_type->getId()]) ? $facebook_pixel_group_by_product_type[$product_type->getId()] : []; ?>
                                            <option value="" disabled="disabled">Please select pixel_id</option>
                                            <?php foreach ($facebook_pixels as $facebook_pixel): ?>
                                                <option value="<?= $facebook_pixel->data['pixel_id'] ?>"
                                                        <?php if (in_array($facebook_pixel->data['pixel_id'], $pixel_ids_selected)): ?>selected<?php endif; ?>><?= $facebook_pixel->data['title'] . " (" . $facebook_pixel->data['pixel_id'] . ")" ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="is_submit" value="1">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>"
           class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit"
                class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $(".select_pixel_id_for_product_type").select2();
    });

</script>
