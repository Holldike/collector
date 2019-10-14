<?php

class Db {
    private static $connect;
    private static $db = DB;
    private static $host = DB_HOST;
    private static $user = DB_USER;
    private static $pass = DB_PASS;

    protected function __construct() { }

    protected function __clone() { }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }
    public static function getConnect() {
        if (self::$connect) {
            return self::$connect;
        } else {
            self::$connect = new mysqli(self::$host, self::$user, self::$pass, self::$db);
            return self::$connect;
        }
    }
}
