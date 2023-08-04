<?php

class OSC_Cron_Cron_Storage_CleanTmp extends OSC_Cron_Abstract {

    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp) {
        $dto = new DateTime();
        $dto->modify('-3 days');
        $flag_timestamp = mktime(0, 0, 1, $dto->format('m'), $dto->format('d'), $dto->format('Y'));

        $debug_root_path = OSC_VAR_PATH . '/tmp';

        $rs = opendir($debug_root_path);

        while (($dirname = readdir($rs)) !== false) {
            if ($dirname == '.' || $dirname == '..' || !preg_match('/^\d+$/', $dirname) || !is_dir($debug_root_path . '/' . $dirname)) {
                continue;
            }

            $dirname = intval($dirname);

            if ($dirname >= $flag_timestamp) {
                continue;
            }

            exec('rm -rf ' . $debug_root_path . '/' . $dirname);
        }

        closedir($rs);
    }

}
