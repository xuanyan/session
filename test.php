<?php

/* www.kukufun.com Session Class-test by xuanyan <xunayan1983@gmail.com> */

require './Session.Pdo.php';

$db_file = dirname(__FILE__) . '/session.db';

$pdo = new PDO('sqlite://' . $db_file);

Session::start($pdo);

print_r($_SESSION);

$_SESSION['now'] = time();

$_SESSION['count'] = empty($_SESSION['count']) ? 1 : $_SESSION['count'] + 1;

//session_destroy();

?>
