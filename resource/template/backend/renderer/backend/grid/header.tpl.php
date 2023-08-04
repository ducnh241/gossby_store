<?php

$HTML = '';

foreach ($params as $header) {
    if ($header[1]) {
        $header[1] = "style=\"width: {$header[1]}\"";
    } else {
        $header[1] = '';
    }

    $HTML .= "<td class=\"{$header[2]}\"{$header[1]}>{$header[0]}</td>";
}

$HTML = "<tr class=\"header\">{$HTML}</tr>";

echo $HTML;
?>
