<?php
$design = $params['design'];
?>

<div class="clearfix backend-search-layout">
    <div class="img-col">
        <img src="/resource/template/backend/image/backend/search_design.png" alt="">
    </div>
    <div class="content-col">
        <div class="title">
            <h4>
                <?php if ($this->checkPermission('personalized_design/edit' . ($design->data['locked_flag'] == 1 ? '/locked' : ''))) : ?>
                    <a href="<?= $this->getUrl('personalizedDesign/backend/post', ['id' => $design->getId()]) ?>"><?= $design->data['title'] ?></a>
                <?php else : ?>
                    <a href="javascript:void(0)"><?= $design->data['title'] ?></a>
                <?php endif; ?>
            </h4>
        </div>
        <div class="action-bar clearfix">
            <?php if ($this->checkPermission('personalized_design/view_report')) : ?>
                <a class="btn btn-small btn-icon" href="<?= $this->getUrl('personalizedDesign/backend/report', ['id' => $design->getId()]) ?>">
                    <?= $this->getIcon('analytics') ?>
                </a>
            <?php else : ?>
                <a class="btn btn-small btn-icon" href="javascript:void(0)">
                    <?= $this->getIcon('analytics') ?>
                </a>
            <?php endif; ?>

            <?php if ($this->checkPermission('personalized_design/edit' . ($design->data['locked_flag'] == 1 ? '/locked' : ''))) : ?>
                <a class="btn btn-small btn-icon" href="<?= $this->getUrl('personalizedDesign/backend/post', ['id' => $design->getId()]) ?>">
                    <?= $this->getIcon('pencil') ?>
                </a>
            <?php else : ?>
                <a class="btn btn-small btn-icon" href="javascript:void(0)">
                    <?= $this->getIcon('pencil') ?>
                </a>
            <?php endif; ?>
            </a>
        </div>
    </div>
</div>
