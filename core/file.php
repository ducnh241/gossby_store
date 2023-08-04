<?php

class OSC_File {

    //https://en.wikipedia.org/wiki/List_of_file_signatures
    //http://filesignatures.net/index.php?page=all&currentpage=3&order=SIGNATURE&sort=DESC
    public static $_signature_data = array(
        'png' => array('89 50 4E 47'),
        'gif' => array('47 49 46 38'),
        'jpg' => array('FF D8 FF E0', 'FF D8 FF E1', 'FF D8 FF E2', 'FF D8 FF E3', 'FF D8 FF DB', 'FF D8 FF EE', 'FF D8 FF ED'),
        'mov' => array(array(4, '6D 6F 6F 76'), array(4, '66 72 65 65'), array(4, '6D 64 61 74'), array(4, '77 69 64 65'), array(4, '70 6E 6F 74'), array(4, '73 6B 69 70')),
        'avi' => array('52 49 46 46'),
        'wmv' => array('30 26 B2 75 8E 66 CF 11'),
        'flv' => array('46 4C 56'),
        'webm' => array('1A 45 DF A3'),
        '3gp' => array('00 00 00 14 66 74 79 70', '00 00 00 20 66 74 79 70'),
        'mp4' => 1
    );
    public static $_video_extension = array('mov', 'wmv', 'avi', 'flv', '3gp', 'mp4', 'mkv'); // MP4|AVI|FLV|MOV|WMV|MTS|MPG|MKV|3GP
    public static $_image_extension = array('jpg', 'png', 'gif', 'webp');
    public static $_mimetype = array(
        'text/plain' => 'txt',
        'text/html' => 'html',
        'application/vnd.ms-opentype' => 'otf',
        'application/x-font-ttf' => 'ttf',
        'application/font-sfnt' => 'ttf',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'text/csv' => 'csv',
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/svg' => 'svg',
        'image/webp' => 'webp',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/x-ms-wmv' => 'wmv',
        'video/x-flv' => 'flv',
        'video/webm' => 'webm',
        'video/3gpp' => '3gp',
        'audio/3gpp' => '3gp',
        'video/mp4' => 'mp4',
        'video/x-matroska' => 'mkv',
        'audio/x-flac' => 'flac',
            //video/mpeg , video/mpg , video/x-mpg , video/mpeg2 , application/x-pn-mpg , video/x-mpeg , video/x-mpeg2a , audio/mpeg , audio/x-mpeg , image/mpg => mpg
    );

    public static function verifyExtension($file_path, $extensions = null) {
        if (!($fh = @fopen($file_path, 'rb'))) {
            throw new Exception('File [' . $file_path . '] is not readable');
        }

        $binary_data = fread($fh, 50);

        fclose($fh);

        return static::_verifyExtension($binary_data, strtolower(preg_replace('/^.+\.([^\.]+)$/i', '\\1', $file_path)), $extensions);
    }

    public static function verifyExtensionByData($file_data, $extensions = null) {
        return static::_verifyExtension($file_data, null, $extensions);
    }

    protected static function _verifyExtension($binary_data, $file_extension = null, $extensions = null) {
        if ($extensions !== null) {
            if (!is_array($extensions)) {
                $extensions = array($extensions);
            }
        } else {
            $extensions = array();
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_buffer($finfo, $binary_data);
            finfo_close($finfo);

            if (!isset(static::$_mimetype[$mime_type])) {
                throw new Exception('File mime type [' . $mime_type . '] is not exist');
            }

            $extension = static::$_mimetype[$mime_type];

            if (count($extensions) > 0 && !in_array($extension, $extensions)) {
                throw new Exception('File mime type [' . $mime_type . '] is not matched');
            }

            return $extension;
        }

        $bytes = array();

        for ($i = 0; $i < min(25, strlen($binary_data)); $i ++) {
            $bytes[] = strtoupper(bin2hex($binary_data[$i]));
        }

        if (count($extensions) > 0) {
            $signature_data = array();

            foreach ($extensions as $extension) {
                $extension = trim(strtolower($extension));

                if (!isset(static::$_signature_data[$extension])) {
                    throw new Exception('File signature is not exists for extension: ' . $extension);
                }

                $signature_data[$extension] = static::$_signature_data[$extension];
            }
        } else {
            $signature_data = static::$_signature_data;
        }

        foreach ($signature_data as $extension => $signatures) {
            if (!is_array($signatures)) {
                if ($file_extension == $extension) {
                    return $extension;
                }

                continue;
            }

            foreach ($signatures as $signature) {
                $offset = 0;

                if (is_array($signature)) {
                    $offset = $signature[0];
                    $signature = $signature[1];
                }

                if (implode(' ', array_slice($bytes, $offset, substr_count($signature, ' ') + 1)) == $signature) {
                    return $extension;
                }
            }
        }

        throw new Exception('File signature data is not matched');
    }

    public static function verifyImage($file_path) {
        return static::verifyExtension($file_path, static::$_image_extension);
    }

    public static function verifyImageByData($file_data) {
        return static::verifyExtensionByData($file_data, static::$_image_extension);
    }

    public static function verifyVideo($file_path) {
        return static::verifyExtension($file_path, static::$_video_extension);
    }

    public static function verifyVideoByData($file_data) {
        return static::verifyExtensionByData($file_data, static::$_video_extension);
    }

}
