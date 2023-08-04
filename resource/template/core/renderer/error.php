<?php
/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
$lang = OSC::core('language')->lang;

if (!$params['title']) {
    $params['title'] = $lang['core.error_title'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title><?php echo $params['title']; ?></title>        
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="icon" href="<?= OSC::helper('frontend/template')->getFavicon()->url ?>" type="image/x-icon" />
        <link rel="shortcut icon" href="<?= OSC::helper('frontend/template')->getFavicon()->url ?>" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->getFile('style/core/fontface/MyriadPro/regular.css'); ?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo $this->getFile('style/core/error.css'); ?>" />
    </head>
    <body>
        <div class="wrapper">
            <div class="content">
                <div class="title"><?php echo $params['title']; ?></div>
                <ul>
                    <?php foreach ($params['error'] as $error) : ?>
                        <li><div><?php echo $error; ?></div></li>
                    <?php endforeach; ?>
                </ul>
                <div class="actions">
                    <button onclick="window.location = '<?php echo $this->getUrl(true); ?>';">Dashboard</button>
                    <button onclick="history.go(-1);">Go Back</button>
                    <button onclick="window.location.reload(true);">Refresh</button>
                    <?php if ($params['backtrace']) : ?>
                        <button onclick="window.location.reload(true);">Debug</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($params['backtrace']) : ?>
                <?php $nodeCounter = 0; ?>
                <ul class="backtrace">
                    <li class="head">
                        <div class="functionCell">Function</div>
                        <div class="lineCell">Line</div>
                        <div class="fileCell">File</div>
                    </li>
                    <?php foreach (debug_backtrace() as $node): ?>
                        <li class="<?php echo++$nodeCounter % 2 ? 'even' : 'odd'; ?>">
                            <div class="functionCell">
                                <?php if ($node['class']) : ?><span class="class"><?php echo $node['class']; ?></span> <strong><?php echo $node['type']; ?></strong><?php endif; ?>
                                <span class="function"><?php echo $node['function']; ?>()</span>
                            </div>
                            <div class="lineCell"><span><?php echo $node['line']; ?></span></div>                    
                            <div class="fileCell" title="<?php echo $node['file']; ?>"><span><?php echo str_replace(ROOT_PATH, DS, $node['file']); ?></span></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <div class="copyright"><?php echo $lang['copyright']; ?></div>
        </div>
    </body>
</html>