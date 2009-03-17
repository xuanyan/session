<?php

/* www.kukufun.com Session Class by xuanyan <xunayan1983@gmail.com> */

require_once dirname(__FILE__) . '/Abstract.php';

class Session_PDO extends abstract_session
{
    public static function start(PDO $pdo)
    {
        parent::init($pdo);
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
        $sql = "SELECT * FROM session WHERE PHPSESSID = ?";
        $sth = parent::$handler->prepare($sql);
        $sth->execute(array($PHPSESSID));

        if (!$result = $sth->fetch(PDO::FETCH_ASSOC))
        {
            return '';
        }
        // ua ip 更改
        if (parent::$ip != $result['client_ip'] || parent::$ua != $result['user_agent'])
        {
            if (!self::$flash)
            {
                self::destroy($PHPSESSID);
                return '';
            }
        }
        // 过期
        if (($result['update_time'] + parent::$lifetime) < parent::$time)
        {
            self::destroy($PHPSESSID);

            return '';
        }

        return $result['data'];
    }

    public static function write($PHPSESSID, $data)
    {
        $sql = "SELECT * FROM session WHERE PHPSESSID = ?";
        $sth = parent::$handler->prepare($sql);
        $sth->execute(array($PHPSESSID));

        // test sessionkey是否存在
        if ($result = $sth->fetch(PDO::FETCH_ASSOC))
        {
            // 当session数据没有在30s内改变则不更新
            if ($result['data'] != $data || parent::$time > ($result['update_time'] + 30))
            {
                $sql = "UPDATE session SET update_time = ?, data = ? WHERE PHPSESSID = ?";
                $sth = parent::$handler->prepare($sql);
                $sth->execute(array(parent::$time, $data, $PHPSESSID));
            }
        }
        else
        {
            // 空session不插入记录
            if (!empty($data))
            {
                $sql = "INSERT INTO session (PHPSESSID, update_time, client_ip, user_agent, data) VALUES (?, ?, ?, ?, ?)";
                $sth = parent::$handler->prepare($sql);
                $sth->execute(array($PHPSESSID, parent::$time, parent::$ip, parent::$ua, $data));
            }
        }

        return true;
    }

    public static function destroy($PHPSESSID)
    {
        $sql = "DELETE FROM session WHERE PHPSESSID = ?";
        $sth = parent::$handler->prepare($sql);
        $sth->execute(array($PHPSESSID));

        return true;
    }

    private static function gc($lifetime)
    {
        $sql = "DELETE FROM session WHERE update_time < ?";
        $sth = parent::$handler->prepare($sql);
        $sth->execute(array(parent::$time - $lifetime));

        return true;
    }
}

?>
