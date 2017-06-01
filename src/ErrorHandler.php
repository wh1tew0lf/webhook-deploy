<?php

namespace wh1te_w0lf\webhook_deploy;

use wh1te_w0lf\webhook_deploy\base\Component;
use wh1te_w0lf\webhook_deploy\base\Log;

class ErrorHandler extends Component {
    /** @var base\Log $_log */
    protected $_log;

    /** @var base\Notificator $_log */
    protected $_notification;

    protected function __construct(array $config) {
        parent::__construct($config);
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    public function errorHandler($errno , $errstr, $errfile, $errline, $errcontext) {
        $errorTypes = array(
            E_STRICT => 'Strict',
            E_PARSE => 'Parse',
            E_COMPILE_WARNING => 'Compile_warning',
            E_COMPILE_ERROR => 'Compile_error',
            E_CORE_WARNING => 'Core_warning',
            E_CORE_ERROR => 'Core_error',
            E_NOTICE => 'Notice',
            E_USER_NOTICE => 'Notice',
            E_WARNING => 'Warning',
            E_USER_WARNING => 'Warning',
            E_ERROR => 'Fatal_Error',
            E_USER_ERROR => 'Fatal_Error',
        );
        $error = isset( $errorTypes[ $errno ] ) ? $errorTypes[ $errno ] :  'Unknown(' . $errno . ')';
        $message = "{$error}: {$errstr} in {$errfile}:{$errline}";

        $this->_log->log(Log::lError, $message);
        $this->_notification->notificate("Deploy failed: {$message}");
        return true;
    }

    /**
     * @param \Exception|\Throwable $ex
     */
    public function exceptionHandler($ex) {
        $this->_notification->notificate("Deploy failed: {$ex->getMessage()}");
        $this->_log->log(Log::lError, $ex->getMessage());
    }
}