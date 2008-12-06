<?php

/* kukufun Session.Memcache by xuanyan <xunayan1983@gmail.com> */

require_once dirname(__FILE__) . '/Abstract.php';

class Session extends abstract_session
{
    const NS = 'session_';

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
        $out = parent::$handler->get(self::NS . $PHPSESSID);
        if ($out === false || $out === null)
        {
            return '';
        }

        return $out;
    }

    public static function write($PHPSESSID, $data)
    {
        $method = $data ? 'set' : 'replace';

        return parent::$handler->$method(self::NS . $PHPSESSID, $data, MEMCACHE_COMPRESSED, parent::$lifetime);
    }

    public static function destroy($PHPSESSID)
    {
        return parent::$handler->delete(self::NS . $PHPSESSID);
    }

    private static function gc($lifetime)
    {
        return true;
    }
}

?>
