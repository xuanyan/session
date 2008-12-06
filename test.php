<?php

/* www.kukufun.com Session Class-test by xuanyan <xunayan1983@gmail.com> */

// require './Session.Pdo.php';
// 
// $db_file = dirname(__FILE__) . '/session.db';
// 
// $pdo = new PDO('sqlite://' . $db_file);


// Session::start($pdo);


require './Session.Memcache.php';

$mc = new Memcache();
$mc->connect('192.168.13.32', 11211);
Session::start($mc);


print_r($_SESSION);

$_SESSION['now'] = time();

$_SESSION['count'] = empty($_SESSION['count']) ? 1 : $_SESSION['count'] + 1;

//session_destroy();

?>
