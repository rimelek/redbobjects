<?php
/**
 * R.E. DB Objects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
namespace REDBObjects;

use PDO;

/**
 * Project kezelő osztály
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
class REDBObjects
{
    /**
     * @var PDO[]
     */
    private static array $connections = [];

    /**
     * @param PDO $connection
     * @param $class
     * @return void
     */
    public static function setConnection(PDO $connection, $class = null)
    {
        $key = $class === null ? 'default' : $class;
        self::$connections[$key] = $connection;
    }

    /**
     * @param string|null $class
     * @return PDO
     */
    public static function getConnection(string $class = null): PDO
    {
        $key = ($class === null or !array_key_exists($class, self::$connections)) ? 'default' : $class;
        return self::$connections[$key];
    }

    /**
     * @param $dsn
     * @param $user
     * @param $password
     * @return PDO
     */
    public static function createDatabaseConnection($dsn, $user, $password): PDO
    {
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
}