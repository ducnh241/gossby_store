<?php
$default = array('type' => 'text', 'class' => '', 'width' => '200');

foreach($default as $k => $v) {    
    if(! isset($params[$k])) {
        $params[$k] = $v;
    }
}

if($params['type'] == 'password') {
    $params['type'] = 'text';
}

$params['class'] .= ' mrk-osc-input-init';
$params['class'] = trim($params['class']);

$ui_attr = array('icon');

foreach($ui_attr as $k) {
    if(! isset($params[$k])) {
        continue;
    }
    
    $params['ui-' . $k] = $params[$k];
    unset($params[$k]);
}

foreach($params as $k => $v) {    
    $params[$k] = "{$k}=\"{$v}\"";
}

$params = implode(' ', $params);
?>
<input <?php echo $params; ?>/>