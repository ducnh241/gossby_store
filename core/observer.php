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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC_Framework::Observer
 *
 * @package OSC_Observer
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Observer extends OSC_Object {

    protected $_lock_shutdown = false;

    public function shutdown() {
        if ($this->_lock_shutdown) {
            return false;
        }

        $this->_lock_shutdown = true;

        $this->dispatchEvent('shutdown');
    }

    /**
     * 
     * @param string $event
     * @param mixed $object
     * @param int $priority
     * @param string $key
     * @param mixed $call_params
     * @param boolean $once
     */
    public static function registerObserver($event, $object, $priority = null, $key = null, $call_params = null, $once = false) {
        $observer_config = OSC::systemRegistry('observer');

        if (!is_array($observer_config)) {
            $observer_config = array();
        }

        $observer_config[] = array(
            'event' => $event,
            'object' => $object,
            'priority' => $priority,
            'key' => $key,
            'call_params' => $call_params,
            'once' => $once
        );

        OSC::systemRegister('observer', $observer_config);
    }

}
