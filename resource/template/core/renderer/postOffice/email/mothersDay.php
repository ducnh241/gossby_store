<html>

<head>
    <title>
        Thank you
    </title>
    <!--[if mso]>
    <style>
        body, table, td {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        a {
            text-decoration: none;
        }
    </style>
    <![endif]-->
    <style type="text/css">
        /* Remove space around the email design. */
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            font-size: 14px;
            line-height: 22px;
        }

        body {
            background: #AEB2BF;
            min-width: 100% !important;
            font-family: Arial, Helvetica, sans-serif !important;
            font-style: normal;
            font-weight: normal;
        }

        /* Stop Outlook resizing small text. */
        * {
            -ms-text-size-adjust: 100%;
        }

        /* Stop Outlook from adding extra spacing to tables. */
        table,
        td {
            mso-table-lspace: 0 !important;
            mso-table-rspace: 0 !important;

        }

        /* Use a better rendering method when resizing images in Outlook IE. */
        img {
            -ms-interpolation-mode: bicubic;
        }

        /* Prevent Windows 10 Mail from underlining links. Styles for underlined links should be inline. */
        a {
            text-decoration: none;
        }

        img {
            height: auto;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: white;
        }

        .top {
            background: #F7F8FC;
            text-align: center;
            padding: 15px 0;
        }

        .logo {
            display: inline-block;
        }

        .header {
            padding: 15px 0;
        }

        .email_title {
            text-align: center;
            font-weight: normal;
            font-size: 25px;
            margin: 0 0 20px;
            padding: 22px 0;
        }

        .button a {
            text-decoration: none !important;
            color: white !important;
        }

        .product_list {
            width: 100%;
            padding-bottom: 5px;
            margin-top: 5px;
        }

        .product_list .product_info {}

        .product_list .product_price {
            max-width: 120px;
            text-align: right;
            white-space: nowrap;
        }

        .product_list .product_price_mobile {
            display: none;
        }

        .footer {
            padding: 25px 0;
            text-align: center;
            font-size: 12px;
            color: #AEB2BF;
        }

        .footer a {
            color: #AEB2BF;
        }

        .footer a:hover {
            color: #1688FA;
        }

        .footer_social {
            padding: 10px 0;
        }

        .footer_social a {}

        .footer_social a img {}

        .address_info td {
            padding: 15px;
        }
    </style>
    <!--[if (gte mso 9)|(IE)]>
    </td>
    </tr>
    </table>
    <![endif]-->
    <!--[if (gte mso 9)|(IE)]>
    <table width="600" align="center" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td>
    <![endif]-->
</head>

