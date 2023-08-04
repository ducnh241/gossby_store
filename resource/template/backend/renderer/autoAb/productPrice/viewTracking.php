<?php
/* @var $this Helper_Backend_Template */
$this->push('core/setting.scss', 'css');
$this->addComponent('daterangepicker');
$this->push(array('report/common.scss', 'backend/dashboard.scss'), 'css');
$this->push(array('report/common.js', '[core]community/chart.min.js'), 'js');

$data = $params['data'];

?>
<div class="dashboard">
    <div class="filter-menu">
        <ul class="date-range">
            <li<?= ($params['range'] == 'today') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'today']); ?>">Today</a></li>
            <li<?= ($params['range'] == 'yesterday') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'yesterday']); ?>">Yesterday</a></li>
            <li<?= ($params['range'] == 'thisweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thisweek']); ?>">This Week</a></li>
            <li<?= ($params['range'] == 'lastweek') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastweek']); ?>">Last Week</a></li>
            <li<?= ($params['range'] == 'thismonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'thismonth']); ?>">This Month</a></li>
            <li<?= ($params['range'] == 'lastmonth') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'lastmonth']); ?>">Last Month</a></li>
            <li<?= ($params['range'] == 'alltime') ? ' class="active"' : ''; ?>><a href="<?= $this->rebuildUrl(['range' => 'alltime']); ?>">All time</a></li>
            <li<?= (is_array($params['range'])) ? ' class="active"' : ''; ?>>
                <a href="javascript://" data-link="<?= $this->rebuildUrl(['range' => '']); ?>" data-insert-cb="initReportCustomDate" data-begin="<?= is_array($params['range']) ? $params['range'][0] : '' ?>" data-end="<?= is_array($params['range']) ? $params['range'][1] : '' ?>">Custom</a>
            </li>
        </ul>
    </div>
</div>
<div class="setting-config-panel post-frm">
    <div class="setting-config-group">
        <div class="block">
            <div class="p20">
                <div class="frm-grid frm-grid--separate">
                    <div class="setting-item">
                        <div class="title"><?= $params['name']; ?></div>
                        <div class="setting-table">
                            <table>
                                <tbody>
                                <tr>
                                    <th>Price Range</th>
                                    <th>Total Order</th>
                                    <th>Total sale</th>
                                    <th>Total Revenue</th>
                                    <th>Total Base Cost</th>
                                    <th>Total Quantity</th>
                                    <th>Product</th>
                                </tr>
                                <?php foreach ($data as $price_range => $value) : ?>
                                    <tr>
                                        <td><?= OSC::helper('catalog/common')->integerToFloat($price_range); ?></td>
                                        <td><?= count(array_unique($value['order_id'])); ?></td>
                                        <td><?= $value['total_sale'] ?? 0; ?></td>
                                        <td> <?= '$' . OSC::helper('catalog/common')->integerToFloat($value['revenue']); ?></td>
                                        <td> <?= '$' . OSC::helper('catalog/common')->integerToFloat($value['base_cost']); ?></td>
                                        <td> <?= $value['quantity']; ?></td>
                                        <td> <?php
                                            $total_product = '';
                                            $count_product = count($value['product_id']);
                                            foreach ($value['product_id'] as $product_id => $quantity) {
                                                $count_product -= 1;

                                                if ($count_product < 1) {
                                                    $total_product .= $product_id . '(qty: ' . $quantity . ')';
                                                } else {
                                                    $total_product .= $product_id . '(qty: ' . $quantity . ')' . ' ,';
                                                }
                                            }
                                            echo $total_product;
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5">
            Go Back
        </a>
    </div>
</div>
