<?php
$addon_report_data = $params['addon_report_data'];
$addon_version_data = $params['addon_version_data'];
$addon_report_order_data = $params['addon_report_order_data'];

?>
<div class="frm-grid--separate">
    <div class="setting-item clearfix">
        <h3 class="mb10">Report for Add-on</h3>
        <div class="setting-table">
            <table class="">
                <tbody>
                <tr>
                    <th>Version</th>
                    <th>Distributed Unique Visitor</th>
                    <th>Approached unique visitor</th>
                    <th>Approached</th>
                    <th>Total order</th>
                    <th>Total sale</th>
                    <th>Revenue</th>
                    <th>AOV</th>
                    <th>CR</th>
                </tr>
                <?php if (empty($addon_report_data)): ?>
                    <tr>
                        <td>No data</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($addon_report_data as $addon_report): ?>
                    <tr <?php if (strpos($addon_version_data[$addon_report['addon_version_id']]['title'], 'deleted')): ?>style="background: #fbefef" <?php endif; ?>>
                        <td><?= $addon_version_data[$addon_report['addon_version_id']]['title'] ?></td>
                        <td><?= $addon_report['distributed'] ?></td>
                        <td><?= $addon_report['approached_unique'] ?></td>
                        <td><?= $addon_report['approached'] ?></td>
                        <td><?= $addon_report['total_order'] ?></td>
                        <td><?= $addon_report['total_sale'] ?></td>
                        <td>$<?= OSC::helper('catalog/common')->integerToFloat($addon_report['revenue']) ?></td>
                        <td><?= round($addon_report['total_sale'] / $addon_report['total_order'], 2) ?></td>
                        <td><?= round(($addon_report['total_order'] / $addon_report['distributed']) * 100, 2) ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="w-100p"></div>

    <div class="setting-item clearfix">
        <h3 class="mb10 mt25">Report for order</h3>
        <div class="setting-table">
            <table class="">
                <tbody>
                <tr>
                    <th>Version</th>
                    <th>Distributed Unique Visitor</th>
                    <th>Approached unique visitor</th>
                    <th>Approached</th>
                    <th>Total order</th>
                    <th>Total sale</th>
                    <th>Revenue</th>
                    <th>AOV</th>
                    <th>CR</th>
                </tr>
                <?php if (empty($addon_report_data)): ?>
                    <tr>
                        <td>No data</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($addon_report_data as $addon_report): ?>
                    <?php
                    $total_order = $addon_report_order_data[$addon_report['addon_version_id']]['total_order'] ?? 0;
                    $total_sale = $addon_report_order_data[$addon_report['addon_version_id']]['total_sale'] ?? 0;
                    $revenue = $addon_report_order_data[$addon_report['addon_version_id']]['revenue'] ?? 0;
                    ?>
                    <tr <?php if (strpos($addon_version_data[$addon_report['addon_version_id']]['title'], 'deleted')): ?>style="background: #fbefef" <?php endif; ?>>
                        <td><?= $addon_version_data[$addon_report['addon_version_id']]['title'] ?></td>
                        <td><?= $addon_report['distributed'] ?></td>
                        <td><?= $addon_report['approached_unique'] ?></td>
                        <td><?= $addon_report['approached'] ?></td>
                        <td><?= $total_order ?></td>
                        <td><?= $total_sale ?></td>
                        <td>$<?= OSC::helper('catalog/common')->integerToFloat($revenue) ?></td>
                        <td><?= round($total_sale / $total_order, 2) ?></td>
                        <td><?= round(($total_order / $addon_report['distributed']) * 100, 2) ?>%
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="wrap-loader" style="display: none">
    <div class="loader">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
<div class="action-bar">
    <a href="<?= $this->getUrl('addon/backend_service/index') ?>" class="btn btn-outline mr5">
        Go Back
    </a>
</div>