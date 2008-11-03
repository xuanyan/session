<?php

/* www.kukufun.com Session Class by xuanyan <xunayan1983@gmail.com> */

class Session
{
    private static $pdo = null;
    private static $ua = null;
    private static $ip = null;
    private static $lifetime = null;
    private static $time = null;

    public static function start(PDO $pdo)
    {
        self::$pdo = $pdo;
        self::$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        self::$ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
                    (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
                    (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));

        // 判断是否为合法ip
        filter_var(self::$ip, FILTER_VALIDATE_IP) === false && self::$ip = 'unknown';

        self::$lifetime = ini_get('session.gc_maxlifetime');
        self::$time = time();

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
        // self::gc(ini_get('session.gc_maxlifetime'));

        return true;
    }

    private static function read($PHPSESSID)
    {
        $sql = "SELECT * FROM session WHERE PHPSESSID = ?";
        $sth = self::$pdo->prepare($sql);
        $sth->execute(array($PHPSESSID));

        if (!$result = $sth->fetch(PDO::FETCH_ASSOC))
        {
            return '';
        }
        // ua ip 更改
        if (self::$ip != $result['client_ip'] || self::$ua != $result['user_agent'])
        {
            self::destroy($PHPSESSID);

            return '';
        }
        // 过期
        if (($result['update_time'] + self::$lifetime) < self::$time)
        {
            self::destroy($PHPSESSID);

            return '';
        }

        return $result['data'];
    }

    public static function write($PHPSESSID, $data)
    {
        $sql = "SELECT * FROM session WHERE PHPSESSID = ?";
        $sth = self::$pdo->prepare($sql);
        $sth->execute(array($PHPSESSID));

        // test sessionkey是否存在
        if ($result = $sth->fetch(PDO::FETCH_ASSOC))
        {
            // 当session数据没有在30s内改变则不更新
            if ($result['data'] != $data || self::$time > ($result['update_time'] + 30))
            {
                $sql = "UPDATE session SET update_time = ?, data = ? WHERE PHPSESSID = ?";
                $sth = self::$pdo->prepare($sql);
                $sth->execute(array(self::$time, $data, $PHPSESSID));
            }
        }
        else
        {
            // 空session不插入记录
            if (!empty($data))
            {
                $sql = "INSERT INTO session (PHPSESSID, update_time, client_ip, user_agent, data) VALUES (?, ?, ?, ?, ?)";
                $sth = self::$pdo->prepare($sql);
                $sth->execute(array($PHPSESSID, self::$time, self::$ip, self::$ua, $data));
            }
        }

        return true;
    }

    public static function destroy($PHPSESSID)
    {
        $sql = "DELETE FROM session WHERE PHPSESSID = ?";
        $sth = self::$pdo->prepare($sql);
        $sth->execute(array($PHPSESSID));

        return true;
    }

    private static function gc($lifetime)
    {
        $sql = "DELETE FROM session WHERE update_time < ?";
        $sth = self::$pdo->prepare($sql);
        $sth->execute(array(self::$time - $lifetime));

        return true;
    }
}

?>
