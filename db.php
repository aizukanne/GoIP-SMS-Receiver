<?php
class Database
{
    private static $dbName = 'goip' ;
    private static $dbHost = 'localhost' ;
    private static $dbUsername = 'root';
    private static $dbUserPassword = 'your-password';
    private static $cont  = null;
    public function __construct() {
        die('Init function is not allowed');
    }
    public static function connect()
    {
       // One connection through whole application
       if ( null == self::$cont )
       {
        try
        {
          self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword, array(PDO::ATTR_PERSISTENT => TRUE)); 
        }
        catch(PDOException $e)
        {
          die($e->getMessage());
        }
       }
       return self::$cont;
    }
    public static function disconnect()
    {
        self::$cont = null;
    }
}
?> 
