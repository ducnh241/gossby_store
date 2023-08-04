<?php
$params['css_attributes'] = [
    'table' => 'border-spacing: 0 !important; border-collapse: collapse !important; width: 100% !important;',
];
$logo = OSC::helper('frontend/template')->getLogo(true);
$params['email_cs'] = OSC::helper('core/setting')->get('theme/contact/customer_service_email');
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

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-base {
            font-size: 16px;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-normal {
            font-weight: 400;
        }

        .text-2xl {
            font-size: 24px;
        }

        .pl-4 {
            padding-left: 16px;
        }

        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .my-2 {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .my-4 {
            margin-top: 16px;
            margin-bottom: 16px;
        }

        .my-5 {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .my-6 {
            margin-top: 24px;
            margin-bottom: 24px;
        }

        .ml-4 {
            margin-left: 14px;
        }

        .mt-8 {
            margin-top: 32px;
        }

        .mt-6 {
            margin-top: 24px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .text-3A {
            color: #3A3A3A !important;
        }

        .button {
            background-color: #70BE54;
            border-radius: 8px;
            padding: 12px 32px;
            display: inline-block;
            font-size: 18px;
            color: #fff !important;
            font-weight: 700;
            width: auto;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .text-base {
                font-size: 14px;
            }
        }
    </style>
</head>

<body style="margin: 0;">
    <table class="body" style="<?= $params['css_attributes']['table'] ?>height: 100% !important;">
        <tr>
            <td style="font-family: arial, -apple-system !important; font-size: 0.875rem !important; background: #e0e0e0 !important;" align="center" valign="center">
                <?= OSC::helper('postOffice/email')->getTrackingContent() ?>
                <table class="body" style="width: 100% !important; max-width: 600px !important; border-spacing: 0; border-collapse: collapse; margin-bottom: 16px;">
                    <?php if ($params['big_logo']) : ?>
                        <tr>
                            <td style="text-align: center;width: 600px;padding: 60px 0 20px;margin: 0 auto;background: white; border-radius: 8px 8px 0 0;" align="center" valign="center">
                                <img src="<?= $logo->url ?>" title="<?= $logo->alt ?>" alt="<?= $logo->alt ?>" style="width: 210px;" data-index="0" />
                            </td>
                        </tr>
                    <?php else : ?>
                        <tr>
                            <td style="background: #f7f8fc !important; height: 65px !important; border-radius: 8px 8px 0 0;" align="center" valign="center">
                                <img src="<?= $logo->url ?>" title="<?= $logo->alt ?>" alt="<?= $logo->alt ?>" style="display:block;border:0;outline:none;height:auto;max-height:25px" data-index="0" />
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="background: #fff !important; border-radius: 0 0 8px 8px;" align="center" valign="center">
                            <div style="padding: 30px !important;">
                                <?= $this->build($params['template'], $params) ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>