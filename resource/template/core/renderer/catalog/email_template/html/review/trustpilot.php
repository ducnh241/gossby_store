<!DOCTYPE html>
<html>

<head>
    <title><?= $params['title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <style>
        body div {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bodybg" style="background:#fff">
    <p>
        <strong>Dear <?= $params['customer_first_name']; ?></strong>
    </p>
    <p>
        Thank you for choosing <?= $params['site_name']; ?>.
    </p>
    <p>
        To improve the satisfaction of our customers, we have partnered with the online review community, Trustpilot, to collect reviews.
    </p>
    <p>
        <h3>
            How did we do?
        </h3>
    </p>
    <div>
        <a href="<?= $params['review_url']; ?>" target="_blank">
            <img src="<?= $params['base_url']; ?>/resource/template/backend/image/backend/trustpilot.png" width="250" height="250"/>
        </a>
    </div>
    <p>
        All reviews, good, bad or otherwise will be visible immediately.
    </p>
    <p>
        <strong>Thanks for your time,</strong>
        <br/>
        <strong><?= $params['site_name']; ?></strong>
    </p>
    <p>
        <strong>Please note:</strong> This email is sent automatically, so you may have received this review invitation before the arrival of your package or service. In this case, you are welcome to wait with writing your review until your package or service arrives.
    </p>
    <p style="color: #999; font-size: 12px">
        If you want to opt out of receiving review invitation emails from Trustpilot, please click <a href="<?= OSC::helper('postOffice/email')->getUnsubsribingUrl() ?>" target="_blank" style="color: #999">unsubscribe</a>.
    </p>
	<?php if (intval(OSC::helper('core/setting')->get('catalog/product/enable_trust_pilot_signature')) == 1):
		$rating_value = doubleval(OSC::helper('core/setting')->get('catalog/product/trust_pilot_rating_value'));
		?>
		<h3 style="border-top: 1px solid #ddd;padding-top: 15px;">
            See our reviews on
		</h3>
		<div>
			<a href="<?= $params['signature_url']; ?>" target="_blank">
				<img src="<?= $params['base_url']; ?>/resource/template/backend/image/backend/trustpilot/trust_pilot_<?= $rating_value; ?>.png" height="80" />
			</a>
		</div>
	<?php endif; ?>
</body>