<?php

class Observer_PostOffice_Common {

    public static function initialize() {
        $ref_mailer = OSC::core('request')->get('_refmailer');

        if ($ref_mailer) {
            $ref_mailer = explode('-', $ref_mailer);

            if (count($ref_mailer) == 3) {
                $ref_mailer[0] = intval($ref_mailer[0]);

                if ($ref_mailer[0] >= (time() - 60) && $ref_mailer[2] == md5(OSC::core('encode')->encode($ref_mailer[0], '9823asd**'))) {
                    try {
                        OSC::helper('report/common')->setReferer('https://osc-emailer.com/' . $ref_mailer[1]);
                    } catch (Exception $ex) {
                        
                    }
                }
            }
        }
    }
}
