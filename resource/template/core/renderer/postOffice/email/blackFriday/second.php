<html xmlns="http://www.w3.org/1999/xhtml">  
    <head>    
        <title>
            Thank you
        </title>
        <style type="text/css">
            /* Remove space around the email design. */
            html,
            body {
                margin: 0 auto !important;
                padding: 0 !important;
                height: 100% !important;
                width: 100% !important;
                font-size: 14px;
                line-height: 22px
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
                mso-table-lspace: 0pt !important;
                mso-table-rspace: 0pt !important;
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
                background: white
            }

            .top {
                background: #F7F8FC;
                text-align: center;
                padding: 15px 0
            }

            .logo {
                display: inline-block
            }


            .header {
                padding: 15px 0;
            }

            .email_title {
                text-align: center;
                font-weight: normal;
                font-size: 25px;
                margin: 0 0 20px;
                padding: 22px 0
            }

            .button a {
                text-decoration: none !important;
                color: white !important;
            }


            .product_list {
                width: 100%;
                padding-bottom: 5px;
                margin-top: 5px
            }

            .product_list .product_info {
            }

            .product_list .product_price {
                max-width: 120px;
                text-align: right;
                white-space: nowrap;
            }

            .product_list .product_price_mobile {
                display: none
            }


            .footer {
                padding: 25px 0;
                text-align: center;
                font-size: 12px;
                color: #AEB2BF;
            }

            .footer a {
                color: #AEB2BF
            }

            .footer a:hover {
                color: #1688FA
            }

            .footer_social {
                padding: 10px 0
            }

            .footer_social a {
                margin: 0 15px
            }

            .footer_social a img {
                width: 15px
            }


            .address_info td {
                padding: 15px
            }



        </style>
        <!--[if mso]>
            <style type="text/css">
                body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
                a {text-decoration: none;}
            </style>
            <![endif]-->
    </head>
    <body style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly;" width="100%">    <!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->
        <!--[if (gte mso 9)|(IE)]>
        <table width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td>
        <![endif]-->
        <table bgcolor="#E0E0E0" border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">      
            <tr>        
                <td>          
                    <table align="center" border="0" cellpadding="0" cellspacing="0" style="background: white" width="600">            
                        <tr>              
                            <td class="top" style="max-width: 600px;width: 221px;height: 21px; padding: 15px 0">                
                                <a class="logo" href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=6&_dcode=BF302019') ?>" title="">
                                    <img alt="" height="25" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/41ee9069-1b99-4db7-ad7f-75ccae03fa92.png" width="112"/>
                                </a>
                            </td>
                        </tr>
                        <tr>              
                            <td>                
                                <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=6&_dcode=BF302019') ?>" style="text-align: center" title="">
                                    <img alt="" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/486fadd9-f1c9-42c1-945d-da32929a4aab.png" width="600"/>
                                </a>
                            </td>
                        </tr>
                        <tr>              
                            <td class="main">                
                                <table align="center" border="0" cellspacing="0" class="container" role="presentation">                  
                                    <tr>                    
                                        <td class="header">                      
                                            <table align="center" border="0" cellspacing="0" role="presentation" style="padding: 30px;" width="100%">                        
                                                <tr>                          
                                                    <td align="center" style="text-align: center" valign="middle">                            
                                                        <div style="font-size: 18px;font-weight: 600;line-height: 26px;text-transform: unset;font-family: Arial, Helvetica, sans-serif;color: #282364">                              
                                                            <strong style="font-weight: 500"> Hi <?= $params['first_name'] ?>!</strong>
                                                        </div>
                                                        <p style="font-family: Arial, Helvetica, sans-serif;font-style: normal;font-weight: bold;font-size: 20px;line-height: 22px;text-align: center;text-transform: uppercase;color: #282364;margin: 0;margin-top: 2%;">
                                                            IT'S TIME FOR ONLY SALE OF THE YEAR. BLACK FRIDAY STARTS... NOW!
                                                            <br/>
                                                            <strong style="color:#1688FA">30% OFF</strong> sitewide.
                                                        </p><?= OSC::helper('postOffice/email')->getTrackingContent() ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>                    
                                        <td class="content">                      
                                            <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width: 600px;height: 250px;">                        
                                                <tr>                          
                                                    <td background="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/489ef8bf-d721-4f94-82f6-84cd3348c810.png" bgcolor="white" height="250" style="background-image: url('https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/489ef8bf-d721-4f94-82f6-84cd3348c810.png');background-size: cover" valign="top" width="600">                            <!--[if gte mso 9]>
                                                        <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:250px;">
                                                            <v:fill type="tile" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/489ef8bf-d721-4f94-82f6-84cd3348c810.png" color="white" />
                                                            <v:textbox inset="0,0,0,0">
                                                        <![endif]-->
                                                        <table>                              
                                                            <tr>                                
                                                                <td width="550">
                                                                    &nbsp;
                                                                </td>
                                                                <td height="220" width="210">                                  
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background: white;padding: 14px 14px 14px 14px;margin-top: 20px;margin-right: 15px;margin-bottom: 17px;">                                    
                                                                        <tr>                                      
                                                                            <td>                                        
                                                                                <div>                                          
                                                                                    <div>                                            
                                                                                        <h3 class="product_tag_name" style="font-size: 12px;font-weight: normal;line-height: 15px;color: #72849C;font-family: Arial, Helvetica, sans-serif; margin: 0;">
                                                                                            Personalized Best Friends Circle Ornament - Best Friends Forever
                                                                                        </h3>
                                                                                        <h3 class="product_name" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">                                              
                                                                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D44D4CRZ/2/You_are_my_person_You_will_always_be_my_person?sref=6&_dcode=BF302019') ?>" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">
                                                                                                Circle: 3,3" x 3,3" /  Star: 3,6" x 3,5"<br/>

                                                                                                1-sided design<br/>
                                                                                                Ribbon for hanging included
                                                                                            </a>
                                                                                        </h3>
                                                                                    </div>
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D44D4CRZ/2/You_are_my_person_You_will_always_be_my_person?sref=6&_dcode=BF302019') ?>" style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;" target="_blank">
                                                                                        <div>
                                                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                                                <tr>
                                                                                                    <td>
                                                                                                        <div class="full" style="height: 30px;line-height: 18px; width: 183px;background: white;text-align: center;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0">
                                                                                                                <tr>
                                                                                                                    <td align="center" bgcolor="#fff" cellpadding="0" style="height: 27px;line-height: 18px; width: 183px;border: 1px solid #282364;" valign="middle">
                                                                                                                        <div style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;">
                                                                                                                            Shop Now
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                    </a>
                                                                                </div>
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
                                            <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width: 600px;height: 250px;">                        
                                                <tr>                          
                                                    <td background="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/7a3545cc-1ca1-4b63-a685-000f8a0876a8.png" bgcolor="white" height="250" style="background-image: url('https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/7a3545cc-1ca1-4b63-a685-000f8a0876a8.png');background-size: cover" valign="top" width="600">                            <!--[if gte mso 9]>
                                                        <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:250px;">
                                                            <v:fill type="tile" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/7a3545cc-1ca1-4b63-a685-000f8a0876a8.png" color="white" />
                                                            <v:textbox inset="0,0,0,0">
                                                        <![endif]-->
                                                        <table>                              
                                                            <tr>                                
                                                                <td width="550">
                                                                    &nbsp;
                                                                </td>
                                                                <td height="220" width="210">                                  
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background: white;padding: 14px 14px 14px 14px;margin-top: 20px;margin-right: 15px;margin-bottom: 17px;">                                    
                                                                        <tr>                                      
                                                                            <td>                                        
                                                                                <div>                                          
                                                                                    <div>                                            
                                                                                        <h3 class="product_tag_name" style="font-size: 12px;font-weight: normal;line-height: 15px;color: #72849C;font-family: Arial, Helvetica, sans-serif; margin: 0;">
                                                                                            Personalized Mug
                                                                                        </h3>
                                                                                        <h3 class="product_name" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">                                              
                                                                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D6D6E8IN/18/Personalized_Mug_-_Curvy_Girls?sref=6&_dcode=BF302019') ?>" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">
                                                                                                Curvy Girls - I&rsquo;m pretty sure we are more than best friends. We are like a really small gang.
                                                                                            </a>
                                                                                        </h3>
                                                                                    </div>
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D6D6E8IN/18/Personalized_Mug_-_Curvy_Girls?sref=6&_dcode=BF302019') ?>" style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;" target="_blank">
                                                                                        <div>
                                                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                                                <tr>
                                                                                                    <td>
                                                                                                        <div class="full" style="height: 30px;line-height: 18px; width: 183px;background: white;text-align: center;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0">
                                                                                                                <tr>
                                                                                                                    <td align="center" bgcolor="#fff" cellpadding="0" style="height: 27px;line-height: 18px; width: 183px;border: 1px solid #282364;" valign="middle">
                                                                                                                        <div style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;">
                                                                                                                            Shop Now
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                    </a>
                                                                                </div>
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
                                            <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width: 600px;height: 250px;">                        
                                                <tr>                          
                                                    <td background="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/884718d1-8350-42a0-b0e2-eea2680de394.png" bgcolor="white" height="250" style="background-image: url('https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/884718d1-8350-42a0-b0e2-eea2680de394.png');background-size: cover" valign="top" width="600">                            <!--[if gte mso 9]>
                                                          <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:250px;">
                                                              <v:fill type="tile" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/884718d1-8350-42a0-b0e2-eea2680de394.png" color="white" />
                                                              <v:textbox inset="0,0,0,0">
                                                          <![endif]-->
                                                        <table>                              
                                                            <tr>                                
                                                                <td width="550">
                                                                    &nbsp;
                                                                </td>
                                                                <td height="220" width="210">                                  
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background: white;padding: 14px 14px 14px 14px;margin-top: 20px;margin-right: 15px;margin-bottom: 17px;">                                    
                                                                        <tr>                                      
                                                                            <td>                                        
                                                                                <div>                                          
                                                                                    <div>                                            
                                                                                        <h3 class="product_tag_name" style="font-size: 12px;font-weight: normal;line-height: 15px;color: #72849C;font-family: Arial, Helvetica, sans-serif; margin: 0;">
                                                                                            Personalized Mug - Girl and Dogs christmas. Life is better with dogs.
                                                                                        </h3>
                                                                                        <h3 class="product_name" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">                                              
                                                                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D5F0A4SW/14/Life_is_better_with_dogs?sref=6&_dcode=BF302019') ?>" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">
                                                                                                High-quality white ceramic.<br/>
                                                                                                Printed on Both Sides.<br/>

                                                                                                Microwave and dishwasher safe.<br/>

                                                                                                Skin color, hairstyle, name, dog breeds... can be changed.
                                                                                            </a>
                                                                                        </h3>
                                                                                    </div>
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D5F0A4SW/14/Life_is_better_with_dogs?sref=6&_dcode=BF302019') ?>" style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;" target="_blank">
                                                                                        <div>
                                                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                                                <tr>
                                                                                                    <td>
                                                                                                        <div class="full" style="height: 30px;line-height: 18px; width: 183px;background: white;text-align: center;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0">
                                                                                                                <tr>
                                                                                                                    <td align="center" bgcolor="#fff" cellpadding="0" style="height: 27px;line-height: 18px; width: 183px;border: 1px solid #282364;" valign="middle">
                                                                                                                        <div style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;">
                                                                                                                            Shop Now
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                    </a>
                                                                                </div>
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
                                        <td border="0" cellpadding="0" cellspacing="0" class="content">                      
                                            <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width: 600px;height: 250px;padding: 0">                        
                                                <tr>                          
                                                    <td height="250" style="background-image: url('https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/fd196696-08fc-48d0-b98b-b8559b379880.png');background-size: cover;padding: 0;" valign="middle" width="600">                            <!--[if gte mso 9]>
                                                      <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:250px;">
                                                          <v:fill type="tile" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/fd196696-08fc-48d0-b98b-b8559b379880.png" color="white" />
                                                          <v:textbox inset="0,0,0,0">
                                                      <![endif]-->
                                                        <table border="0" cellpadding="0" cellspacing="0">                              
                                                            <tr>                                
                                                                <td width="550">
                                                                    &nbsp;
                                                                </td>
                                                                <td height="220" width="210">                                  
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background: white;padding: 14px 14px 14px 14px;margin-top: 20px;margin-right: 15px;margin-bottom: 17px;">                                    
                                                                        <tr>                                      
                                                                            <td>                                        
                                                                                <div>                                          
                                                                                    <div>                                            
                                                                                        <h3 class="product_tag_name" style="font-size: 12px;font-weight: normal;line-height: 15px;color: #72849C;font-family: Arial, Helvetica, sans-serif; margin: 0;">
                                                                                            Personalized Mug - Wizard Best Friends, Two Girl Always Sister
                                                                                        </h3>
                                                                                        <h3 class="product_name" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">                                              
                                                                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D71BC28F/2/Personalized_Mug_-_Wizard_Best_Friends?sref=6&_dcode=BF302019') ?>" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">
                                                                                                High-quality white ceramic.<br/>

                                                                                                Printed on Both Sides.<br/>

                                                                                                Microwave and dishwasher safe.<br/>

                                                                                                Skin color, hairstyle, name,  can be changed.
                                                                                            </a>
                                                                                        </h3>
                                                                                    </div>
                                                                                    <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D71BC28F/2/Personalized_Mug_-_Wizard_Best_Friends?sref=6&_dcode=BF302019') ?>" style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;" target="_blank">
                                                                                        <div>
                                                                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                                                <tr>
                                                                                                    <td>
                                                                                                        <div class="full" style="height: 30px;line-height: 18px; width: 183px;background: white;text-align: center;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0">
                                                                                                                <tr>
                                                                                                                    <td align="center" bgcolor="#fff" cellpadding="0" style="height: 27px;line-height: 18px; width: 183px;border: 1px solid #282364;" valign="middle">
                                                                                                                        <div style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;">
                                                                                                                            Shop Now
                                                                                                                        </div>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </div>
                                                                                    </a>
                                                                                </div>
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
                                            <table align="center" border="0" cellspacing="0" class="product_list" role="presentation" style="width: 600px;height: 250px;">                        
                                                <tr>                          
                                                    <td background="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/1387b6bf-49d9-48f5-876a-3dc263d527f8.png" bgcolor="white" height="250" style="background-image: url('https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/1387b6bf-49d9-48f5-876a-3dc263d527f8.png');background-size: cover" valign="top" width="600">                            <!--[if gte mso 9]>
                                                    <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:250px;">
                                                        <v:fill type="tile" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/1387b6bf-49d9-48f5-876a-3dc263d527f8.png" color="white" />
                                                        <v:textbox inset="0,0,0,0">
                                                    <![endif]-->
                                                        <table>                              
                                                            <tr>                                
                                                                <td width="550">
                                                                    &nbsp;
                                                                </td>
                                                                <td height="220" width="210">                                  
                                                                    <table border="0" cellpadding="0" cellspacing="0" style="background: white;padding: 14px 14px 14px 14px;margin-top: 20px;margin-right: 15px;margin-bottom: 17px;">                                    
                                                                        <tr>                                      
                                                                            <td>                                        
                                                                                <div>                                          
                                                                                    <div>                                            
                                                                                        <h3 class="product_tag_name" style="font-size: 12px;font-weight: normal;line-height: 15px;color: #72849C;font-family: Arial, Helvetica, sans-serif; margin: 0;">
                                                                                            Personalized Mug - Best Friend , You are my person, You will always be my person
                                                                                        </h3>
                                                                                        <h3 class="product_name" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">                                              
                                                                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D44D4CRZ/2/You_are_my_person_You_will_always_be_my_person?variant=16&sref=6&_dcode=BF302019') ?>" style="font-size: 14px;font-weight: normal;line-height: 18px;color: #282364;font-family: Arial, Helvetica, sans-serif;margin-top: 4px;margin-bottom: 4px; text-overflow: ellipsis;overflow: hidden;word-break: break-word;">
                                                                                                High-quality white ceramic mug.<br/>

                                                                                                Printed on Both Sides.<br/>

                                                                                                Microwave and dishwasher safe.<br/>

                                                                                                Skin color, hairstyle, name,  can be changed.
                                                                                            </a>
                                                                                        </h3>
                                                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/5DC9278D44D4CRZ/2/You_are_my_person_You_will_always_be_my_person?variant=16&sref=6&_dcode=BF302019') ?>" style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;" target="_blank">
                                                                                            <div>
                                                                                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                                                    <tr>
                                                                                                        <td>
                                                                                                            <div class="full" style="height: 30px;line-height: 18px; width: 183px;background: white;text-align: center;">
                                                                                                                <table border="0" cellpadding="0" cellspacing="0">
                                                                                                                    <tr>
                                                                                                                        <td align="center" bgcolor="#fff" cellpadding="0" style="height: 27px;line-height: 18px; width: 183px;border: 1px solid #282364;" valign="middle">
                                                                                                                            <div style="color: #282364;text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;">
                                                                                                                                Shop Now
                                                                                                                            </div>
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                            </div>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
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
                                            <div style="height: 110px;background: #F7F8FC;padding-top: 40px;margin-top: 15px;">                        
                                                <p style="font-family: Arial, Helvetica, sans-serif;font-style: normal;font-weight: bold;font-size: 18px;text-align: center;text-transform: uppercase;color: #282364;margin: 0; padding-bottom: 16px;line-height: 21px;">
                                                    see more product
                                                </p>
                                                <div style="text-align: center;">                          
                                                    <table style="border: 1px solid #282364;box-sizing: border-box;width: 130px;height: 35px;text-align: center;margin: 0 auto;border-radius: 30px;background: #282364;">                            
                                                        <tr>                              
                                                            <td>                                
                                                                <table align="center" style="width: 130px;" valign="middle">                                  
                                                                    <tr>                                    
                                                                        <td align="center" style="height: 30px;line-height: 18px; width: 100px;text-align: center;" valign="middle">                                      
                                                                            <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/?sref=6&_dcode=BF302019') ?>" style="text-align: center; ;font-family: Arial, Helvetica, sans-serif;font-size: 14px;text-decoration: none;font-weight:bold;display: inline-block;color: white;" target="_blank">
                                                                                Shop Now
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>                    
                                        <table align="center" style="text-align: center;">                      
                                            <tr>                        
                                                <td class="footer" style="padding: 25px 0">
                                                    Need help? Visit
                                                    <a href="mailto:support@gossby.com" style="color: #1688FA" target="_blank">support@gossby.com</a>
                                                    <div style="font-size: 12px">
                                                        You are receiving this newsletter because you subscribed to our mailing list via: gossby.com
                                                    </div>
                                                    <div>                            <a href="<?= OSC::helper('postOffice/email')->getUnsubsribingUrl() ?>" target="_blank">Unsubscribe</a> |
                                                        <a href="<?= OSC::helper('postOffice/email')->getClickUrl(OSC::$base_url . '/page/1/FAQs?sref=6&_dcode=BF302019') ?>" target="_blank">Visit FAQ</a>
                                                    </div>
                                                    <div class="footer_social" style="padding: 10px 0; margin: 0 15px">                            
                                                        <a href="https://www.facebook.com/Gossby-100330011357456" style="display: inline-block">
                                                            <img alt="Facebook" border="0" src="https://d3k81ch9hvuctc.cloudfront.net/company/MF3R6J/images/979eddae-504b-499d-9fad-78b717f29269.png" style="height: 15px;width: 9px"/></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
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
