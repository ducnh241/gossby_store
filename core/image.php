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
/**
 * OSECORE Core
 *
 * @package OSECORE_Core_Image_Processor
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
use GifCreator\GifCreator;
use GifFrameExtractor\GifFrameExtractor;

class OSC_Image {

    const FOCUS_LEFT = 'left';
    const FOCUS_RIGHT = 'right';
    const FOCUS_TOP = 'top';
    const FOCUS_BOTTOM = 'bottom';
    const FOCUS_MIDDLE = 'middle';

    protected $_jpg_quality = 100;
    protected $_image_path = null;
    protected $_image_info = array();
    protected $_image_src = null;
    protected $_animated_frames = array();
    protected $_animated_config = array();
    protected $_bg_color = array();
    protected $_imagick_flag = true;

    /**
     * 
     * @param boolean $flag
     * @return \OSC_Image
     */
    public function setImagickFlag($flag) {
        $this->_imagick_flag = $flag ? true : false;
        return $this;
    }

    public function getWidth() {
        return $this->_image_info->width;
    }

    public function getHeight() {
        return $this->_image_info->height;
    }

    /**
     *
     * @param string $imagePath
     * @return OSC_Image
     */
    public function setImage($imagePath, $anim_process_skip_flag = false) {
        $this->_image_info = $this->getImageInfo($imagePath, $anim_process_skip_flag);
        $this->_image_path = $imagePath;

        $this->_createImage();

        return $this;
    }

    /**
     *
     * @param integer $quality
     * @return OSC_Image
     */
    public function setJpgQuality($quality) {
        $quality = intval($quality);

        if ($quality > 100) {
            $quality = 100;
        } else if ($quality < 1) {
            $quality = 1;
        }

        $this->_jpg_quality = $quality;

        return $this;
    }

    /**
     *
     * @return OSC_Image
     */
    protected function _createImage() {
        $this->_image_src = null;
        $this->_animated_frames = array();
        $this->_animated_config = array(
            'delays' => array(),
            'loop' => 0,
            'disposal' => 2,
            'red' => 0,
            'green' => 0,
            'blue' => 0
        );

        if ($this->_image_info->is_animated) {
            if (class_exists('Imagick') && $this->_imagick_flag) {
                $imagick = new Imagick($this->_image_info->file_path);

                $frames = $imagick->coalesceImages();

                foreach ($frames as $frame) {
                    $i = $frame->getIteratorIndex();

                    $this->_animated_frames[$i] = imagecreatefromstring($frame->getImageBlob());
                    $this->_animated_config['delays'][$i] = $frame->getImageDelay();
                }
            } else {
                $gif_extractor = new GifFrameExtractor();
                $gif_extractor->extract($this->_image_info->file_path, false);

                foreach ($gif_extractor->getFrames() as $i => $frame) {
                    $this->_animated_frames[$i] = $frame['image'];
                    $this->_animated_config['delays'][$i] = $frame['duration'];
                }
            }
        } else {
            $this->_image_src = @call_user_func($this->_image_info->image_function->create_image, $this->_image_path);

            imageAlphaBlending($this->_image_src, true);
            imageSaveAlpha($this->_image_src, true);
        }

        if ($this->_image_src === false) {
            $error = error_get_last();
            throw new Exception('Cannot create image to process: ' . $error['message']);
        }

        return $this;
    }

    protected function _getMap() {
        $map = array();

        if (!$this->_image_info->is_animated) {
            $map[] = &$this->_image_src;
        } else {
            foreach ($this->_animated_frames as $i => $frame) {
                $map[] = &$this->_animated_frames[$i];
            }
        }

        return $map;
    }

    /**
     *
     * @param integer $width
     * @param integer $height
     * @param string $focusX
     * @param string $focusY
     * @return OSC_Image
     */
    public function trimAndResize($width, $height, $focusX = null, $focusY = null) {
        $new_width = $width;
        $new_height = $height;

        if ($width > $height) {
            $new_height = ceil($new_height / ($new_width / $this->_image_info->width));
            $new_width = $this->_image_info->width;

            if ($new_height > $this->_image_info->height) {
                $new_width = ceil($new_width / ($new_height / $this->_image_info->height));
                $new_height = $this->_image_info->height;
            }
        } else {
            $new_width = ceil($new_width / ($new_height / $this->_image_info->height));
            $new_height = $this->_image_info->height;

            if ($new_width > $this->_image_info->width) {
                $new_height = ceil($new_height / ($new_width / $this->_image_info->width));
                $new_width = $this->_image_info->width;
            }
        }

        $sx = 0;
        $sy = 0;

        if (!$focusX || $focusX == static::FOCUS_MIDDLE) {
            $sx = ceil(($this->_image_info->width - $new_width) / 2);
        } else if ($focusX == static::FOCUS_RIGHT) {
            $sx = $this->_image_info->width - $new_width;
        }

        if (!$focusY || $focusY == static::FOCUS_MIDDLE) {
            $sy = ceil(($this->_image_info->height - $new_height) / 2);
        } else if ($focusY == static::FOCUS_BOTTOM) {
            $sy = $this->_image_info->height - $new_height;
        }

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            $new_image_src = imageCreateTrueColor($width, $height);

            imagealphablending($new_image_src, false);
            imagesavealpha($new_image_src, true);
            $transparent = imagecolorallocatealpha($new_image_src, 255, 255, 255, 127);
            imagefilledrectangle($new_image_src, 0, 0, $new_width, $new_height, $transparent);

            imageCopyResampled($new_image_src, $map[$i], 0, 0, $sx, $sy, $width, $height, $new_width, $new_height);

            $map[$i] = $new_image_src;
        }

        $this->_image_info->width = $width;
        $this->_image_info->height = $height;

        return $this;
    }

    /**
     *
     * @param integer $new_width
     * @param integer $new_height
     * @return OSC_Image
     */
    public function hardResize($new_width = 0, $new_height = 0) {
        $new_width = intval($new_width);
        $new_height = intval($new_height);

        if ($new_width < 1 && $new_height < 1) {
            return $this;
        }

        if ($new_width < 1) {
            $new_width = $this->_image_info->width * ( $new_height / $this->_image_info->height);
        } else if ($new_height < 1) {
            $new_height = $this->_image_info->height * ( $new_width / $this->_image_info->width);
        }

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            $new_image_src = imageCreateTrueColor($new_width, $new_height);

            imageCopyResampled($new_image_src, $map[$i], 0, 0, 0, 0, $new_width, $new_height, $this->_image_info->width, $this->_image_info->height);

            $map[$i] = $new_image_src;
        }

        $this->_image_info->width = $new_width;
        $this->_image_info->height = $new_height;

        return $this;
    }

    /**
     *
     * @param integer $new_width
     * @param integer $new_height
     * @param string $bgcolor
     * @return OSC_Image
     */
    public function resizeAndFill($new_width, $new_height, $bgcolor = 'ffffff') {
        $bgcolor = preg_replace('/[a-f0-9]/', '', strtolower($bgcolor));

        if (!$bgcolor) {
            $bgcolor = 'ffffff';
        } else if (strlen($bgcolor) != 6) {
            while (strlen($bgcolor) < 6) {
                $bgcolor .= 'f';
            }
        }

        $new_width = intval($new_width);
        $new_height = intval($new_height);

        if ($new_width < 1 && $new_height < 1) {
            return $this;
        }

        $this->resize($new_width, $new_height);

        if ($new_width < 1 || $new_height < 1) {
            return $this;
        }

        $bgcolor = $this->_getColor($bgcolor);

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            $new_image_src = imageCreateTrueColor($new_width, $new_height);
            @ImageFill($new_image_src, 0, 0, @ImageColorAllocate($new_image_src, $bgcolor[0], $bgcolor[1], $bgcolor[2]));

            imageCopyResampled($new_image_src, $map[$i], ceil(($new_width - $this->_image_info->width) / 2), ceil(($new_height - $this->_image_info->height) / 2), 0, 0, $this->_image_info->width, $this->_image_info->height, $this->_image_info->width, $this->_image_info->height);

            $map[$i] = $new_image_src;
        }

        $this->_image_info->width = $new_width;
        $this->_image_info->height = $new_height;

        return $this;
    }

    /**
     *
     * @param integer $new_width
     * @param integer $new_height
     * @return OSC_Image
     */
    public function resize($new_width = 0, $new_height = 0) {
        $new_width = intval($new_width);
        $new_height = intval($new_height);

        if ($new_width < 1 && $new_height < 1) {
            return $this;
        }

        $raw_new_width = $new_width;
        $raw_new_height = $new_height;

        $new_width = $this->_image_info->width;
        $new_height = $this->_image_info->height;

        if ($raw_new_width > 0 && $raw_new_width < $new_width) {
            $new_height = ( $new_height / ( $new_width / $raw_new_width ) );
            $new_width = $raw_new_width;
        }

        if ($raw_new_height > 0 && $raw_new_height < $new_height) {
            $new_width = ( $new_width / ( $new_height / $raw_new_height ) );
            $new_height = $raw_new_height;
        }

        if ($new_width == $this->_image_info->width && $new_height == $this->_image_info->height) {
            return $this;
        }

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            $new_image_src = imageCreateTrueColor($new_width, $new_height);

            imagealphablending($new_image_src, false);
            imagesavealpha($new_image_src, true);
            $transparent = imagecolorallocatealpha($new_image_src, 255, 255, 255, 127);
            imagefilledrectangle($new_image_src, 0, 0, $new_width, $new_height, $transparent);

            imageCopyResampled($new_image_src, $map[$i], 0, 0, 0, 0, $new_width, $new_height, $this->_image_info->width, $this->_image_info->height);

            $map[$i] = $new_image_src;
        }

        $this->_image_info->width = $new_width;
        $this->_image_info->height = $new_height;

        return $this;
    }

    /**
     *
     * @param float $percent
     * @return OSC_Image
     */
    public function percentResize($percent) {
        $percent = intval($percent);

        if ($percent <= 0 || $percent == 100) {
            return $this;
        }

        $percent = round($percent / 100, 2);

        $new_width = ceil($this->_image_info->width * $percent);
        $new_height = ceil($this->_image_info->height * $percent);

        if ($new_width == $this->_image_info->width && $new_height == $this->_image_info->height) {
            return $this;
        }

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            $new_image_src = imageCreateTrueColor($new_width, $new_height);

            imageCopyResampled($new_image_src, $map[$i], 0, 0, 0, 0, $new_width, $new_height, $this->_image_info->width, $this->_image_info->height);

            $map[$i] = $new_image_src;
        }

        $this->_image_info->width = $new_width;
        $this->_image_info->height = $new_height;

        return $this;
    }

    /**
     *
     * @param integer $x1
     * @param integer $y1
     * @param integer $x2
     * @param integer $y2
     * @return OSC_Image
     */
    public function crop($x1, $y1, $x2, $y2) {
        $x1 = ceil($x1);
        $y1 = ceil($y1);
        $x2 = ceil($x2);
        $y2 = ceil($y2);

        $width = $x2 - $x1;
        $height = $y2 - $y1;

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            $new_image_src = imageCreateTrueColor($width, $height);

            imageCopyResampled($new_image_src, $map[$i], 0, 0, $x1, $y1, $width, $height, $width, $height);

            $map[$i] = $new_image_src;
        }

        $this->_image_info->width = $width;
        $this->_image_info->height = $height;

        return $this;
    }

    /**
     *
     * @return OSC_Image 
     */
    public function toBlackWhite() {
        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            for ($c = 0; $c < 256; $c++) {
                imageColorAllocate($map[$i], $c, $c, $c);
            }
        }

        return $this;
    }

    public function calDimension($width, $height, $mWidth = 0, $mHeight = 0) {
        $mWidth = intval($mWidth);
        $mHeight = intval($mHeight);

        if ($mWidth > 0) {
            if ($mWidth < $width) {
                $height = ( $height / ( $width / $mWidth ) );
                $width = $mWidth;
            }
        }

        if ($mHeight > 0) {
            if ($mHeight < $height) {
                $width = ( $width / ( $height / $mHeight ) );
                $height = $mHeight;
            }
        }

        return array('h' => intval($height),
            'w' => intval($width));
    }

    /**
     *
     * @param string $watermask_path
     * @param array  $opts
     * @return OSC_Image
     */
    public function addWatermask($watermask_path, $opts = array()) {
        if (!file_exists($watermask_path)) {
            return $this;
        }

        if (!isset($opts)) {
            $opts = array();
        }

        if (isset($opts['ratio'])) {
            if (!is_array($opts['ratio'])) {
                
            } else {
                if (isset($opts['ratio']['w'])) {
                    $opts['ratio']['w'] = (float) $opts['ratio']['w'];

                    if ($opts['ratio']['w'] > 0) {
                        $max_w = $this->_image_info->width * $opts['ratio']['w'];
                    }
                }

                if (isset($opts['ratio']['h'])) {
                    $opts['ratio']['h'] = (float) $opts['ratio']['h'];

                    if ($opts['ratio']['h'] > 0) {
                        $max_h = $this->_image_info->height * $opts['ratio']['h'];
                    }
                }
            }
        }

        $watermask_info = $this->getImageInfo($watermask_path);
        $watermark_src = call_user_func($watermask_info->image_function->create_image, $watermask_path);

        if (isset($max_w) || isset($max_h)) {
            $watermask_dim = $this->calDimension($watermask_info->width, $watermask_info->height, $max_w, $max_h);

            if ($watermask_info->width > $watermask_dim['w']) {
                $new_watermark_src = imageCreateTrueColor($watermask_dim['w'], $watermask_dim['h']);

                imagecolortransparent($new_watermark_src, imagecolorallocatealpha($new_watermark_src, 0, 0, 0, 127));
                imagealphablending($new_watermark_src, false);
                imagesavealpha($new_watermark_src, true);

                imageCopyResampled($new_watermark_src, $watermark_src, 0, 0, 0, 0, $watermask_dim['w'], $watermask_dim['h'], $watermask_info->width, $watermask_info->height);

                $watermark_src = $new_watermark_src;

                $watermask_info->width = $watermask_dim['w'];
                $watermask_info->height = $watermask_dim['h'];
            }
        }

        $x = $this->_image_info->width - $watermask_info->width;
        $y = $this->_image_info->height - $watermask_info->height;

        $map = $this->_getMap();

        for ($i = 0; $i < count($map); $i ++) {
            imagecopy($map[$i], $watermark_src, $x, $y, 0, 0, $watermask_info->width, $watermask_info->height);
        }

        if (isset($opts['pointer'])) {
            $opts['pointer'] = array('dim' => array('w' => $watermask_info->width, 'h' => $watermask_info->height));
        }

        return $this;
    }

    /**
     *
     * @param string $path
     * @param integer $width
     * @param integer $height
     * @return OSC_Image
     */
    public function makeThumbnail($path, $width = 0, $height = 0) {
        $this->resize($width, $height)->save($path);

        return $this;
    }

    /**
     *
     * @param string $imageDestPath
     * @return OSC_Image
     */
    public function save($imageDestPath = null) {
        if (!$imageDestPath) {
            $imageDestPath = $this->_image_path;
        }

        if ($this->_image_info->is_animated) {
            OSC::writeToFile($imageDestPath, $this->_getAnimatedData(), array('chmod' => 0644));
        } else {
            if ($this->_image_info->image_function->render_image == 'imagejpeg') {
                call_user_func($this->_image_info->image_function->render_image, $this->_image_src, $imageDestPath, $this->_jpg_quality);
            } else {
                call_user_func($this->_image_info->image_function->render_image, $this->_image_src, $imageDestPath);
            }
        }

        chown($imageDestPath, OSC_FS_USERNAME);
        chgrp($imageDestPath, OSC_FS_USERNAME);

        return $this;
    }

    /**
     * 
     * @param string $file_name
     * @param int $frame_index
     * @return OSC_Image
     * @throws Exception
     */
    public function saveAnimatedFrame($file_name, $frame_index = 0) {
        if (!$this->isAnimated()) {
            throw new Exception("The image is not animated gif");
        }

        $frame_index = intval($frame_index);

        if ($frame_index < 0 || !isset($this->_animated_frames[$frame_index])) {
            throw new Exception("The frame index is not exists");
        }

        if (!imagegif($this->_animated_frames[$frame_index], $file_name)) {
            throw new Exception("Cannot save animated frame[{$frame_index}] as " . $file_name);
        }

        return $this;
    }

    /**
     *
     */
    public function render() {
        @header("Content-type: " . $this->_image_info->mime_type);

        if ($this->_image_info->is_animated) {
            echo $this->_getAnimatedData();
        } else {
            if ($this->_image_info->image_function->render_image == 'imagejpeg') {
                call_user_func($this->_image_info->image_function->render_image, $this->_image_src, null, $this->_jpg_quality);
            } else {
                call_user_func($this->_image_info->image_function->render_image, $this->_image_src);
            }
        }
    }

    protected function _getAnimatedData() {
        if (!$this->_image_info->is_animated) {
            return null;
        }

        if (class_exists('Imagick') && $this->_imagick_flag) {
            $GIF = new Imagick();
            $GIF->setFormat('gif');

            foreach ($this->_animated_frames as $i => $gd_frame) {
                ob_start();
                imagegif($gd_frame);
                $blob_data = ob_get_contents();
                ob_end_clean();


                $frame = new Imagick();
                $frame->readImageBlob($blob_data);
                $frame->setImageDelay($this->_animated_config['delays'][$i]);
                $GIF->addImage($frame);
            }

            $GIF = $GIF->deconstructimages();
            $GIF->setImageIterations(0);

            return $GIF->getImagesBlob();
        }

        $gif_creator = new GifCreator();
        return $gif_creator->create($this->_animated_frames, $this->_animated_config['delays']);
    }

    /**
     *
     * @return OSC_Image
     */
    public function destroy() {
        imageDestroy($this->_image_src);
        return $this;
    }

    public function dropShadow($url) {
        $shadows['l'] = @ImageCreateFromPNG($url . '/ds_left.png');
        $shadows['r'] = @ImageCreateFromPNG($url . '/ds_right.png');
        $shadows['t'] = @ImageCreateFromPNG($url . '/ds_top.png');
        $shadows['b'] = @ImageCreateFromPNG($url . '/ds_bottom.png');
        $shadows['tl'] = @ImageCreateFromPNG($url . '/ds_tlcorner.png');
        $shadows['tr'] = @ImageCreateFromPNG($url . '/ds_trcorner.png');
        $shadows['bl'] = @ImageCreateFromPNG($url . '/ds_blcorner.png');
        $shadows['br'] = @ImageCreateFromPNG($url . '/ds_brcorner.png');

        $ox = @ImageSX($this->IMGSrc);
        $oy = @ImageSY($this->IMGSrc);
        $nx = @ImageSX($shadows['l']) + @ImageSX($shadows['r']) + $ox;
        $ny = @ImageSY($shadows['t']) + @ImageSY($shadows['b']) + $oy;

        $imgShadow = @ImageCreateTrueColor($nx, $ny);

        @ImageAlphaBlending($imgShadow, TRUE);
        @ImageFill($imgShadow, 0, 0, @ImageColorAllocate($imgShadow, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]));

        @ImageCopyResampled($imgShadow, $shadows['tl'], 0, 0, 0, 0, @ImageSX($shadows['tl']), @ImageSY($shadows['tl']), @ImageSX($shadows['tl']), @ImageSY($shadows['tl']));

        @ImageCopyResampled($imgShadow, $shadows['t'], @ImageSX($shadows['l']), 0, 0, 0, $ox, @ImageSY($shadows['t']), @ImageSX($shadows['t']), @ImageSY($shadows['t']));

        @ImageCopyResampled($imgShadow, $shadows['tr'], ($nx - @ImageSX($shadows['r'])), 0, 0, 0, @ImageSX($shadows['tr']), @ImageSY($shadows['tr']), @ImageSX($shadows['tr']), @ImageSY($shadows['tr']));

        @ImageCopyResampled($imgShadow, $shadows['l'], 0, @ImageSY($shadows['t']), 0, 0, @ImageSX($shadows['l']), $oy, @ImageSX($shadows['l']), @ImageSY($shadows['l']));

        @ImageCopyResampled($imgShadow, $shadows['r'], ($nx - @ImageSX($shadows['r'])), @ImageSY($shadows['tl']), 0, 0, @ImageSX($shadows['r']), $oy, @ImageSX($shadows['r']), @ImageSY($shadows['r']));

        @ImageCopyResampled($imgShadow, $shadows['bl'], 0, ($ny - @ImageSY($shadows['b'])), 0, 0, @ImageSX($shadows['bl']), @ImageSY($shadows['bl']), @ImageSX($shadows['bl']), @ImageSY($shadows['bl']));

        @ImageCopyResampled($imgShadow, $shadows['b'], @ImageSX($shadows['l']), ($ny - @ImageSY($shadows['b'])), 0, 0, $ox, @ImageSY($shadows['b']), @ImageSX($shadows['b']), @ImageSY($shadows['b']));

        @ImageCopyResampled($imgShadow, $shadows['br'], ($nx - @ImageSX($shadows['r'])), ($ny - @ImageSY($shadows['b'])), 0, 0, @ImageSX($shadows['br']), @ImageSY($shadows['br']), @ImageSX($shadows['br']), @ImageSY($shadows['br']));

        @ImageCopyResampled($imgShadow, $this->IMGSrc, @ImageSX($shadows['l']), @ImageSY($shadows['t']), 0, 0, $ox, $oy, $ox, $oy);

        @imagedestroy($shadows['l']);
        @imagedestroy($shadows['r']);
        @imagedestroy($shadows['t']);
        @imagedestroy($shadows['b']);
        @imagedestroy($shadows['tl']);
        @imagedestroy($shadows['tr']);
        @imagedestroy($shadows['bl']);
        @imagedestroy($shadows['br']);

        $this->IMGSrc = $imgShadow;
    }

    protected function _getColor($color) {
        for ($i = 0; $i < 3; $i++) {
            $foo = substr($color, 2 * $i, 2);
            $background[$i] = 16 * hexdec(substr($foo, 0, 1)) + hexdec(substr($foo, 1, 1));
        }

        return $background;
    }

    public function imageShadow($startAlpha = 40) {
        $w = imageSX($this->IMGSrc);
        $h = imageSY($this->IMGSrc);

        $imgShadow = ImageCreateTrueColor($w, $h * 2);

        imageAlphaBlending($imgShadow, true);
        imageFill($imgShadow, 0, 0, imageColorAllocate($imgShadow, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]));

        imageCopyResampled($imgShadow, $this->IMGSrc, 0, 0, 0, 0, $w, $h, $w, $h);

        $i = 0;

        if ($w > $h) {
            while ($i < $h) {
                $i++;
                imageCopyResampled($imgShadow, $this->IMGSrc, 0, $h + $i - 1, 0, $h - $i, $w, $h, $w, 1);
            }
        } else {
            $this->IMGSrc = imageRotate($this->IMGSrc, 180, 0);

            while ($i < $w) {
                $i++;
                imageCopyResampled($imgShadow, $this->IMGSrc, $i - 1, $h, $w - $i, 0, $w, $h, 1, $h);
            }
        }

        $alpha = 0;
        $n = intval($h / $startAlpha);

        $alphaLayer = imageCreate($w, $h - $n * ($startAlpha + 1));

        imagecolorallocatealpha($alphaLayer, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2], $startAlpha);

        imageCopyResampled($imgShadow, $alphaLayer, 0, $h, 0, 0, $w, $h - $n * ($startAlpha + 1), $w, $h - $n * ($startAlpha + 1));

        imagedestroy($alphaLayer);

        while ($alpha <= $startAlpha) {
            $alphaLayer = imageCreate($w, $n);

            imagecolorallocatealpha($alphaLayer, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2], $alpha);

            imageCopyResampled($imgShadow, $alphaLayer, 0, $h * 2 - $n * ( $alpha + 1 ), 0, 0, $w, $n, $w, $n);

            imagedestroy($alphaLayer);

            $alpha++;
        }

        $this->IMGSrc = $imgShadow;
    }

    /**
     *
     * @param string $imagePath
     * @return stdClass
     */
    public function getImageInfo($image_path, $anim_process_skip_flag) {
        $info = @getimagesize($image_path);

        if ($info !== false) {
            $image_info = new stdClass();
            $image_info->image_function = new stdClass();
            $image_info->is_animated = false;
            $image_info->file_path = $image_path;

            switch ($info['mime']) {
                case 'image/gif':
                    $image_info->extension = 'gif';
                    $image_info->is_animated = $anim_process_skip_flag ? false : GifFrameExtractor::isAnimatedGif($image_path);
                    $image_info->image_function->render_image = 'imagegif';
                    $image_info->image_function->create_image = 'imagecreatefromgif';
                    break;
                case 'image/png':
                    $image_info->extension = 'png';
                    $image_info->image_function->render_image = 'imagepng';
                    $image_info->image_function->create_image = 'imagecreatefrompng';
                    break;
                default:
                    $image_info->extension = 'jpg';
                    $image_info->image_function->render_image = 'imagejpeg';
                    $image_info->image_function->create_image = 'imagecreatefromjpeg';
            }

            $image_info->width = $info[0];
            $image_info->height = $info[1];
            $image_info->mime_type = $info['mime'];

            return $image_info;
        } else {
            $error = error_get_last();
            throw new Exception('Cannot get image information: ' . $error['message']);
        }
    }

    public function isAnimated() {
        return $this->_image_info->is_animated;
    }

}
