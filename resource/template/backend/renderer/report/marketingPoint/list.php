<?php
/* @var $this Helper_Backend_Template */
$this->addComponent('daterangepicker');
$this->push(array('report/common.js'), 'js');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$list_members = OSC::helper('report/common')->getListMemberMkt();

$data = $params['data'];
$range = $params['range'];

if (is_array($range)) {
    if ($range[0] == $range[1]) {
        $range = $range[0];
    } else {
        $range = implode('-', $range);
    }
}

?>
<div class="dashboard">
    <div class="filter-menu">
        <?php if (OSC::controller()->checkPermission('srefReport', false)) : ?>
            <?= $this->build('srefReport/memberSelect', ['action' => $params['action'], 'selectors' => $params['selectors']])?>
        <?php endif; ?>
        <ul class="date-range">
            <li<?= ($params['range'] == 'today') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'today']); ?>">Today</a></li>
            <li<?= ($params['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'yesterday']); ?>">Yesterday</a></li>
            <li<?= ($params['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thisweek']); ?>">This Week</a></li>
            <li<?= ($params['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastweek']); ?>">Last Week</a></li>
            <li<?= ($params['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thismonth']); ?>">This Month</a></li>
            <li<?= ($params['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastmonth']); ?>">Last Month</a></li>
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
            <li<?= (is_array($params['time_range']['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['time_range']['range']) ? $params['time_range']['range'][0] : '' ?>" data-end="<?= is_array($params['time_range']['range']) ? $params['time_range']['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>

<div class="block m25">
    <div class="loading-overlay-wrapper white-bg">
        <div class="loading-overlay"></div>
    </div>
    <div class="loading-wrapper">
        <?php if ($params['total_rows'] > 0) : ?>
            <?php if ($params['view_mode'] == 'total') : ?>
                <table class="grid grid-borderless">
                    <tr>
                        <th style="text-align: left">Member ID</th>
                        <th style="text-align: left">Member Name</th>
                        <th style="text-align: left" <?= $params['options']['sort'] === 'point' ? 'class="active"' : ''; ?>>
                            Sref Point
                            <a href="<?= $this->rebuildUrl(['sort' => 'point', 'order' => 'desc']); ?>">
                                <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($params['options']['sort'] === 'point' && $params['options']['order'] === 'desc') ? 'active ml5':'ml5']); ?>
                            </a>
                            <a href="<?= $this->rebuildUrl(['sort' => 'point', 'order' => 'asc']); ?>">
                                <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($params['options']['sort'] === 'point' && $params['options']['order'] === 'asc') ? 'active ml5':'ml5']); ?>
                            </a>
                        </th>
                        <th style="text-align: left" <?= $params['options']['sort'] === 'vendor_point' ? 'class="active"' : ''; ?>>
                            Vendor Point
                            <a href="<?= $this->rebuildUrl(['sort' => 'vendor_point', 'order' => 'desc']); ?>">
                                <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($params['options']['sort'] === 'vendor_point' && $params['options']['order'] === 'desc') ? 'active ml5':'ml5']); ?>
                            </a>
                            <a href="<?= $this->rebuildUrl(['sort' => 'vendor_point', 'order' => 'asc']); ?>">
                                <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($params['options']['sort'] === 'vendor_point' && $params['options']['order'] === 'asc') ? 'active ml5':'ml5']); ?>
                            </a>
                        </th>
                        <th style="width: 170px; text-align: left" <?= $params['options']['sort'] === 'total_point' ? 'class="active"' : ''; ?>>
                            Total point
                            <a href="<?= $this->rebuildUrl(['sort' => 'total_point', 'order' => 'desc']); ?>">
                                <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($params['options']['sort'] === 'total_point' && $params['options']['order'] === 'desc') ? 'active ml5':'ml5']); ?>
                            </a>
                            <a href="<?= $this->rebuildUrl(['sort' => 'total_point', 'order' => 'asc']); ?>">
                                <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($params['options']['sort'] === 'total_point' && $params['options']['order'] === 'asc') ? 'active ml5':'ml5']); ?>
                            </a>
                        </th>
                    </tr>
                    <?php
                    foreach ($data as $member_id => $item):
                        ?>
                        <tr>
                            <td style="text-align: left"><?= $item['member_id'] ?></td>
                            <td style="text-align: left"><?= $item['name'] ?></td>
                            <td style="text-align: left"><?= $item['point'] ?></td>
                            <td style="text-align: left"><?= $item['vendor_point'] ?></td>
                            <td style="text-align: left"><?= $item['total_point'] ?></td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </table>
            <?php else: ?>
                <table class="grid grid-borderless">
                        <tr>
                            <th style="text-align: left">Order ID</th>
                            <th style="width: 170px; text-align: left">Order Item ID</th>
                            <th style="width: 150px; text-align: left">Product ID</th>
                            <th style="text-align: left">Sref ID</th>
                            <th style="text-align: left" <?= $params['options']['sort'] === 'point' ? 'class="active"' : ''; ?>>
                                Sref Point
                                <a href="<?= $this->rebuildUrl(['sort' => 'point', 'order' => 'desc']); ?>">
                                    <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($params['options']['sort'] === 'point' && $params['options']['order'] === 'desc') ? 'active ml5':'ml5']); ?>
                                </a>
                                <a href="<?= $this->rebuildUrl(['sort' => 'point', 'order' => 'asc']); ?>">
                                    <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($params['options']['sort'] === 'point' && $params['options']['order'] === 'asc') ? 'active ml5':'ml5']); ?>
                                </a>
                            </th>
                            <th style="text-align: left">Vendor</th>
                            <th style="text-align: left">
                                Vendor Point
                                <a href="<?= $this->rebuildUrl(['sort' => 'vendor_point', 'order' => 'desc']); ?>">
                                    <?= $this->getIcon('arrow-down', ['height' => 10, 'class'=> ($params['options']['sort'] === 'vendor_point' && $params['options']['order'] === 'desc') ? 'active ml5':'ml5']); ?>
                                </a>
                                <a href="<?= $this->rebuildUrl(['sort' => 'vendor_point', 'order' => 'asc']); ?>">
                                    <?= $this->getIcon('arrow-up', ['height' => 10, 'class'=> ($params['options']['sort'] === 'vendor_point' && $params['options']['order'] === 'asc') ? 'active ml5':'ml5']); ?>
                                </a>
                            </th>
                        </tr>
                        <?php
                        foreach ($data as $item):
                            ?>
                            <tr>
                                <td style="text-align: left"><?= $item->data['order_id'] ?></td>
                                <td style="text-align: left"><?= $item->data['order_line_item_id'] ?></td>
                                <td style="text-align: left"><?= $item->data['product_id'] ?></td>
                                <td style="text-align: left"><?= $item->data['member_id'] ?></td>
                                <td style="text-align: left"><?= $item->data['point'] ?></td>
                                <td style="text-align: left"><?= $item->data['vendor'] ?? '---' ?></td>
                                <td style="text-align: left"><?= $item->data['vendor_point'] ?></td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </table>
            <?php endif;?>

            <?php $pager = $this->buildPager($params['page'], $params['total_rows'], $params['page_size'], 'page'); ?>
            <?php if ($pager) : ?><div class="pagination-bar p20"><?php echo $pager; ?></div><?php endif; ?>
        <?php else : ?>
            <div class="no-result">No data to display</div>
        <?php endif; ?>
    </div>
</div>

