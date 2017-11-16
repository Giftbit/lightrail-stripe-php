<?php

namespace Lightrail;

class Lightrail
{
    public static $apiKey;
    public static $sharedSecret;

    static $API_BASE = 'https://api.lightrail.com/v1/';

    public static function setSharedSecret($theSharedSecret)
    {
        self::$sharedSecret = $theSharedSecret;
    }

    public static function setApiKey($theApiKey)
    {
        self::$apiKey = $theApiKey;
    }

    public static function checkParams($params)
    {
        if (!isset(self::$apiKey))
            throw new BadParameterException('Lightrail::$apiKey not set.');
    }
}