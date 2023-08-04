<div class="block m25">
    <div class="header">
        <div class="header__main-group">
            <div class="header__heading">&nbsp;</div>
        </div>
        <div class="header__action-group">
            <?php if ($this->checkPermission('post/author/add')) : ?>
                <a href="<?php echo $this->getUrl('*/*/post'); ?>"
                   class="btn btn-primary btn-small">
                    <?= $this->getIcon('plus', ['class' => 'mr5']) ?>Add New Author</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($params['collections']->length() > 0) : ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="text-align: left;">No</th>
                <th style="text-align: center;">Avatar</th>
                <th style="text-align: left;">Name</th>
                <th style="text-align: left;">Slug</th>
                <th style="text-align: left;">Content</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php foreach ($params['collections'] as $idx => $author) : ?>
                <tr>
                    <td style="text-align: left"><?= $idx + 1 ?></td>
                    <td style="text-align: center; width: 70px">
                        <div style="width: 70px;height: 70px;box-shadow: inset 0 0 0 1px #0000005c;">
                            <?php if ($author->getAvatarUrl()):?>
                                <img src="<?= $author->getAvatarUrl()?>" alt="Avatar" style="height: 100%;object-fit: unset">
                            <?php endif;?>
                        </div>
                    </td>
                    <td style="text-align: left"><?= $author->data['name'] ?></td>
                    <td style="text-align: left"><?= $author->data['slug'] ?></td>
                    <td style="text-align: left"><?= $author->data['description'] ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?= $author->getDetailUrl()?>"
                           target="_blank"><?= $this->getIcon('eye-regular') ?></a>

                        <?php if ($this->checkPermission('post/author/edit')) : ?>
                            <a class="btn btn-small btn-icon"
                               href="<?php echo $this->getUrl('*/*/post', array('id' => $author->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <?php endif; ?>
                        <?php if ($this->checkPermission('post/author/delete')) : ?>
                            <?php $author_title = addslashes($author->data['name']); ?>
                            <a class="btn btn-small btn-icon"
                               href="javascript:$.confirmAction('<?= $this->safeString("Do you want to delete the author \"{$author_title}\"?") ?>', '<?= $this->getUrl('*/*/delete', array('id' => $author->getId())) ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="no-result">No account was found to display</div>
    <?php endif; ?>
</div>
