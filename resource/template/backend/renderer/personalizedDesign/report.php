
<div class="block m25">
    <div class="header-grid">
        <div class="flex--grow"><?= $params['title'] ?></div>
    </div>
    <?php if (count($params['layers']) < 1) : ?>
        <div class="no-result">No order was made with the design</div>       
    <?php else: ?>
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 100px; text-align: left;">Option Image</th>
                <th style="text-align: left">Option title</th>
                <th style="width: 200px; text-align: left;">Option key</th>
                <th style="width: 100px; text-align: right;">Total</th>
            </tr>
            <?php foreach ($params['layers'] as $layer_key => $layer) : ?>
                <tr>
                    <td style="text-align: left; background: #ddd;" colspan="4"><strong>Layer [<?= $layer_key ?>]:</strong> <?= $layer['layer'] ?></td>
                </tr>
                <?php if ($layer['type'] == 'checker') : ?> 
                    <tr>
                        <td style="text-align: left">&nbsp;</td>
                        <td style="text-align: left">Enabled</td>
                        <td style="text-align: left"><?= $layer_key ?></td>
                        <td style="text-align: right"><?= $layer['counter'] ?></td>
                    </tr>
                <?php elseif ($layer['type'] == 'image') : ?>
                    <?php foreach ($layer['images'] as $image_key => $image) : ?>
                        <tr>
                            <td style="text-align: left"><div class="product-image-preview" style="width: 40px; background-image: url(<?= $this->safeString($image['url']) ?>)"></div></td>
                            <td style="text-align: left">&nbsp;</td>
                            <td style="text-align: left"><?= $image_key ?></td>
                            <td style="text-align: right"><?= $image['counter'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif ($layer['type'] == 'switcher') : ?>
                    <?php foreach ($layer['scenes'] as $scene_key => $scene) : ?>
                        <tr>
                            <td style="text-align: left"><?php if ($scene['image']): ?><div class="product-image-preview" style="width: 40px; background-image: url(<?= $this->safeString($scene['image']) ?>)"></div><?php endif; ?></td>
                            <td style="text-align: left"><?= $this->safeString($scene['title']) ?></td>
                            <td style="text-align: left"><?= $scene_key ?></td>
                            <td style="text-align: right"><?= $scene['counter'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </table> 
    <?php endif; ?>
</div>