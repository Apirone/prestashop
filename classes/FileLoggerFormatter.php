<?php

class FileLoggerFormatter extends FileLogger
{
    protected function logMessage($message, $level)
    {
        $dt = new \DateTime();
        $formatted_message = sprintf('%s %s %s', $dt->format("Y-m-d\TH:i:sP"), $this->level_value[$level], $message . "\n");

        return (bool) file_put_contents($this->getFilename(), $formatted_message, FILE_APPEND);
    }
}