<div class="tab_menu ml20 pb20">
    <?php foreach ($params['power_bis'] as $item):?>
        <a href="<?= OSC::getUrl('srefReport/backend/getPowerBi', ['name' => $item['name']]) ?>" class="<?= $item['activated'] == true ? 'active' : '' ?> tab_menu__item"><?= $item['name'] ?></a>
    <?php endforeach; ?>
</div>

<?= $this->build('user/power_bi/iframe', ['name' => $params['name']]) ?>
