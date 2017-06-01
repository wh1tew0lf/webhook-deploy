<?php

namespace wh1te_w0lf\webhook_deploy\base;

abstract class Log extends Component {
    const lError = 0;
    const lWarning = 1;
    const lNotice = 2;
    const lTrace = 3;
    const lDebug = 4;

    protected $_logLevel = self::lNotice;

    abstract public function log($level, $message);

    public static function levelLabels() {
        return [
            self::lError => 'ERROR',
            self::lWarning => 'WARNING',
            self::lNotice => 'NOTICE',
            self::lTrace => 'TRACE',
            self::lDebug => 'DEBUG',
        ];
    }

    public static function getLevelLabel($level, $undefined = 'UNDEFINED') {
        $levels = static::levelLabels();
        return isset($levels[$level]) ? $levels[$level] : $undefined;
    }
}