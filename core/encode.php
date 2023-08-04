<?php

class OSC_Encode {

    private $__checksum_string = '';
    private $__checksum_array = array();

    public function __construct() {
        // Do nothing;
    }

    public function encode($data, $security_word) {
        $this->__makeChecksum($security_word);

        $data = base64_encode($data);

        $check_sum_len = count($this->__checksum_array);

        for ($i = 0; $i < strlen($data); $i++) {
            $data_encoded .= $this->__encodeBit($data[$i], $this->__checksum_array[$i % $check_sum_len]);
        }

        return $data_encoded;
    }

    public function decode($data, $security_word) {
        $this->__makeChecksum($security_word);

        $check_sum_len = count($this->__checksum_array);

        for ($i = 0; $i < strlen($data); $i++) {
            $data_decoded .= $this->__decodeBit($data[$i], $this->__checksum_array[$i % $check_sum_len]);
        }

        return base64_decode($data_decoded);
    }

    private function __makeChecksum($security_word) {
        $security__checksum = '';

        for ($i = 0; $i < strlen($security_word); $i++) {
            $security__checksum .= ord($security_word[$i]);
            $security__checksum = intval($security__checksum);
            $security__checksum <<= 5;
        }

        $this->__checksum_string = $security__checksum;

        $security__checksum = strval(abs($security__checksum));

        $this->__checksum_array = array();

        for ($idx = 0; $idx < strlen($security__checksum); $idx ++) {
            $this->__checksum_array[] = $security__checksum[$idx];
        }
    }

    private function __encodeBit($v, $n) {
        $ascii_code = ord($v);
        return chr(~ ( $ascii_code ^ ($n % 2 ? $this->__checksum_string << $n : $this->__checksum_string >> $n)));
    }

    private function __decodeBit($v, $n) {
        $ascii_code = ord($v);
        return chr(( ~ $ascii_code ) ^ ($n % 2 ? $this->__checksum_string << $n : $this->__checksum_string >> $n));
    }

}
