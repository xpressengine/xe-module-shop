<?php

class ShopLogger
{
    const LOG_FILE_PATH = './files/shop_log.txt';

    public static function log($message)
    {
        $timestamp = date("y.m.d H:i:s");
        $log_message = $timestamp . "\t" . $message . PHP_EOL;
        FileHandler::writeFile(self::LOG_FILE_PATH, $log_message);
    }
}