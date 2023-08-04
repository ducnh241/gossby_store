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
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package Core_Environment
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Environment {

    /**
     * Client IP address
     *
     * @var String
     */
    public $client_ip = null;

    /**
     * Client operating system information
     *
     * @var String
     */
    public $os = null;

    /**
     * Client User Agent
     *
     * @var String
     */
    public $user_agent = null;

    /**
     * Client browser information
     *
     * @var Array
     */
    public $browser = array();

    /**
     *
     * @var array
     */
    public $variables = array();
    
    protected static $_instance = null;

    public static function getInstance() {
        if (static::$_instance === null) {
            $class = get_class();
            static::$_instance = new $class();
        }

        return static::$_instance;
    }

    public function __construct() {
        //-----------------------------------------
        // Get the client's IP address
        //-----------------------------------------

        $this->client_ip = $this->_detectClientIP();

        if ((!$this->client_ip || $this->client_ip == '...' ) && !isset($_SERVER['SHELL'])) {
            //OSC::core('output')->error("Could not determine your IP address");
        }

        //-----------------------------------------
        // Get the client's user-agent, browser and OS
        //-----------------------------------------

        $this->user_agent = $this->getEnv('HTTP_USER_AGENT');
        $this->browser = $this->_detectClientBrowser();
        $this->os = $this->_detectClientOS();
    }

    /**
     * Detect IP of client OR exit program
     *
     * @return void
     */
    protected function _detectClientIP() {
        $addrs = array();

        foreach (array_reverse(explode(',', $this->getEnv('HTTP_X_FORWARDED_FOR'))) as $x_f) {
            $x_f = trim($x_f);

            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $x_f)) {
                $addrs[] = $x_f;
            }
        }

        $addrs[] = $this->getEnv('HTTP_CLIENT_IP');
        $addrs[] = $this->getEnv('HTTP_X_CLUSTER_CLIENT_IP');
        $addrs[] = $this->getEnv('HTTP_PROXY_USER');
        $addrs[] = $this->getEnv('REMOTE_ADDR');

        $_ip = '';

        foreach ($addrs as $ip) {
            if ($ip) {
                preg_match("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/", $ip, $match);

                $_ip = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];

                if ($_ip && $_ip != '...') {
                    break;
                }
            }
        }

        return $_ip;
    }

    /**
     *
     * @param string $ip 
     * @return mixed
     */
    public function cleanIpAddress($ip) {
        $arr = explode('.', $ip);

        $ip = array();

        if (count($arr) != 4) {
            $ip = false;
        } else {
            foreach ($arr as $segment) {
                $segment = intval($segment);

                if ($segment > 0) {
                    $ip[] = $segment;
                }
            }

            if (count($ip) != 4) {
                $ip = false;
            } else {
                $ip = implode('.', $ip);
            }
        }

        return $ip;
    }

    /**
     * Detect the client's operating system
     *
     * @return string Operate System name
     */
    protected function _detectClientOS() {
        $useragent = strtolower($this->getEnv('HTTP_USER_AGENT'));

        if (strstr($useragent, 'mac')) {
            return 'mac';
        }

        if (preg_match('/wi(n|n32|ndows)/', $useragent)) {
            return 'windows';
        }

        return 'unknown';
    }

    /**
     * Detect the client's browser from their user-agent
     *
     * @return Array Browser name and version
     */
    protected function _detectClientBrowser() {
        $userAgent = strtolower($this->getEnv('HTTP_USER_AGENT'));

        $BROWSERS = array(array('title' => "Amaya",
                'type' => 'desktop',
                'regex' => array("amaya/([0-9.]{1,10})" => "1")),
            array('title' => "AOL",
                'type' => 'desktop',
                'regex' => array("aol[ /\-]([0-9.]{1,10})" => "1")),
            array('title' => "Blackberry",
                'type' => 'mobile',
                'regex' => array("blackberry(\d+?)/([0-9.]{1,10})" => "2")),
            array('title' => "Camino",
                'type' => 'desktop',
                'regex' => array("camino/([0-9.+]{1,10})" => "1")),
            array('title' => "Chimera",
                'type' => 'desktop',
                'regex' => array("chimera/([0-9.+]{1,10})" => "1")),
            array('title' => "Chrome",
                'type' => 'desktop',
                'regex' => array("Chrome/([0-9.]{1,10})" => "1")),
            array('title' => "Curl",
                'regex' => array("curl[ /]([0-9.]{1,10})" => "1")),
            array('title' => "Firebird",
                'type' => 'desktop',
                'regex' => array("Firebird/([0-9.+]{1,10})" => "1")),
            array('title' => "Firefox",
                'type' => 'desktop',
                'regex' => array("Firefox/([0-9.+]{1,10})" => "1")),
            array('title' => "Lotus Notes",
                'type' => 'desktop',
                'regex' => array("Lotus[ \-]?Notes[ /]([0-9.]{1,10})" => "1")),
            array('title' => "Konqueror",
                'type' => 'desktop',
                'regex' => array("konqueror/([0-9.]{1,10})" => "1")),
            array('title' => "Lynx",
                'type' => 'desktop',
                'regex' => array("lynx/([0-9a-z.]{1,10})" => "1")),
            array('title' => "Maxthon",
                'type' => 'desktop',
                'regex' => array(" Maxthon[\);]" => "")),
            array('title' => "OmniWeb",
                'type' => 'desktop',
                'regex' => array("omniweb/[ a-z]?([0-9.]{1,10})$" => "1")),
            array('title' => "Opera",
                'type' => 'desktop',
                'regex' => array("opera[ /]([0-9.]{1,10})" => "1")),
            array('title' => "Safari",
                'type' => 'desktop',
                'regex' => array("version/([0-9.]{1,10})\s+?safari/([0-9.]{1,10})" => "1")),
            array('title' => "iPhone",
                'type' => 'mobile',
                'regex' => array("iphone;" => "0")),
            array('title' => "iPod Touch",
                'type' => 'mobile',
                'regex' => array("ipod;" => "0")),
            array('title' => "Webtv",
                'type' => 'desktop',
                'regex' => array("webtv[ /]([0-9.]{1,10})" => "1")),
            array('title' => "Explorer",
                'type' => 'desktop',
                'regex' => array("\(compatible; MSIE[ /]([0-9.]{1,10})" => "1")),
            array('title' => "Netscape",
                'type' => 'desktop',
                'regex' => array("^mozilla/([0-4]\.[0-9.]{1,10})" => "1")),
            array('title' => "Mozilla",
                'type' => 'desktop',
                'regex' => array("^mozilla/([5-9]\.[0-9a-z.]{1,10})" => "1")),
            array('title' => "Gecko",
                'type' => 'desktop',
                'regex' => array("gecko/(\d+)" => "1")),
            array('title' => "About",
                'type' => 'search',
                'regex' => array("Libby[_/ ]([0-9.]{1,10})" => "1")),
            array('title' => "Alexa",
                'type' => 'search',
                'regex' => array("^ia_archive" => "0")),
            array('title' => "Altavista",
                'type' => 'search',
                'regex' => array("Scooter[ /\-]*[a-z]*([0-9.]{1,10})" => "1")),
            array('title' => "Ask Jeeves",
                'type' => 'search',
                'regex' => array("Ask[ \-]?Jeeves" => "0")),
            array('title' => "Excite",
                'type' => 'search',
                'regex' => array("Architext[ \-]?Spider" => "0")),
            array('title' => "Google",
                'type' => 'search',
                'regex' => array("Googl(e|ebot)(-Image)?/([0-9.]{1,10})" => "\\\\3", "Googl(e|ebot)(-Image)?/" => "")),
            array('title' => "Infoseek",
                'type' => 'search',
                'regex' => array("SideWinder[ /]?([0-9a-z.]{1,10})" => "1", "Infoseek" => "")),
            array('title' => "Inktomi",
                'type' => 'search',
                'regex' => array("slurp@inktomi\.com" => "")),
            array('title' => "InternetSeer",
                'type' => 'search',
                'regex' => array("^InternetSeer\.com" => "")),
            array('title' => "Look",
                'type' => 'search',
                'regex' => array("www\.look\.com" => "")),
            array('title' => "Looksmart",
                'type' => 'search',
                'regex' => array("looksmart-sv-fw" => "")),
            array('title' => "Lycos",
                'type' => 'search',
                'regex' => array("Lycos_Spider_" => "")),
            array('title' => "MSProxy",
                'type' => 'search',
                'regex' => array("MSProxy[ /]([0-9.]{1,10})" => "1")),
            array('title' => "MSN/Bing",
                'type' => 'search',
                'regex' => array("msnbot[ /]([0-9.]{1,10})" => "1")),
            array('title' => "WebCrawl",
                'type' => 'search',
                'regex' => array("webcrawl\.net" => "")),
            array('title' => "Websense",
                'type' => 'search',
                'regex' => array("(Sqworm|websense|Konqueror/3\.(0|1)(\-rc[1-6])?; i686 Linux; 2002[0-9]{4})" => "")),
            array('title' => "Yahoo",
                'type' => 'search',
                'regex' => array("Yahoo(.*?)(Slurp|FeedSeeker)" => "")));

        $browser = array(
            'browser' => 'unknow',
            'version' => '0',
            'type' => 'desktop'
        );

        foreach ($BROWSERS as $arr) {
            foreach ($arr['regex'] as $regex => $verIdx) {
                if (preg_match("#{$regex}#i", $userAgent, $matches)) {
                    $browser['browser'] = $arr['title'];
                    $browser['type'] = $arr['type'];

                    if ($verIdx) {
                        $browser['version'] = $matches[$verIdx];
                    }

                    break;
                }
            }
        }

        return $browser;
    }

    /**
     * Get environment variable
     *
     * @param  String $key Name of environment variable
     * @return Mixed Value of environment variable
     */
    public function getEnv($key) {
        $return = array();

        if (is_array($_SERVER) && count($_SERVER)) {
            if (isset($_SERVER[$key])) {
                $return = $_SERVER[$key];
            }
        }

        if (!$return) {
            $return = getenv($key);
        }

        return $return;
    }

}