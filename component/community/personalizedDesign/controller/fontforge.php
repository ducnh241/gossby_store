<?php

class Controller_PersonalizedDesign_Fontforge extends Abstract_Core_Controller {

    public function actionConvert() {
        if(! isset($_FILES['font'])) {
            $this->_ajaxError('No file uploaded');
        }

        if($_FILES['font']['error'] !== UPLOAD_ERR_OK) {
            $errors_info = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.'
            ];

            $this->_ajaxError($errors_info[$_FILES['font']['error']]);
        }

        try {
            $tmp_file_path = $_FILES['font']['tmp_name'];

            $extension = OSC_File::verifyExtension($tmp_file_path);

            if (!in_array($extension, ['ttf', 'otf'], true)) {
                throw new Exception('Just TTF, OTF extension able to upload');
            }

            $font = \FontLib\Font::load($tmp_file_path);
            $font->parse();

            $font_name = $font->getFontName();

            $font_key = preg_replace('/^(.+)\.[a-zA-Z0-9]+$/', '\\1', $font_name);
            $font_key = preg_replace('/[^a-zA-Z0-9]/', '_', $font_key);
            $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
            $font_key = preg_replace('/_{2,}/', '_', $font_key);
            $font_key = strtolower($font_key);

            $font_name = implode(' ', array_map(function($segment) {
                return ucfirst($segment);
            }, explode('_', $font_key)));

            $random_key = OSC::makeUniqid();

            $font_path = 'fonts/' . $random_key . '/' . $font_key . '/' . $font_key . '.' . $extension;
            $font_path_ttf = 'fonts/' . $random_key . '/' . $font_key . '/' . $font_key . '.ttf';
            $font_path_woff2 = 'fonts/' . $random_key . '/' . $font_key . '/' . $font_key . '.woff2';
            $font_path_svg = 'fonts/' . $random_key . '/' . $font_key . '/' . $font_key . '.svg';

            $font_full_path = OSC::core('storage')->preDirForSaveFile($font_path);
            $font_full_path_ttf = OSC::core('storage')->preDirForSaveFile($font_path_ttf);
            $font_full_path_woff2 = OSC::core('storage')->preDirForSaveFile($font_path_woff2);
            $font_full_path_svg = OSC::core('storage')->preDirForSaveFile($font_path_svg);

            if(! move_uploaded_file($tmp_file_path, $font_full_path)) {
                throw new Exception('Unable to save uploaded file');
            }

            if ($extension !== 'ttf') {
                exec("fontforge -c \"import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])\" {$font_full_path} {$font_full_path_ttf}");

                if (!file_exists($font_full_path_ttf)) {
                    throw new Exception('Cannot convert font to TTF');
                }
            }

            exec("fontforge -c \"import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])\" {$font_full_path} {$font_full_path_woff2}");

            if (!file_exists($font_full_path_woff2)) {
                throw new Exception('Cannot convert font to WOFF2');
            }

            exec("fontforge -c \"import fontforge; from sys import argv; f = fontforge.open(argv[1]); f.generate(argv[2])\" {$font_full_path} {$font_full_path_svg}");

            if (!file_exists($font_full_path_svg)) {
                throw new Exception('Cannot convert font to SVG');
            }

            $this->_ajaxResponse([
                'ttf' => base64_encode(file_get_contents($font_full_path_ttf)),
                'woff2' => base64_encode(file_get_contents($font_full_path_woff2)),
                'svg' => base64_encode(file_get_contents($font_full_path_svg)),
            ]);
        } catch(Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }
}
