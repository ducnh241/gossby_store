<?php
/* @var $this Helper_Backend_Template */

$this->push('core/setting.scss', 'css');
?>
<div class="setting-config-group">
    <div class="info">
        <?php if ($params['group']['title']) : ?>
            <div class="title"><?= $params['group']['title'] ?></div>
        <?php endif; ?>
        <?php if ($params['group']['description']) : ?>
            <div class="desc"><?= $params['group']['description'] ?></div>
        <?php endif; ?>
    </div>
    <div class="block">
        <div class="p20">
            <?php
            $rows = [];
            $row_items = [];
            ?>
            <?php foreach ($params['group']['items'] as $idx => $item) : ?>
                <?php
                if ($item['line_before'] || $item['row_before_title'] || $item['row_before_desc'] || $item['full_row'] || count($row_items) == 2) {
                    if (count($row_items) > 0) {
                        if (count($row_items) < 2) {
                            $row_items[] = '<div>&nbsp;</div>';
                        }

                        $rows[] = '<div class="frm-grid frm-grid--separate">' . implode('', $row_items) . '</div>';

                        $row_items = [];
                    }

                    if ($item['line_before']) {
                        $rows[] = '<div class="frm-line e20"></div>';
                    }

                    if ($item['row_before_title'] || $item['row_before_desc']) {
                        $rows[] = '<div class="frm-heading"><div class="frm-heading__main">' . ($item['row_before_title'] ? ('<div class="frm-heading__title">' . $item['row_before_title'] . '</div>') : '') . ($item['row_before_desc'] ? ('<div class="frm-heading__desc">' . $item['row_before_desc'] . '</div>') : '') . '</div></div>';
                    }
                }

                $item['value'] = isset($params['new_setting_values'][$item['key']]) ? $params['new_setting_values'][$item['key']] : OSC::helper('core/setting')->get($item['key']);

                $item_html = $this->build('core/setting/type', ['item' => $item, 'setting_types' => $params['setting_types']]);

                if ($item['full_row']) {
                    $rows[] = '<div class="frm-grid frm-grid--separate">' . $item_html . '</div>';
                } else {
                    $row_items[] = $item_html;
                }

                if ($item['line_after'] || $item['row_after_desc']) {
                    if (count($row_items) > 0) {
                        if (count($row_items) < 2) {
                            $row_items[] = '<div>&nbsp;</div>';
                        }

                        $rows[] = '<div class="frm-grid frm-grid--separate">' . implode('', $row_items) . '</div>';

                        $row_items = [];
                    }

                    if ($item['line_after']) {
                        $rows[] = '<div class="frm-line e20"></div>';
                    }

                    if ($item['row_after_desc']) {
                        $rows[] = '<div class="frm-desc">' . $item['row_after_desc'] . '</div>';
                    }
                }
                ?>
            <?php endforeach; ?>
            <?php
            if (count($row_items) > 0) {
                if (count($row_items) < 2) {
                    $row_items[] = '<div>&nbsp;</div>';
                }

                $rows[] = '<div class="frm-grid frm-grid--separate">' . implode('', $row_items) . '</div>';
            }
            ?>
            <?= implode('', $rows) ?>
        </div>
    </div>
</div>