<body>
<table bgcolor="#E0E0E0" border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
    <tr>
        <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#FFFFFF;" width="600">
                <tr>
                    <td class="top" style="max-width:600px;width:221px;height:21px;padding:15px 0;"> <a class="logo" href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=130');?>" title="">
                            <img alt="" height="25" src="<?= $this->getImage('postOffice/email/mothersDay/2020/logoGossby.png') ?>" width="112" /></a>
                    </td>
                </tr>
                <tr>
                    <td> <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=130');?>" style="text-align:center; display: block;" title="">
                            <img alt="" src="<?= $this->getImage('postOffice/email/mothersDay/2020/Header.jpg') ?>" width="600" /></a>
                    </td>
                </tr>
                <tr>
                    <td class="main">
                        <table align="center" border="0" cellspacing="0" class="container" role="presentation">
                            <tr>
                                <td class="header">
                                    <table align="center" border="0" cellspacing="0" role="presentation" style="padding:30px;" width="100%">
                                        <tr>
                                            <td align="center" style="text-align:center;" valign="middle">
                                                <div style="font-size:18px;font-weight:600;line-height:26px;font-family:Arial, Helvetica, sans-serif;color:#282364;">
                                                    <strong style="font-weight:500;">Hi <?php echo $params['receiver_name']; ?></strong>,
                                                </div>
                                                <p style="font-family:Arial, Helvetica, sans-serif;font-style:normal;font-size:20px;line-height:25px;text-align:center;text-transform:uppercase;color:#282364;margin:0;margin-top:2%;">
                                                    Be ready for our Mother's Day Sale
                                                    <br />
                                                    UP TO
                                                    <strong style="color:#1688FA;">60% OFF</strong> EVERYTHING SITEWIDE
                                                </p>
                                                <p><i>Note: To make sure the products can get to you on time, we mean 10th of May, customers better place order before April 23</i></p>
                                                <?= OSC::helper('postOffice/email')->getTrackingContent() ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="content">
                                    <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width:600px;">
                                        <tr>
                                            <td background="<?= $this->getImage('postOffice/email/mothersDay/2020/1.jpg') ?>" bgcolor="#FFFFFF" style="padding: 15px; background-image: url(<?= $this->getImage('postOffice/email/mothersDay/2020/1.jpg') ?>); background-size: cover" valign="top" width="600">
                                                <!--[if gte mso 9]>
                                                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true"
                                                        stroke="false" style="width:600px;">
                                                    <v:fill type="tile"
                                                            src="<?= $this->getImage('postOffice/email/mothersDay/2020/1.jpg') ?>"
                                                            color="white"/>
                                                    <v:textbox inset="0,0,0,0">
                                                <![endif]-->
                                                <table style="border-spacing: 0px;border-collapse: collapse;">
                                                    <tr>
                                                        <td width="358">
                                                        </td>
                                                        <td width="210">
                                                            <table border="0" cellpadding="0" cellspacing="0" style="background:#FFFFFF;height: 100%;">
                                                                <tr>
                                                                    <td style="padding:15px 15px 0;" valign="top">
                                                                        <div>
                                                                            <div>
                                                                                <h3 class="product_tag_name" style="font-size:14px;font-weight:normal;line-height:17px;color:#72849C;font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;margin:0;">
                                                                                    Personalized Mug
                                                                                </h3>
                                                                                <h3 class="product_name" style="font-weight:normal;margin-top:4px;margin-bottom:4px;overflow:hidden;">
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E7B35809B27D9U/P?sref=130');?>" style="font-size:14px;font-weight:normal;line-height:17px;color:#282364;font-family: Arial, Helvetica, sans-serif;margin-top:4px;margin-bottom:4px;overflow:hidden;">Mother's Day 2020 - Mother &amp; Daughter Forever Linked Together</a>
                                                                                </h3>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:10px 15px 15px;" valign="bottom">
                                                                        <div style="padding: 5px 0; display: table;">
                                                                            <ins style="display: table-cell;width: 90px;font-weight: bold;font-size: 14px;color: #27AE60;text-decoration: none;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </ins>
                                                                            <del style="display: table-cell;width: 90px;text-align: right;font-size: 14px;color: #72849C;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </del>
                                                                        </div>
                                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E7B35809B27D9U/P?sref=130');?>" style="color: #282364;text-align: center;font-family: Arial, Helvetica, sans-serif;font-size: 16px;text-decoration: none;display: block;border: 2px solid;box-sizing: border-box;line-height: 33px;" target="_blank">Shop Now</a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                                <![endif]-->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="content">
                                    <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width:600px;">
                                        <tr>
                                            <td background="<?= $this->getImage('postOffice/email/mothersDay/2020/2.jpg') ?>" bgcolor="#FFFFFF" style="padding: 15px; background-image: url(<?= $this->getImage('postOffice/email/mothersDay/2020/2.jpg') ?>); background-size: cover" valign="top" width="600">
                                                <!--[if gte mso 9]>
                                                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true"
                                                        stroke="false" style="width:600px;">
                                                    <v:fill type="tile"
                                                            src="<?= $this->getImage('postOffice/email/mothersDay/2020/2.jpg') ?>"
                                                            color="white"/>
                                                    <v:textbox inset="0,0,0,0">
                                                <![endif]-->
                                                <table style="border-spacing: 0px;border-collapse: collapse;">
                                                    <tr>
                                                        <td width="358">
                                                        </td>
                                                        <td width="210">
                                                            <table border="0" cellpadding="0" cellspacing="0" style="background:#FFFFFF;height: 100%;">
                                                                <tr>
                                                                    <td style="padding:15px 15px 0;" valign="top">
                                                                        <div>
                                                                            <div>
                                                                                <h3 class="product_tag_name" style="font-size:14px;font-weight:normal;line-height:17px;color:#72849C;font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;margin:0;">
                                                                                    Personalized Mug
                                                                                </h3>
                                                                                <h3 class="product_name" style="font-weight:normal;margin-top:4px;margin-bottom:4px;overflow:hidden;">
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D6764F41/P?sref=130');?>" style="font-size:14px;font-weight:normal;line-height:17px;color:#282364;font-family: Arial, Helvetica, sans-serif;margin-top:4px;margin-bottom:4px;overflow:hidden;">Girl And Dogs World's Best Dog Mom</a><br /><br /><br />
                                                                                </h3>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:10px 15px 15px;" valign="bottom">
                                                                        <div style="padding: 5px 0; display: table;">
                                                                            <ins style="display: table-cell;width: 90px;font-weight: bold;font-size: 14px;color: #27AE60;text-decoration: none;letter-spacing: -0.5px;">
                                                                            </ins>
                                                                            <del style="display: table-cell;width: 90px;text-align: right;font-size: 14px;color: #72849C;letter-spacing: -0.5px;">
                                                                            </del>
                                                                        </div>
                                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D6764F41/P?sref=130');?>" style="color: #282364;text-align: center;font-family: Arial, Helvetica, sans-serif;font-size: 16px;text-decoration: none;display: block;border: 2px solid;box-sizing: border-box;line-height: 33px;" target="_blank">Shop Now</a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                                <![endif]-->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="content">
                                    <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width:600px;">
                                        <tr>
                                            <td background="<?= $this->getImage('postOffice/email/mothersDay/2020/3.jpg') ?>" bgcolor="#FFFFFF" style="padding: 15px; background-image: url(<?= $this->getImage('postOffice/email/mothersDay/2020/3.jpg') ?>); background-size: cover" valign="top" width="600">
                                                <!--[if gte mso 9]>
                                                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true"
                                                        stroke="false" style="width:600px;">
                                                    <v:fill type="tile"
                                                            src="<?= $this->getImage('postOffice/email/mothersDay/2020/3.jpg') ?>"
                                                            color="white"/>
                                                    <v:textbox inset="0,0,0,0">
                                                <![endif]-->
                                                <table style="border-spacing: 0px;border-collapse: collapse;">
                                                    <tr>
                                                        <td width="358">
                                                        </td>
                                                        <td width="210">
                                                            <table border="0" cellpadding="0" cellspacing="0" style="background:#FFFFFF;height: 100%;">
                                                                <tr>
                                                                    <td style="padding:15px 15px 0;" valign="top">
                                                                        <div>
                                                                            <div>
                                                                                <h3 class="product_tag_name" style="font-size:14px;font-weight:normal;line-height:17px;color:#72849C;font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;margin:0;">
                                                                                    Personalized Canvas
                                                                                </h3>
                                                                                <h3 class="product_name" style="font-weight:normal;margin-top:4px;margin-bottom:4px;overflow:hidden;">
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E5FBA0D24DD81P/P?sref=130');?>" style="font-size:14px;font-weight:normal;line-height:17px;color:#282364;font-family: Arial, Helvetica, sans-serif;margin-top:4px;margin-bottom:4px;overflow:hidden;">Family - Love between a Mother and Daughter is forever</a>
                                                                                </h3>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:10px 15px 15px;" valign="bottom">
                                                                        <div style="padding: 5px 0; display: table;">
                                                                            <ins style="display: table-cell;width: 90px;font-weight: bold;font-size: 14px;color: #27AE60;text-decoration: none;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </ins>
                                                                            <del style="display: table-cell;width: 90px;text-align: right;font-size: 14px;color: #72849C;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </del>
                                                                        </div>
                                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E5FBA0D24DD81P/P?sref=130');?>" style="color: #282364;text-align: center;font-family: Arial, Helvetica, sans-serif;font-size: 16px;text-decoration: none;display: block;border: 2px solid;box-sizing: border-box;line-height: 33px;" target="_blank">Shop Now</a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                                <![endif]-->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="content">
                                    <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width:600px;">
                                        <tr>
                                            <td background="<?= $this->getImage('postOffice/email/mothersDay/2020/4.jpg') ?>" bgcolor="#FFFFFF" style="padding: 15px; background-image: url(<?= $this->getImage('postOffice/email/mothersDay/2020/4.jpg') ?>); background-size: cover" valign="top" width="600">
                                                <!--[if gte mso 9]>
                                                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true"
                                                        stroke="false" style="width:600px;">
                                                    <v:fill type="tile"
                                                            src="<?= $this->getImage('postOffice/email/mothersDay/2020/4.jpg') ?>"
                                                            color="white"/>
                                                    <v:textbox inset="0,0,0,0">
                                                <![endif]-->
                                                <table style="border-spacing: 0px;border-collapse: collapse;">
                                                    <tr>
                                                        <td width="358">
                                                        </td>
                                                        <td width="210">
                                                            <table border="0" cellpadding="0" cellspacing="0" style="background:#FFFFFF;height: 100%;">
                                                                <tr>
                                                                    <td style="padding:15px 15px 0;" valign="top">
                                                                        <div>
                                                                            <div>
                                                                                <h3 class="product_tag_name" style="font-size:14px;font-weight:normal;line-height:17px;color:#72849C;font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;margin:0;">
                                                                                    Personalized Mug
                                                                                </h3>
                                                                                <h3 class="product_name" style="font-weight:normal;margin-top:4px;margin-bottom:4px;overflow:hidden;">
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E7B360382900IS/P?sref=130');?>" style="font-size:14px;font-weight:normal;line-height:17px;color:#282364;font-family: Arial, Helvetica, sans-serif;margin-top:4px;margin-bottom:4px;overflow:hidden;">Mother's Day 2020 - Dear Mom, For All The Time That I Forgot To Thank You</a>
                                                                                </h3>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:10px 15px 15px;" valign="bottom">
                                                                        <div style="padding: 5px 0; display: table;">
                                                                            <ins style="display: table-cell;width: 90px;font-weight: bold;font-size: 14px;color: #27AE60;text-decoration: none;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </ins>
                                                                            <del style="display: table-cell;width: 90px;text-align: right;font-size: 14px;color: #72849C;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </del>
                                                                        </div>
                                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E7B360382900IS/P?sref=130');?>" style="color: #282364;text-align: center;font-family: Arial, Helvetica, sans-serif;font-size: 16px;text-decoration: none;display: block;border: 2px solid;box-sizing: border-box;line-height: 33px;" target="_blank">Shop Now</a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                                <![endif]-->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="content">
                                    <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width:600px;">
                                        <tr>
                                            <td background="<?= $this->getImage('postOffice/email/mothersDay/2020/5.jpg') ?>" bgcolor="#FFFFFF" style="padding: 15px; background-image: url(<?= $this->getImage('postOffice/email/mothersDay/2020/5.jpg') ?>); background-size: cover" valign="top" width="600">
                                                <!--[if gte mso 9]>
                                                <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true"
                                                        stroke="false" style="width:600px;">
                                                    <v:fill type="tile"
                                                            src="<?= $this->getImage('postOffice/email/mothersDay/2020/5.jpg') ?>"
                                                            color="white"/>
                                                    <v:textbox inset="0,0,0,0">
                                                <![endif]-->
                                                <table style="border-spacing: 0px;border-collapse: collapse;">
                                                    <tr>
                                                        <td width="358">
                                                        </td>
                                                        <td width="210">
                                                            <table border="0" cellpadding="0" cellspacing="0" style="background:#FFFFFF;height: 100%;">
                                                                <tr>
                                                                    <td style="padding:15px 15px 0;" valign="top">
                                                                        <div>
                                                                            <div>
                                                                                <h3 class="product_tag_name" style="font-size:14px;font-weight:normal;line-height:17px;color:#72849C;font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;margin:0;">
                                                                                    Personalized Mug
                                                                                </h3>
                                                                                <h3 class="product_name" style="font-weight:normal;margin-top:4px;margin-bottom:4px;overflow:hidden;">
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E7341005670CRF/P?sref=130');?>" style="font-size:14px;font-weight:normal;line-height:17px;color:#282364;font-family: Arial, Helvetica, sans-serif;margin-top:4px;margin-bottom:4px;overflow:hidden;">Cat Mom - You had me at meow</a><br /><br />
                                                                                </h3>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="padding:10px 15px 15px;" valign="bottom">
                                                                        <div style="padding: 5px 0; display: table;">
                                                                            <ins style="display: table-cell;width: 90px;font-weight: bold;font-size: 14px;color: #27AE60;text-decoration: none;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </ins>
                                                                            <del style="display: table-cell;width: 90px;text-align: right;font-size: 14px;color: #72849C;letter-spacing: -0.5px;">
                                                                                <br />
                                                                            </del>
                                                                        </div>
                                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5E7341005670CRF/P?sref=130');?>" style="color: #282364;text-align: center;font-family: Arial, Helvetica, sans-serif;font-size: 16px;text-decoration: none;display: block;border: 2px solid;box-sizing: border-box;line-height: 33px;" target="_blank">Shop Now</a>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!--[if gte mso 9]>
                                                </v:textbox>
                                                </v:rect>
                                                <![endif]-->
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="background:#F7F8FC;padding:40px 0;margin-top:15px;">
                                        <p style="font-family:Arial, Helvetica, sans-serif;font-style:normal;font-size:18px;text-align:center;text-transform:uppercase;color:#282364;margin:0;padding-bottom:16px;line-height:21px;">
                                            see more product
                                        </p>
                                        <div style="text-align:center;">
                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=130');?>" style="width:130px;height:35px;text-align:center;margin:0 auto;border-radius:30px;background:#282364;display: inline-block;line-height: 35px;color: #fff;font-size: 16px;font-family: Arial;" target="_blank">
                                                Shop Now
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer" style="padding:25px 0;">
                                    <div>
                                        Need help? Visit
                                        <a href="mailto:support@gossby.com" style="color:#1688FA;" target="_blank">support@gossby.com</a>
                                    </div>
                                    <div>
                                        You are receiving this newsletter because you subscribed to our mailing list via:
                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=130');?>" target="_blank">gossby.com</a>
                                    </div>
                                    <div>
                                        <a href="<?= OSC::helper('postOffice/email')->getUnsubsribingUrl() ?>" target="_blank">Unsubscribe</a>
                                        &nbsp; | &nbsp;
                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/page/1/FAQs?sref=130');?>" target="_blank">Visit FAQ</a>
                                    </div>
                                    <div class="footer_social" style="padding:10px 0;margin:0 15px;">
                                        <a href="https://www.facebook.com/Gossby-100330011357456" style="display:inline-block; margin-right: 24px;" target="_blank">
                                            <img alt="Facebook" border="0" src="<?= $this->getImage('postOffice/email/icons/facebook.png') ?>" />
                                        </a>
                                        <a href="https://www.instagram.com/gossby2019/" style="display:inline-block; margin-right: 24px;" target="_blank">
                                            <img alt="Instagram" border="0" src="<?= $this->getImage('postOffice/email/icons/instagram.png') ?>" />
                                        </a>
                                        <a href="https://www.pinterest.com/gossby2019/" style="display:inline-block;" target="_blank">
                                            <img alt="Pinterest" border="0" src="<?= $this->getImage('postOffice/email/icons/Pinterest.png') ?>" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>