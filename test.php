<?php

require './Session.php';
$dir = dirname(__FILE__);
$db_file = $dir . '/session.sqlite3';
$pdo = new PDO('sqlite://' . $db_file);
Session::start($pdo);

var_dump($_SESSION);

$_SESSION['now'] = time();

?>
