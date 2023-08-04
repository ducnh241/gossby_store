<?php
/* @var $this Helper_Backend_Template */
/* @var $review Model_Catalog_Product_Review */
$review = $params['review'];
$list_image = $review->getListImage();
$list_child_review = $review->getChildReview();
$count = count($list_child_review->toArray());
$content = $review->getFormattedContent();

$black_list_keywords = $params['black_list_keyword'];
if (!empty($black_list_keywords)) {
    foreach ($black_list_keywords as $keyword) {
        $keyword = trim($keyword);
    	$content = str_replace($keyword, "<strong>" . $keyword . "</strong>", $content);
    }
}

?>
<div class="catalog-review-item" data-state="<?= $review->getStateKey() ?>" data-id="<?= $review->getId() ?>">
    <div>
        <div class="info">
            <div class="header">
                <div class="title"><a href="mailto: <?= $this->safeString($review->data['customer_email']) ?>"><?= $this->safeString($review->data['customer_name']) ?></a> about <a href="<?= $this->safeString($review->getProductDetailUrl()) ?>"><?= $this->safeString($review->getProductTitle()) ?></a></div>
                <div>
                    <span class="rating"><?php for ($i = 1; $i <= 5; $i ++) : ?><?= $this->getIcon('star' . ($i <= $review->data['vote_value'] ? '' : '-regular')) ?><?php endfor; ?></span>
                    <?= $this->getIcon('clock') ?>
                    <span class="date"><?= date('d/m/Y', $review->data['added_timestamp']) ?></span>
                </div>
            </div>
            <div class="body"><?= $content ?></div>
            <div class="action">
                <?php if ($this->checkPermission('catalog/super|catalog/review/approve')) : ?>
                    <?php if ($review->isPending()) : ?>
                        <a href="<?= $this->getUrl('*/*/switchState', ['id' => $review->getId(), 'state' => 2]) ?>" class="btn btn-small btn-secondary" data-insert-cb="catalogInitReviewStateSwitcher">Approve</a>
                    <?php elseif ($review->isHidden()) : ?>
                        <a href="<?= $this->getUrl('*/*/switchState', ['id' => $review->getId(), 'state' => 2]) ?>" class="btn btn-small btn-secondary" data-insert-cb="catalogInitReviewStateSwitcher">Show</a>
                    <?php else : ?>
                        <a href="<?= $this->getUrl('*/*/switchState', ['id' => $review->getId(), 'state' => 0]) ?>" class="btn btn-small btn-outline" data-insert-cb="catalogInitReviewStateSwitcher">Hide</a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($this->checkPermission('catalog/super|catalog/review/edit')) : ?>
                    <a class="ml5 btn btn-small btn-primary" href="<?= $this->getUrl('*/*/post', ['id' => $review->getId()]) ?>">Edit</a>
                <?php endif; ?>
                <?php if ($this->checkPermission('catalog/super|catalog/review/delete')) : ?>
                    <a class="ml5 btn btn-small btn-danger" href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the review of {$review->data['customer_name']}?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $review->getId())) ?>')">Delete</a>
                <?php endif; ?>
				<?php if ($this->getAccount()->isAdmin() && $review->data['parent_id'] == 0): ?>
					<a class="ml5 btn btn-small btn-primary" href="<?= $this->getUrl('*/*/reply', ['id' => $review->getId()]) ?>">Reply</a>
				<?php endif; ?>
            </div>
        </div>
        <?php if (!empty($list_image)): ?>
			<div class="list_photo">
			<?php foreach ($list_image as $item): ?>
				<div class="photo" style="background-image: url(<?= $this->safeString($item['url']) ?>)"></div>
			<?php endforeach; ?>
			</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($count > 0): ?>
	<div class="catalog-review-list">
        <?php foreach ($list_child_review as $child_review) : ?>
            <?= $this->build('catalog/review/item', ['review' => $child_review, 'black_list_keyword' => $black_list_keywords]) ?>
        <?php endforeach; ?>
	</div>
<?php endif; ?>