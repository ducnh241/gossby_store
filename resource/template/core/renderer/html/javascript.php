<?php
$this->push(array('-1000:/script/core/core/insertEvent.js'), 'js');

$JS_CODE = '';

$JS_SEPARATE = [];
$JS_CODE_READY = '';

if (isset($this->resource['js_code'])) {
    $JS_CODE = array_keys($this->resource['js_code']);
    $JS_CODE = is_array($JS_CODE) && count($JS_CODE) > 0 ? implode("\r\n", $JS_CODE) : '';
}

if (isset($this->resource['js_separate'])) {
    $JS_SEPARATE = array_keys($this->resource['js_separate']);
    $JS_SEPARATE = is_array($JS_SEPARATE) && !empty($JS_SEPARATE) ? $JS_SEPARATE : [];
}

if (isset($this->resource['js_code_ready'])) {
    $JS_CODE_READY = array_keys($this->resource['js_code_ready']);
    $JS_CODE_READY = is_array($JS_CODE_READY) && count($JS_CODE_READY) > 0 ? implode("\r\n", $JS_CODE_READY) : '';
}
?>
<?= $this->importResource('js*') ?>
<?php if ($JS_CODE) : ?>
    <script id="jscript" defer>
        <!--//--><![CDATA[//><!--
        <?php echo $JS_CODE; ?>
        //--><!]]>
    </script>
<?php endif; ?>
<?php if ($JS_SEPARATE) :
    foreach ($JS_SEPARATE as $item): ?>
    <script><?= $item; ?></script>
<?php endforeach; endif; ?>
<?php if ($JS_CODE_READY) : ?>
    <script id="jscript" defer>
        <!--//--><![CDATA[//><!--
        $(document).ready(function () {
            <?php echo $JS_CODE_READY; ?>
        })
        //--><!]]>
    </script>
<?php endif; ?>