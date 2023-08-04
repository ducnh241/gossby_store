<?php
/* @var $this Helper_Frontend_Template */

$lang = OSC::core('language')->get();

$style = '';

if (isset($this->resource['css_code'])) {
    $CSS_CODE = array_keys($this->resource['css_code']);
    $CSS_CODE = is_array($CSS_CODE) && count($CSS_CODE) > 0 ? implode("\r\n", $CSS_CODE) : '';

    if ($CSS_CODE != '') {
        $style = "<style type=\"text/css\">{$CSS_CODE}</style>";
    }
}

if (isset($this->resource['head_tags']) && is_array($this->resource['head_tags']) && count($this->resource['head_tags']) > 0) {
    if (!isset($params['HEAD_TAGS'])) {
        $params['HEAD_TAGS'] = '';
    }

    $params['HEAD_TAGS'] .= implode('', array_keys($this->resource['head_tags']));
}

$metadata_tags = '';

if (isset($params['metadata_tags']) && is_array($params['metadata_tags']) && count($params['metadata_tags']) > 0) {

}

$this->push(array(
    '10000:/script/community/jquery/jquery-1.7.2.js',
    '9999:/script/core/core/osecore.js',
    '/script/community/jquery/plugin/cookie.jquery.js',
    '/script/community/xregexp-all.js'
), 'js/top');

$JS_INIT_CODE = '';

if (isset($this->resource['js_init'])) {
    $JS_INIT_CODE = array_keys($this->resource['js_init']);
    $JS_INIT_CODE = is_array($JS_INIT_CODE) && count($JS_INIT_CODE) > 0 ? implode("\r\n", $JS_INIT_CODE) : '';
}
?>
<head>
    <title><?= $params['title']; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="p:domain_verify" content="d0c07eefae45b3538a6fbd8a8197ce09"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <link rel="icon" href="<?= OSC::helper('frontend/template')->getFavicon()->url ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?= OSC::helper('frontend/template')->getFavicon()->url ?>" type="image/x-icon" />
    <?php if (isset($params['metadata_tags']) && is_array($params['metadata_tags']) && count($params['metadata_tags']) > 0) : ?>
        <?php foreach ($params['metadata_tags'] as $key => $value) : ?>
            <meta <?php if (strtolower(substr($key, 0, 3)) === 'og:') : ?>property<?php else : ?>name<?php endif; ?>="<?php echo $key; ?>" content="<?php echo $this->safeString($value); ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
    <meta name="robots" content="index, follow" />
    <meta property="fb:app_id" content="<?= OSC::helper('core/setting')->get('post/comment/facebook/app_id') ?>" />
    <?php
    if (isset($params['HEAD_TAGS'])) {
        echo $params['HEAD_TAGS'];
    }
    ?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "url": "<?= OSC::$base_url ?>",
            "logo": "<?= OSC::helper('frontend/template')->getLogo()->url ?>"
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "url": "<?= OSC::$base_url ?>",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "<?php echo OSC::$base_url . '/' . OSC::core('language')->getCurrentLanguageKey() . '/search'; ?>?keywords={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
    </script>
    <script>
        <!--//--><![CDATA[//><!--
        var OSC_DOMAIN = '<?php echo OSC::$domain; ?>';
        var OSC_BASE = '<?php echo OSC::$base; ?>';
        var OSC_BASE_URL = '<?php echo OSC::$base_url; ?>';
        //D2 Flow
        const D2_FLOW_BASE_URL = '<?= D2_FLOW_BASE_URL ?>'
        var OSC_FRONTEND_BASE_URL = '<?php echo OSC_FRONTEND_BASE_URL ?>';
        const OSC_STORAGE_BASE_URL = '<?= OSC::core('aws_s3')->getS3CDNUrl() ?>';
        var OSC_TPL_BASE_URL = '<?php echo OSC::core('template')->tpl_base_url; ?>';
        var OSC_TPL_CSS_BASE_URL = '<?= OSC::core('template')->tpl_base_url . '/style' ?>';
        var OSC_TPL_JS_BASE_URL = '<?= OSC::core('template')->tpl_base_url . '/script' ?>';
        var OSC_TPL_IMG_BASE_URL = '<?= OSC::core('template')->tpl_base_url . '/image' ?>';
        var OSC_TIMEZONE = '<?= OSC::helper('core/setting')->get('core/timezone') ?>';
        <?= $JS_INIT_CODE ?>
        <?= $params['url_js_init'] ?>
        //--><!]]>
    </script>
    <?php echo $this->importResource('css'); ?>
    <?php echo $style; ?>
    <?= $this->importResource('js/top') ?>
</head>
