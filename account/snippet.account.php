<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

/**
 * modxAccount snippet
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author ko4inn <ko4inn@gmail.com>
 */

$config = array();

if(empty($controller)) {
	$controller = $modx->documentIdentifier;
}

$config['controller'] = !empty($controller) ? (is_numeric($controller) ? $modx->makeUrl($controller) : '/' . ltrim($controller, '/')) : '/account';
$config['controllerRegister'] = !empty($controllerRegister) ? (is_numeric($controllerRegister) ? $modx->makeUrl($controllerRegister) : '/' . ltrim($controllerRegister, '/')) : '/account/register';
$config['controllerLogin'] = !empty($controllerLogin) ? (is_numeric($controllerLogin) ? $modx->makeUrl($controllerLogin) : '/' . ltrim($controllerLogin, '/')) : '/account';
$config['controllerForgot'] = !empty($controllerForgot) ? (is_numeric($controllerForgot) ? $modx->makeUrl($controllerForgot) : '/' . ltrim($controllerForgot, '/')) : '/account/forgot';
$config['controllerProfile'] = !empty($controllerProfile) ? (is_numeric($controllerProfile) ? $modx->makeUrl($controllerProfile) : '/' . ltrim($controllerProfile, '/')) : '/account/profile';
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
