<?php

namespace Bca;

class BcaHttpInstance
{
    private static $instance      = null;
    private static $corp_id       = '';
    private static $client_id     = '';
    private static $client_secret = '';
    private static $api_key       = '';
    private static $secret_key    = '';

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getBcaHttp()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new BcaHttp(
            self::$corp_id,
            self::$client_id,
            self::$client_secret,
            self::$api_key,
            self::$secret_key
        );
        return self::$instance;
    }
}
