<?php

namespace wh1te_w0lf\webhook_deploy;

use wh1te_w0lf\webhook_deploy\base\Log;

class FileLog extends Log {
    protected $_logFileName = 'log.log';
    protected $_maxLogSize = 20 * 1024 * 1024;
    protected $_timezone = '+6';
    protected $_dateFormat = 'Y-m-d H:i:s';

    public function log($level, $message) {
        if ($this->_logLevel < $level) { return; }
        if (file_exists($this->_logFileName) && (filesize($this->_logFileName) > $this->_maxLogSize) ) {
            rename($this->_logFileName, $this->_logFileName . '.' . time());
        }

        $level = static::getLevelLabel($level);
        $date = gmdate($this->_dateFormat, time() + 3600 * ($this->_timezone + date("I")));

        file_put_contents($this->_logFileName, "{$date} [{$level}] '{$message}'\n", FILE_APPEND);
    }

}