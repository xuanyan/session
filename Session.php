<?php

/* www.kukufun.com Session Class by xuanyan <xunayan1983@gmail.com> */

class Session
{
    private static $pdo = null;
    private static $ua = null;
    private static $ip = null;
    private static $data = null;
    private static $update_time = null;
    private static $lifetime = null;
    private static $time = null;

    public static function start(PDO $pdo)
    {
        self::$pdo = $pdo;
        self::$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        self::$ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
                    (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
                    (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));

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

        if (self::$ip != $result['client_ip'] || self::$ua != $result['user_agent'])
        {
            self::destroy($PHPSESSID);

            return '';
        }

        if (($query['update_time'] + self::$lifetime) < self::$time)
        {
            self::destroy($PHPSESSID);

            return '';
        }

        self::$data = $query['data'];
        self::$update_time = $query['update_time'];

        return $query['data'];
    }

    public static function write($sesskey, $data)
    {
        // test sessionkey是否存在
        if (self::$query->select('sessions', 'sesskey')->where('sesskey', $sesskey)->getOne())
        {
            // 当session数据没有在30s内改变则不更新
            if (self::$data != $data || self::$time > (self::$update_time + 30))
            {
                $query = self::$query->update('sessions')->set(array('update_time'=>self::$time, 'data'=>$data))
                                     ->where('sesskey', $sesskey)
                                     ->exec();
            }
        }
        else
        {
            // 空session不插入记录
            if (!empty($data))
            {
                $query = self::$query->insert('sessions', 'sesskey, update_time, ip, agent, data')
                                    ->values(array($sesskey, self::$time, self::$ip, self::$ua, $data))
                                    ->exec();
            }
        }

        return true;
    }

    public static function destroy($sesskey)
    {
        $query = self::$query->delete('sessions')->where('sesskey', $sesskey)
                             ->exec();
        return true;
    }

    private static function gc($life)
    {
        $query = self::$query->delete('sessions')->where('update_time', self::$time - $life, '<')
                                                 ->exec();
        return true;
    }
}

?>
