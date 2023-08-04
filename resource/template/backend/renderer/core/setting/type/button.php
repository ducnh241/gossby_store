
<div class="d-flex align--item--center">
    <button type="button" class="btn btn-primary"
        <?php if(isset($params['insert_cb'])): ?>
            data-insert-cb="<?= $params['insert_cb'] ?>"
        <?php endif; ?>
        <?php if(isset($params['data_url'])): ?>
            data-url="<?= $params['data_url'] ?>"
        <?php endif; ?>
    >
        <?=$params['title']?>
    </button>
    <div class="description ml10"><?= $params['description'] ?? '' ?></div>
</div>
