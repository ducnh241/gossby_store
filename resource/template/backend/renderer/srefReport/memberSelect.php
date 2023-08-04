<?php
if ($params['selectors']) :
?>
<div class="members-report">
    <div class="styled-select--small">
        <?php
            if (!isset($params['product_page']) || $params['product_page'] != 1) {
                echo $this->build('srefReport/memberSelectGroupItem', ['action' => $params['action'] ?? 'index', 'items' => $params['selectors']]);
            }
        ?>
    </div>
</div>
<?php
endif;
?>
