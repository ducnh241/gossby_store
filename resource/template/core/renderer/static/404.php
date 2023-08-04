<?php
/* @var $this Helper_Frontend_Template */
$collection = $params['bestSelling'];
$query_string = $_SERVER['QUERY_STRING'] ? ('?' . $_SERVER['QUERY_STRING']) : '';
$current_lang_key = OSC::core('language')->getCurrentLanguageKey();
$home_page_url = OSC_CMS_BASE_URL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>404 ERROR</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0"/>
    <link rel="icon" href="/resource/template/frontend/default2/image/favicon.png" type="image/x-icon" />
    <link rel="shortcut icon" href="/resource/template/frontend/default2/image/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->getFile('style/static/page-404_v3.css'); ?>" />
</head>
<body>
<div class="page_404">
    <div class="logo">
        <a href="<?= $home_page_url ?>" title="">
            <img class="logo-header" src="<?= OSC::helper('frontend/template')->getLogo()->url ?>" alt="<?= $this->getLogo()->alt ?>"/>
        </a>
    </div>
    <img class="logo-404" src="/resource/template/frontend/default3/image/404.svg"/>
    <div class="title">Oops... This page was not found!</div>
    <a href="<?= $home_page_url ?>" class="btn_back_home">Back Home</a>

    <div class="copyright">
        Copyright © 2019 Gossby. All Rights Reserved
    </div>
</div>

<script type="text/javascript">
</script>
</body>
</html>
