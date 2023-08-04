<?php
/* @var $this Helper_Backend_Template */
/* @var $collection Model_Catalog_Product_Review_Collection */
/* @var $review Model_Catalog_Product_Review */
$collection = $params['collection'];

$this->push('catalog/review.js', 'js')
        ->push('catalog/review.scss', 'css');
$black_list_keywords = OSC::helper('core/setting')->get('catalog/product_review/review_black_list');
$black_list_keywords = !empty($black_list_keywords) ? explode(',', $black_list_keywords) : [];
?>
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow">
			<?php if ($this->getAccount()->isRoot()) : ?>
				<a class="btn btn-primary btn-small ml5" href="<?= $this->getUrl('*/*/resyncReview') ?>">Resync Review</a>
			<?php endif; ?>
		</div>
        <div>
            <?php if ($this->checkPermission('catalog/super|catalog/review/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Add New Review</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-grid"><?= $this->build('backend/UI/search_form', ['process_url' => $this->getUrl('*/*/search'), 'search_keywords' => $params['search_keywords'], 'filter_config' => $params['filter_config']]) ?></div>
    <?php if ($collection->length() > 0) : ?>
        <div class="catalog-review-list">
            <?php foreach ($collection as $review) : ?>
                <?= $this->build('catalog/review/item', ['review' => $review, 'black_list_keyword' => $black_list_keywords]) ?>
            <?php endforeach; ?>
        </div>
        <?php $pager = $this->buildPager($collection->getCurrentPage(), $collection->collectionLength(), $collection->getPageSize(), 'page'); ?>
        <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
    <?php else : ?>
        <div class="no-result">
            <?php if (OSC::core('request')->get('search') == 1): ?>
                Sorry, we couldn't find any results for "<?= $params['search_keywords']; ?>"
            <?php else: ?>
                There aren’t any reviews yet.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>