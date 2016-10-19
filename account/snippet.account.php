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

function trim_name_controller($controller) {
	global $modx;
	if(is_numeric($controller)) {
		$controller = str_replace($modx->config['friendly_url_suffix'], '', $modx->makeUrl($controller));
	} else {
		$controller = '/' . ltrim($controller, '/');
	}
	return $controller;
}

$config['controller'] = !empty($controller) ? trim_name_controller($controller) : '/account';
$config['controllerRegister'] = !empty($controllerRegister) ? trim_name_controller($controllerRegister) : '/account/register';
$config['controllerLogin'] = !empty($controllerLogin) ? trim_name_controller($controllerLogin) : '/account';
$config['controllerForgot'] = !empty($controllerForgot) ? trim_name_controller($controllerForgot) : '/account/forgot';
$config['controllerProfile'] = !empty($controllerProfile) ? trim_name_controller($controllerProfile) : '/account/profile';
$config['success'] = !empty($success) ? trim_name_controller($success) : '';
$config['userGroupId'] = !empty($userGroupId) ? $userGroupId : '';
$config['keyVeriWord'] = time();
$config['tpl'] = isset($tpl) ? $tpl : '';

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
