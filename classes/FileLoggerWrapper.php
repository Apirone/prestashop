<?php

use Apirone\API\Log\LogLevel;

class FileLoggerWrapper
{
    public const DEBUG = 0;
    public const INFO = 1;
    public const WARNING = 2;
    public const ERROR = 3;

    private $logger;

    public function __construct($log_level)
    {
        $this->logger = new FileLogger($log_level);
        $this->logger->setFilename(_PS_ROOT_DIR_ . $this->logFilename());

        return $this;
    }

    public function log($level, $message, $context = [])
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::ERROR:
                $ps_error_level = self::ERROR;
                break;
            case LogLevel::WARNING:
                $ps_error_level = self::WARNING;
                break;
            case LogLevel::NOTICE:
            case LogLevel::INFO:
                $ps_error_level = self::INFO;
                break;
            case LogLevel::DEBUG:
            default:
                $ps_error_level = self::DEBUG;
                break;
        }

        $replace = $this->prepareContext($context);

        $message = strip_tags($message);

        if (!empty($replace)) {
            $message = $this->prepareMessage($replace, $message);
        }

        $this->logger->log($message, $ps_error_level);

    }

    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->logger->$this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    private function prepareMessage($replace, $message)
    {
        foreach ($replace as $key => $object) {
            $label = trim($key, "{}");
            $message .= " \n{$label}: {$object}";
        }

        return $message;
    }

    private function prepareContext($context)
    {
        if (!$context) {
            return;
        }

        $replace = array();
        foreach ($context as $key => $value) {
            $replace['{'.$key.'}'] = (is_array($value) || is_object($value)) ? json_encode($value, JSON_PRETTY_PRINT) : $value;
        }

        return $replace;
    }

    private function logFilename() {
        return '/var/logs/' . date('Ymd') . '_apirone.log';
    }

}
