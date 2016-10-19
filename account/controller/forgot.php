<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

require_once(dirname(dirname(__FILE__)) . '/Account.abstract.php');

class AccountControllerForgot extends Account {

	public function index() {

	}

	/**
	 * render form
	 * @param $config
	 */
	public function render($config) {
		$data = $config;
		$data['json_config'] = json_encode($config);

		if($this->getID()) {
			$this->modx->sendRedirect($config['controllerProfile']);
		}

		foreach($_POST as $key => $value) {
			$data[$this->clean($key)] = $this->clean($value);
		}

		if($data['action'] == 'forgot' && $this->validate($data)) {
			$this->forgot($data);

			if($config['success']) {
				$this->modx->sendRedirect($config['success']);
			} else {
				$this->modx->sendRedirect($config['controllerLogin']);
			}
		}

		foreach($this->error as $key => $value) {
			$data['error_' . $key] = $value;
		}
		
		if(empty($config['tpl'])) {
			echo $this->view('assets/snippets/account/view/forgot.tpl', $data);
		} else {
			echo $this->modx->parseText($this->modx->getTpl($config['tpl']), $data);
		}
	}

	/**
	 * validate form
	 * @param $data
	 * @return bool
	 */
	private function validate($data) {

		if(mb_strlen($data['email']) > 96 || !$this->mail_validate($data['email'])) {
			$this->error['email'] = 'Проверьте правильность электронного адреса.';
		} else {
			$sql = $this->modx->db->select('*', $this->modx->getFullTableName('web_user_attributes'), 'email="' . $data['email'] . '"');

			if($this->modx->db->getRecordCount($sql) > 0) {
				$this->user = $this->modx->db->getRow($sql);
			} else {
				$this->error['email'] = 'Данный адрес электронной почты (' . $data['email'] . ') не найден.';
			}
		}

		if(isset($data['captcha_' . $data['keyVeriWord']]) && $_SESSION['veriword_' . md5($data['keyVeriWord'])] !== $data['captcha_' . $data['keyVeriWord']]) {
			$this->error['captcha_' . $data['keyVeriWord']] = 'Неверный проверочный код.';
		}

		if(isset($data['captcha']) && $_SESSION['veriword'] !== $data['captcha']) {
			$this->error['captcha'] = 'Неверный проверочный код.';
		}

		return !$this->error;
	}

	/**
	 * reset password
	 * @param $data
	 */
	private function forgot($data) {
		$data['cachepwd'] = $this->genPass(10, 'Aa0');

		$this->modx->db->update(array(
			'cachepwd' => md5($data['cachepwd'])
		), $this->modx->getFullTableName('web_users'), 'id=' . $this->getID());

		foreach($this->user as $key => $value) {
			if(!empty($value)) {
				$data[$key] = $value;
			}
		}

		$data['password'] = $data['cachepwd'];
		$this->send($data, $this->modx->config['webpwdreminder_message']);
	}

	public function ajax($config) {
		$json = array();

		if($this->getID()) {
			$json['redirect'] = $config['controllerProfile'];

		} else {
			foreach($_POST as $key => $value) {
				$data[$this->clean($key)] = $this->clean($value);
			}

			if($data['action'] == 'forgot' && $this->validate($data)) {
				$this->forgot($data);

				if($config['success']) {
					$json['redirect'] = $config['success'];
				} else {
					$json['redirect'] = $config['controllerLogin'];
				}

			} else {
				$json['error'] = $this->error;
			}
		}

		header('content-type: application/json');
		return json_encode($json);
	}
}
