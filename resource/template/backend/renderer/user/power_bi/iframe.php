<?php
/* @var $this Helper_Backend_Template */
$this->push('report/power_bi_iframe.scss', 'css');

$name = $params['name'];
$token = base64_encode(time() . '_' . OSC_SITE_KEY . '_' . $name);
?>

<div class="iframe-wrapper">
    <iframe
            id="myFrame"
            loading="lazy"
            referrerpolicy="no-referrer"
            src="<?= OSC::getUrl('srefReport/backend/getPowerBiUrl', ['t' => $token, 'name' => $name], true) ?>"
    ></iframe>
</div>

