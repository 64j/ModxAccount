<?php

if(!defined('MODX_BASE_PATH')) {
	die('HACK???');
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
		$modx->load->controller('account/controller/login', $config);
		break;

	case $config['controllerRegister']:
		$modx->load->controller('account/controller/register', $config);
		break;

	case $config['controllerForgot']:
		$modx->load->controller('account/controller/forgot', $config);
		break;

	case $config['controllerProfile']:
		$modx->load->controller('account/controller/profile', $config);
		break;
}