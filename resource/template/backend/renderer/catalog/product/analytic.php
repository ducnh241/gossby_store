<?php
/* @var $this Helper_Backend_Template */
/* @var $params['model'] Model_Catalog_Discount_Code */

$this->addComponent('daterangepicker')
    ->push('catalog/analytic.js', 'js')
    ->push('[core]catalog/campaign.scss', 'css');
?>

<div class="post-frm-grid">
    <div class="post-frm-grid__main-col">
        <div class="block mt15">
            <div class="plr20 p20">
                <div class="frm-heading">
                    <form method="post" action="<?= $params['process_url'] ?>" class="flex--grow">
                        <div class="styled-search">
                            <a class="btn btn-primary" href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initAnalyticCustomDate" data-begin="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][0] : '' ?>" data-end="<?= is_array($params['meta_data']['range']) ? $params['meta_data']['range'][1] : '' ?>">
                                Choose time
                            </a>
                            <input type="text" name="product_id" placeholder="Input product id" value="" />
                            <input type="hidden" name="date_range" value="" />
                            <input type="hidden" name="action" value="send" />
                            <button type="submit"><?= $this->getIcon('search') ?>Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>