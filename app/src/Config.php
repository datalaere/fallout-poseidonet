<?php

class Config
{
    // this is public to allow better Unit Testing
    public static $config;

    public static function get($key)
    {
        if (!self::$config) {

            $config_file = '../app/config/config.' . Env::get() . '.php';

            if (!file_exists($config_file)) {
                return false;
            }

            self::$config = require $config_file;
        }

        return self::$config[$key];
    }
}