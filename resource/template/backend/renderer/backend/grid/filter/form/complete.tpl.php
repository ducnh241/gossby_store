<?php
$params['groups'] = implode('', $params['groups']);
?>
<div class="grid-filter-frm" id="<?php echo $params['index'] ?>-filter-frm">
    <form action="<?php echo $params['process_url'] ?>" method="post">
        <div class="grid-filter-frm-head mrk-grid-filter-frm-head" id="<?php echo $params['index'] ?>-filter-frm-head">
            Filter form
            <i class="grid-filter-frm-close-btn mrk-grid-filter-frm-close" rel="<?php echo $params['index'] ?>"><i></i></i>
        </div>
        <div>
            <?php echo $params['groups']; ?>
        </div>
        <div class="act-bar">
            <span class="btn red">Cancel</span>
            <button type="submit" class="btn">Update Filter</button>
        </div>
    </form>
</div>