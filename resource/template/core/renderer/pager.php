<ul class="pagination">
    <?php if (isset($params['first'])) : ?><li><a href="<?php echo $params['first']['url']; ?>"<?php if (isset($params['first']['attributes'])) : ?><?= $params['first']['attributes'] ?><?php endif; ?>><?= $this->getIcon('arrow-to-left-light') ?></a></li><?php endif; ?>
    <?php foreach ($params['pages'] as $page) : ?>
        <li<?php if ($params['cur_page'] == $page['page']) : ?> class="current"<?php endif; ?>>
            <?php if ($params['cur_page'] == $page['page']) : ?>
                <div><?php echo $page['page']; ?></div>
            <?php else : ?>
                <a href="<?php echo $page['url']; ?>"<?php if (isset($page['attributes'])) : ?><?= $page['attributes'] ?><?php endif; ?>><?php echo $page['page']; ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    <?php if (isset($params['last'])) : ?><li><a href="<?php echo $params['last']['url']; ?>"<?php if (isset($params['last']['attributes'])) : ?><?= $params['last']['attributes'] ?><?php endif; ?>><?= $this->getIcon('arrow-to-right-light') ?></a></li><?php endif; ?>
</ul>