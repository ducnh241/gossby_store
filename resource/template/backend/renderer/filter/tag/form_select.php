<?php
/* @var $this Helper_Backend_Template */
$this->push(['filter/tag.scss'], 'css');
$list_product_tags = $params['list_product_tags'] ?? [];
$product_tag_selected = $params['product_tag_selected'] ?? [];
?>

<div class="block mt15">
    <div class="fm-product-tag">
        <div class="frm-heading" style="margin: 15px 0">
            <div class="frm-heading__title mr15" style="font-size: 15px">Product Tags</div>
        </div>
        <?php if (count($list_product_tags) > 0) : ?>
            <div class="frm-tag">
                <?php foreach ($list_product_tags as $product_tag) : ?>
                    <?php if (count($product_tag['children']) > 0) : ?>
                        <div class="product-tags">
                            <div class="tag-title"
                                 title="<?= $product_tag['title'] ?>"><?= $product_tag['title'] . ($product_tag['required'] ? ' <span>(*)</span>' : '') ?></div>
                            <div class="tag-children <?= $product_tag['required'] ? 'required' : '' ?>">
                                <?php foreach ($product_tag['children'] as $tag_item) : ?>
                                    <div class="product-tag-item">
                                        <div class="styled-checkbox mr5">
                                            <input type="checkbox" value="<?= $tag_item['id'] ?>"
                                                   name="product_tags[]"
                                                   id="tag_<?= $tag_item['id'] ?>"
                                                <?php if (isset($product_tag_selected[$tag_item['id']])) : ?> checked="checked"<?php endif; ?> />
                                            <ins><?= $this->getIcon('check-solid') ?></ins>
                                        </div>
                                        <label class="label-inline label-tag" for="tag_<?= $tag_item['id'] ?>"
                                               title="<?= $tag_item['title'] ?>"><?= $tag_item['title'] ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>