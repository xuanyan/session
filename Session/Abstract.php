<?php

/* www.kukufun.com Session Abstract Class by xuanyan <xunayan1983@gmail.com> */

abstract class abstract_session
{
    protected static $handler = null;
    protected static $ua = null;
    protected static $ip = null;
    protected static $lifetime = null;
    protected static $time = null;
    protected static $flash = false;

    protected static function init($handler)
    {
        self::$handler = $handler;

        self::$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        self::$ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
                    (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
                    (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));

        // 判断是否为合法ip
        filter_var(self::$ip, FILTER_VALIDATE_IP) === false && self::$ip = 'unknown';

        self::$lifetime = ini_get('session.gc_maxlifetime');
        self::$time = time();

        $session_name = ini_get('session.name');

        // fix for swf ie.swfupload
        if (isset($_POST[$session_name]))
        {
            self::$flash = true;
            session_id($_POST[$session_name]);
        }
    }
}
?>
