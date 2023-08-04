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
 * @package Core_Storage
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Storage extends OSC_Object {

    public static function storageSendDirectory($dir_path, $storage_path, $overwrite = true, $recursive = false) {
        if (!file_exists($dir_path)) {
            throw new Exception('Storage: Directory [' . $dir_path . '] is not exists');
        }

        $response = array();

        if (!file_exists($dir_path)) {
            throw new Exception($dir_path . ' is not exists');
        }

        $dp = opendir($dir_path);

        while (($f = readdir($dp)) !== false) {
            if ($f == '.' || $f == '..' || $f == '.svn' || $f == 'Thumbs.db') {
                continue;
            }

            if (is_file($dir_path . DS . $f)) {
                try {
                    try {
                        static::storageSendFile($dir_path . DS . $f, $storage_path . '/' . $f, $overwrite);
                    } catch (Exception $ex) {
                        
                    }

                    $response[$dir_path . DS . $f] = true;
                } catch (OSC_Exception $e) {
                    $response[$dir_path . DS . $f] = $e->getMessage();
                }
            } else if (is_dir($dir_path . DS . $f)) {
                if ($recursive) {
                    try {
                        $response = array_merge($response, static::storageSendDirectory($dir_path . DS . $f, $storage_path . '/' . $f, $overwrite, $recursive));
                    } catch (Exception $ex) {
                        
                    }
                }
            }
        }

        closedir($dp);

        return $response;
    }

    public function sendDirectory($dir_path, $storage_path, $overwrite = true, $recursive = false) {
        return static::storageSendDirectory($dir_path, $storage_path, $overwrite, $recursive);
    }

    public static function storageSendFile($source, $destination, $overwrite = true) {
        if (!file_exists($source)) {
            throw new Exception('Storage: File [' . $source . '] is not exists');
        }

        $filepath = static::getStoragePath($destination);

        OSC::makeDir(dirname($filepath));

        if (!copy($source, $filepath)) {
            throw new Exception('Cannot save file to storage');
        }

        @chown($filepath, OSC_FS_USERNAME);
        @chgrp($filepath, OSC_FS_USERNAME);

        return static::getStorageUrl($destination);
    }

    /**
     * 
     * @param type $source
     * @param type $destination
     * @param type $overwrite
     */
    public function sendFile($source, $destination, $overwrite = true) {
        static::storageSendFile($source, $destination, $overwrite);
    }

    public function delete($filename) {
        $path = static::getStoragePath($filename);

        if (is_file($path)) {
            return @unlink($path) ? 'OK' : false;
        }

        return OSC::removeDir($path) ? 'OK' : false;
    }

    public static function getStoragePath($filename) {
        return OSC_STORAGE_PATH . '/' . $filename;
    }

    public static function getStorageRootPath() {
        return OSC_STORAGE_PATH;
    }

    public static function getStorageRootUrl() {
        return str_replace(OSC_SITE_PATH, OSC::$base_url, OSC_STORAGE_PATH);
    }

    public static function getStorageUrl($filename) {
        return $filename ? static::getStorageRootUrl() . '/' . $filename : '';
    }

    public static function getFilepathFromUrl($url) {
        $url = preg_replace('/^https(:.+)$/i', 'http\\1', $url);
        $storage_root_url = preg_replace('/^https(:.+)$/i', 'http\\1', static::getStorageRootUrl());

        if (strpos($url, $storage_root_url) !== 0) {
            return '';
        }

        return str_replace($storage_root_url, OSC_STORAGE_PATH, $url);
    }

    function getUrl($filename) {
        return static::getStorageUrl($filename);
    }

    public static function isUrlExists($url) {
        return static::isExists(static::getFilepathFromUrl($url));
    }

    public static function isExists($filename) {
        return file_exists(static::getStoragePath($filename));
    }

    function exists($path) {
        return static::isExists($path);
    }

    public static function tmpGetDirPath($absolute_flag = false) {
        $tmp_path = 'tmp/' . mktime(0, 0, 1);

        if (!OSC::makeDir(OSC_VAR_PATH . '/' . $tmp_path, 0755, true)) {
            throw new Exception("Cannot make storage temporary directory");
        }

        return ($absolute_flag ? '/var' : OSC_VAR_PATH) . '/' . $tmp_path;
    }

    public static function tmpGetFilePath($file_name, $absolute_flag = false, $re_check = false) {
        $tmp_file_path = mktime(0, 0, 1);

        if ($re_check) {
            $tmp_file_path -= 60 * 60 * 24;
        }

        $tmp_file_path = 'tmp/' . $tmp_file_path . '/' . $file_name;


        if (!file_exists(OSC_VAR_PATH . '/' . $tmp_file_path)) {
            if (!$re_check) {
                return static::tmpGetFilePath($file_name, $absolute_flag, true);
            }

            return false;
        }

        return ($absolute_flag ? '/var' : OSC_VAR_PATH) . '/' . $tmp_file_path;
    }

    public static function tmpGetFileUrl($file_name) {
        $file_path = static::tmpGetFilePath($file_name, true);

        if (!$file_path) {
            return false;
        }

        return OSC::$base_url . $file_path;
    }

    public static function tmpIsUrl($url) {
        return static::tmpGetFileNameFromUrl($url) !== '';
    }

    public static function tmpGetFileNameFromUrl($url) {
        $url = preg_replace('/^https(:.+)$/i', 'http\\1', $url);

        $url = str_replace(preg_replace('/^https(:.+)$/i', 'http\\1', OSC::$base_url), '', $url);

        if (!preg_match('/^\/+var\/+tmp\/+[0-9]+\/+([^\/]+.*)$/i', $url, $matches)) {
            return '';
        }

        return $matches[1];
    }

    public static function tmpGetFileNameFromPath($path) {
        $path = str_replace(OSC_SITE_PATH, '', $path);

        if (!preg_match('/^\/+var\/+tmp\/+[0-9]+\/+([^\/]+.*)$/i', $path, $matches)) {
            return '';
        }

        return $matches[1];
    }

    public static function tmpGetFilePathFromUrl($url) {
        return static::tmpGetFilePath(static::tmpGetFileNameFromUrl($url));
    }

    public static function tmpUrlIsExists($url) {
        return static::tmpFileExists(static::tmpGetFileNameFromUrl($url));
    }

    public static function tmpFileExists($file_name) {
        return static::tmpGetFilePath($file_name) !== false;
    }

    public static function tmpMoveFile($file_name, $storage_file_path, $overwrite = true) {
        return static::storageSendFile(static::tmpGetFilePath($file_name), $storage_file_path, $overwrite);
    }

    public static function preDirForSaveFile($file_name) {
        $dest_path = OSC_Storage::tmpGetDirPath() . '/' . $file_name;

        OSC::makeDir(dirname($dest_path));

        return $dest_path;
    }

    public static function tmpSaveFile($file, $file_name) {
        $dest_path = static::preDirForSaveFile($file_name);

        try {
            if (is_string($file)) {
                if (OSC::isUrl($file)) {
                    $response = OSC::core('network')->curl($file, ['timeout' => 1800]);

                    if ($response['content'] === false || $response['content'] === null) {
                        throw new Exception('Khong the get file content tu URL: ' . $file);
                    }

                    if (OSC::writeToFile($dest_path, $response['content'], array('chmod' => 0644)) === false) {
                        throw new Exception('Khong the save file toi temporary folder');
                    }
                } else if (OSC::isFilePath($file)) {
                    if (copy($file, $dest_path) === false) {
                        throw new Exception('Khong the save file toi temporary folder');
                    }
                } else {
                    if (OSC::writeToFile($dest_path, $file, array('chmod' => 0644)) === false) {
                        throw new Exception('Khong the save file toi temporary folder');
                    }
                }
            } else if ($file instanceof OSC_Image) {
                $file->save($dest_path);
            } else if ($file instanceof OSC_Uploader) {
                $file->save($dest_path, true);
            } else {
                rename($file, $dest_path);
            }

            @chown($dest_path, OSC_FS_USERNAME);
            @chgrp($dest_path, OSC_FS_USERNAME);
            chmod($dest_path, 0644);

            return $dest_path;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage() . ' :: ' . strval($file));
        }
    }

}
