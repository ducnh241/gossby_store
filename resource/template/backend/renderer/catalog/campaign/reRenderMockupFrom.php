<?php
/* @var $this Helper_Backend_Template */
?>

<form action="<?php echo $this->getUrl('*/*/*'); ?>" method="post" class="post-frm product-post-frm p25" style="width: 1400px">
    <div class="post-frm-grid">
        <div class="post-frm-grid__main-col">
            <div class="block">
                <div class="p20">
                    <div class="frm-grid">
                        <div data-frm="email">
                            <label class="">Product IDs</label>
                            <textarea placeholder="Please enter the product id comma separated (ex: product id: 19123, 19143)" class="styled-textarea" name="id"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>