<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');

spl_autoload_register(function ($className) {
	include 'src/' . $className . '.php';
});

$conf = include 'conf.php';

session_start();

$mysqli = new mysqli($conf['mysql_host'], $conf['mysql_user'], $conf['mysql_pswd'], $conf['mysql_db']);
$mysqli->query("set names utf8");

$userManager = new UserManager($mysqli, $_SESSION);
$todoManager = new TodoManager($mysqli, $userManager->getAuthorizedUser());
$controller = new Controller($todoManager, $userManager, $conf);

if (!array_key_exists($key = 'action', $_REQUEST)
	|| !is_string($action = $_REQUEST[$key])
	|| !is_callable([$controller, $method = $action.'Action'])) {
	$method = 'mainAction';
}
$controller->$method($_REQUEST);
