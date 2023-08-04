<?php
$params['css_attributes'] = [
    'font' => 'font-family: arial, -apple-system !important; font-size: 0.875rem !important; line-height: 1rem !important;',
    'table' => 'border-spacing: 0 !important; border-collapse: collapse !important; width: 100% !important;',
    'h1' => 'font-family: arial, -apple-system !important; font-size: 1.565rem !important; color: #282364 !important; line-height: 1 !important; margin: 0 !important; padding: 0 !important; text-align: center !important;',
    'message' => 'margin-top: 30px !important; line-height: 1rem !important; color: #282364 !important;',
    'message p' => 'margin: 0 !important; margin-bottom: 10px !important;',
    'section-title' => 'color: #282364 !important; font-size: 1rem !important; margin-bottom: 10px !important;',
    'section-desc' => 'color: #72849C !important; margin-bottom: 10px !important;',
    'section-desc div' => 'margin-bottom: 5px !important;',
    'order-line-item' => 'border: 1px solid #F2F2F2 !important; margin-bottom: 30px !important;',
    'order-line-item title' => 'margin-bottom: 10px !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; line-height: 1.075rem !important; color: #282364 !important; font-weight: bold !important;',
    'order-line-item sub-info' => 'margin-top: 3px !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; line-height: 1.075rem !important; color: #72849C !important;',
    'customer-info-heading' => 'font-family: arial, -apple-system !important; font-size: 1.25rem !important; padding-bottom: 20px !important; color: #282364 !important;',
    'customer-info-cell-left' => 'width: 50% !important; padding-right: 10px !important; padding-bottom: 20px !important;',
    'customer-info-cell-right' => 'width: 50% !important; padding-left: 10px !important; padding-bottom: 20px !important;',
    'customer-info-title' => 'font-family: arial, -apple-system !important; font-size: 0.875rem !important; line-height: 1.075rem !important; color: #282364 !important;',
    'customer-info-content' => 'color: #72849C !important; font-family: arial, -apple-system !important; font-size: 0.875rem !important; line-height: 1.075rem !important; margin-top: 10px !important;',
    'button-cell' => 'cursor: pointer !important; border-radius: 4px !important; background: #1688FA !important;',
    'button-cell-green' => 'cursor: pointer !important; border-radius: 4px !important; background: #25C38C !important;',
    'button-cell a' => 'cursor: pointer !important; font-size: 0.875rem !important; line-height: 0.875rem !important; display: block !important; color: #fff !important; padding: 12px 30px !important;',
    'button-cell-outline' => 'cursor: pointer !important; border-radius: 4px !important; background: #E0E0E0 !important; color: #1688FA !important;',
    'button-cell-outline a' => 'cursor: pointer !important; border-radius: 4px !important; font-size: 0.875rem !important; line-height: 0.875rem !important; display: block !important; color: #1688FA !important; padding: 12px 30px !important; background: #fff !important;',
];
/* @var $order Model_Catalog_Order */

$order = $params['order'];
$logo = OSC::helper('frontend/template')->getLogo(true);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Thank you for your purchase!</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width">
        <style>
            body {
                margin: 0;
            }

            a, a:hover {
                text-decoration: none;
            }

            @media (max-width: 600px) {
                .container {
                    width: 94% !important;
                }
                .main-action-cell {
                    float: none !important; margin-right: 0 !important;
                }
                .secondary-action-cell {
                    text-align: center; width: 100%;
                }
                .header {
                    margin-top: 20px !important; margin-bottom: 2px !important;
                }
                .shop-name__cell {
                    display: block;
                }
                .order-number__cell {
                    display: block; text-align: left !important; margin-top: 20px;
                }
                .button {
                    width: 100%;
                }
                .or {
                    margin-right: 0 !important;
                }
                .apple-wallet-button {
                    text-align: center;
                }
                .customer-info__item {
                    display: block; width: 100% !important;
                }
                .spacer {
                    display: none;
                }
                .subtotal-spacer {
                    display: none;
                }
            }
        </style>
    </head>
    <body style="margin: 0;">
        <table class="body" style="<?= $params['css_attributes']['table'] ?>height: 100% !important;">
            <tr>
                <td style="font-family: arial, -apple-system !important; font-size: 0.875rem !important; background: #e0e0e0 !important;" align="center" valign="center">
                    <?= OSC::helper('postOffice/email')->getTrackingContent() ?>
                    <table class="body" style="width: 100% !important; max-width: 600px !important; border-spacing: 0; border-collapse: collapse;">
                        <?php if ($params['big_logo']) : ?>
                        <tr>
                            <td style="text-align: center;width: 600px;padding: 60px 0 20px;margin: 0 auto;background: white" align="center" valign="center">
                                <img src="<?= $logo->url ?>" title="<?= $logo->alt ?>" alt="<?= $logo->alt ?>" style="width: 210px;" data-index="0" />
                            </td>
                        </tr>
                        <?php else: ?>
                            <tr>
                                <td style="background: #f7f8fc !important; height: 65px !important" align="center" valign="center">
                                    <img src="<?= $logo->url ?>" title="<?= $logo->alt ?>" alt="<?= $logo->alt ?>" style="display:block;border:0;outline:none;height:auto;max-height:25px" data-index="0" />
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td style="background: #fff !important;" align="center" valign="center">
                                <div style="padding: 30px !important;">
                                    <?= $this->build($params['template'], $params) ?>
                                    <table style="<?= $params['css_attributes']['table'] ?>border-top-width: 1px !important; border-top-color: #E0E0E0 !important; border-top-style: solid !important; margin-top: 30px !important">
                                        <tr>
                                            <td style="padding: 20px 0 !important">
                                                <?php
                                                /* @var $params['order'] Model_Catalog_Order */
                                                $chat_url = OSC::helper('frontend/template')->getContactUrl() . '?open_chat=1';
                                                if ($order) {
                                                    $chat_url = $order->getDetailUrl() . '?open_chat=1';
                                                }
                                                ?>
                                                <div style="text-align: center !important; font-size: 0.75rem !important; line-height: 1.075rem !important; color: #AEB2BF !important">Got questions? Don't hesitate to <a href="<?= $chat_url ?>" style="color: #2f80ed !important; font-family: arial, -apple-system !important; font-size: 0.75rem !important; line-height: 1.075rem !important; text-decoration: underline !important">chat with us.</a></div>
                                                <?php if ($params['is_marketing_email']) : ?>
                                                    <div style="text-align: center !important; font-size: 0.75rem !important; line-height: 1.075rem !important; color: #AEB2BF !important; margin-top: 5px !important;">You are receving this newsletter because you subscribed to our mailing list via: <?= OSC::helper('core/setting')->get('theme/site_name') ?></div>
                                                    <div style="text-align: center !important; font-size: 0.75rem !important; line-height: 1.075rem !important; color: #AEB2BF !important; margin-top: 5px !important;"><a href="<?= OSC::helper('postOffice/email')->getUnsubsribingUrl() ?>" style="text-align: center !important; font-size: 0.75rem !important; line-height: 1.075rem !important; color: #AEB2BF !important">Unsubscribe</a> | <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC_FRONTEND_BASE_URL . '/faqs') ?>" style="text-align: center !important; font-size: 0.75rem !important; line-height: 1.075rem !important; color: #AEB2BF !important">Visit FAQ</a></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
