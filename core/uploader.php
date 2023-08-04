<?php

class OSC_Uploader {

    protected $_handler = null;
    protected $_extensions = array();
    protected $_size_limit = null;

    public function __construct() {
        $request = OSC::core('request');

        if ($request->headerIsExists('X-File-Name')) {
            $this->_handler = new OSC_Uploader_Handler_Xhr();
        } elseif (isset($_FILES['file'])) {
            $this->_handler = new OSC_Uploader_Handler_Form();
        } else {
            throw new Exception('No file uploaded');
        }
    }

    public function setExtension() {
        $arg_counter = func_num_args();

        if ($arg_counter == 1 && func_get_arg(0) == null) {
            $this->_extensions = array();
            return $this;
        }

        foreach (func_get_args() as $ext) {
            $this->_extensions[] = strtolower($ext);
        }

        return $this;
    }

    public function setSizeLimit($size = null) {
        $size = intval($size);

        $this->_size_limit = $size > 0 ? $size : null;

        return $this;
    }

    public function getSize($format = false) {
        $size = $this->_handler->getSize();

        if ($format) {
            $size = OSC::formatSize($size);
        }

        return $size;
    }

    public function getName() {
        return $this->_handler->getName();
    }

    public function getExtension() {
        $pathinfo = pathinfo($this->getName());
        return strtolower($pathinfo['extension']);
    }

    public function save($path, $overwrite = false) {
        $dir_name = dirname($path);

        if (!is_writable($dir_name)) {
            throw new Exception("The directory '{$dir_name}' is not writable");
        }

        if (file_exists($path) && !$overwrite) {
            throw new Exception('The file "' . $path . '" is always exists');
        }

        $size = $this->_handler->getSize();

        if ($size == 0) {
            throw new Exception('The file is empty');
        }

        if ($this->_size_limit > 0 && $size > $this->_size_limit) {
            throw new Exception('The file is too large');
        }

        $extension = $this->getExtension();

        if (count($this->_extensions) > 0 && !in_array(strtolower($extension), $this->_extensions)) {
            throw new Exception('The file extension should be one of ' . implode(', ', $this->_extensions));
        }

        try {
            $this->_handler->save($path);
        } catch (Exception $e) {
            throw $e;
        }

        if (in_array($extension, array('gif', 'png', 'jpg', 'jpeg'))) {
            $exif = exif_read_data($path);

            if (isset($exif['Orientation']) && in_array($exif['Orientation'], array(3, 6, 8))) {
                $image = imagecreatefromstring(file_get_contents($path));

                switch ($exif['Orientation']) {
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;
                }

                if ($image !== false) {
                    if ($extension == 'gif') {
                        imagegif($image, $path);
                    } else if ($extension == 'png') {
                        imagepng($image, $path, 0);
                    } else {
                        imagejpeg($image, $path, 100);
                    }
                }
            }
        }

        return $this;
    }

}

?>
