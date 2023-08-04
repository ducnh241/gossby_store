<?php
/* @var $this Helper_Backend_Template */
$trackings = $params['trackings'];

?>
<table class="grid grid-borderless">
    <tr>
        <th class="text_left" style="width: 70%">Referer Url</th>
        <th class="text_left" style="width: 30%">Value</th>
    </tr>
    <?php if ($trackings->length() > 0) : ?>
        <?php foreach ($trackings as $k => $tracking) {  ?>
            <tr>
                <td class="text_left" style="width: 70%"><?= $tracking->data['referer'] ?></td>
                <td class="text_left" style="width: 30%"><?= $tracking->data['report_value'] ?></td>
            </tr>
        <?php } ?>
    <?php else : ?>
        <tr>
            <td> Not have data</td>
            <td></td>
        </tr>
    <?php endif; ?>
</table>