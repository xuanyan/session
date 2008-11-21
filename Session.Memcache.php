<?php

/* kukufun Session.Memcache by xuanyan <xunayan1983@gmail.com> */

require_once './Abstract.php';

class Session extends abstract_session
{
    public static function start(Memcache $memcache)
    {
        parent::init($memcache);
        session_set_save_handler(
            array(__CLASS__, 'open'),
            array(__CLASS__, 'close'),
            array(__CLASS__, 'read'),
            array(__CLASS__, 'write'),
            array(__CLASS__, 'destroy'),
            array(__CLASS__, 'gc')
        );
        session_start();
    }

    private static function open($path, $name)
    {
        return true;
    }

    public static function close()
    {
        return true;
    }

    private static function read($PHPSESSID)
    {
        
    }

    public static function write($PHPSESSID, $data)
    {
        
    }

    public static function destroy($PHPSESSID)
    {
        
    }

    private static function gc($lifetime)
    {
        
    }
}

?>