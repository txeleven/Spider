<?php
namespace extend;

use extend\TxExt\TxCache;
use UP\UpGrade;


class Tx{
    public static $redis;
    //public static $jwt;
    public static $grade;
    public static function Instance(){
        require_once $_SERVER['DOCUMENT_ROOT']. '/upgrade.php';
        self::$grade=UpGrade::class;
        if(!self::$redis) {
          //  require_once app()->getBasePath(). '../extend/TxExt/TxRedis.php';
        //    self::$redis = (new PRedis());
           // echo  app()->getBasePath(). '../extend/TxExt/TxCache.php';exit;
            require_once app()->getBasePath(). '../extend/TxExt/TxCache.php';
            self::$redis = (new TxCache());
        }
        return self::class;
    }
}
//初始化redis
Tx::Instance();
