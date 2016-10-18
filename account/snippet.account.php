<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

$config = array();
$config['controller'] = !empty($controller) ? (is_numeric($controller) ? $modx->makeUrl($controller) : '/' . ltrim($controller, '/')) : 'register';
$config['controllerRegister'] = !empty($controllerRegister) ? (is_numeric($controllerRegister) ? $modx->makeUrl($controllerRegister) : '/' . ltrim($controllerRegister, '/')) : 'register';
$config['controllerLogin'] = !empty($controllerLogin) ? (is_numeric($controllerLogin) ? $modx->makeUrl($controllerLogin) : '/' . ltrim($controllerLogin, '/')) : 'login';
$config['controllerForgot'] = !empty($controllerForgot) ? (is_numeric($controllerForgot) ? $modx->makeUrl($controllerForgot) : '/' . ltrim($controllerForgot, '/')) : 'forgot';
$config['controllerProfile'] = !empty($controllerProfile) ? (is_numeric($controllerProfile) ? $modx->makeUrl($controllerProfile) : '/' . ltrim($controllerProfile, '/')) : 'profile';
$config['success'] = !empty($success) ? (is_numeric($success) ? $modx->makeUrl($success) : '/' . ltrim($success, '/')) : '';
$config['userGroupId'] = !empty($userGroupId) ? $userGroupId : '';
$config['keyVeriWord'] = time();

switch($config['controller']) {
	case $config['controllerLogin']:
		include_once('controller/login.php');
		$controller = new AccountControllerLogin($modx);
		$controller->render($config);
		break;

	case $config['controllerRegister']:
		include_once('controller/register.php');
		$controller = new AccountControllerRegister($modx);
		$controller->render($config);
		break;

	case $config['controllerForgot']:
		include_once('controller/forgot.php');
		$controller = new AccountControllerForgot($modx);
		$controller->render($config);
		break;

	case $config['controllerProfile']:
		include_once('controller/profile.php');
		$controller = new AccountControllerProfile($modx);
		$controller->render($config);
		break;
}