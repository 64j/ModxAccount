<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

require_once(dirname(dirname(__FILE__)) . '/Account.abstract.php');

class AccountControllerLogin extends Account {

	public function index() {

	}

	/**
	 * render form
	 * @param $config
	 */
	public function render($config = array()) {
		if($this->getID()) {
			$this->modx->sendRedirect($config['controllerProfile']);
		}

		$data = $config;
		$data['json_config'] = json_encode($config);

		foreach($_POST as $key => $value) {
			$data[$key] = $this->clean($value);
		}

		if(isset($data['action'])) {
			switch($data['action']) {
				case 'login': {
					if($this->validate($data)) {
						$this->login();
						$this->SessionHandler('start');
						if(!empty($config['success'])) {
							$this->modx->sendRedirect($config['success']);
						} else {
							$this->modx->sendRedirect($config['controllerProfile']);
						}
					}
					break;
				}
			}
		}

		foreach($this->error as $key => $value) {
			$data['error_' . $key] = $value;
		}

		if(empty($config['tpl'])) {
			echo $this->view('assets/snippets/account/view/login.tpl', $data);
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

		// mail
		if(isset($data['email']) && (mb_strlen($data['email']) > 96) || !$this->mail_validate($data['email'])) {
			$this->error['email'] = 'Проверьте правильность электронного адреса.';
		} else {
			if(!empty($data['email']) && !empty($data['password'])) {
				$sql = $this->modx->db->query("SELECT * 
				FROM " . $this->modx->getFullTableName('web_users') . " AS wu
				LEFT JOIN " . $this->modx->getFullTableName('web_user_attributes') . " AS wua USING(id)
				WHERE wua.email='" . $data['email'] . "'");

				if($this->modx->db->getRecordCount($sql) > 0) {
					$row = $this->modx->db->getRow($sql);

					if($row['email'] == $data['email'] && ($row['password'] == md5($data['password']) || $row['cachepwd'] == md5($data['password']))) {
						if($row['blocked'] == 1) {
							$this->error['email'] = 'Данный адрес электронной почты (' . $data['email'] . ') заблокирован.';
						} else {
							$this->user = $row;
						}
					} else if($row['email'] == $data['email'] && $row['password'] != md5($data['password'])) {
						$this->error['password'] = 'Неверно указан пароль.';
					}

				} else {
					$this->error['email'] = 'Данный адрес электронной почты (' . $data['email'] . ') не найден.';
				}
			}
		}

		// username
		if(isset($data['username']) && (mb_strlen($data['username']) < 3 || mb_strlen($data['username']) > 32)) {
			$this->error['username'] = 'Проверьте правильность имени пользователя.';
		} else {
			if(!empty($data['username']) && !empty($data['password'])) {
				$sql = $this->modx->db->query("SELECT * 
				FROM " . $this->modx->getFullTableName('web_users') . " AS wu
				LEFT JOIN " . $this->modx->getFullTableName('web_user_attributes') . " AS wua USING(id)
				WHERE wu.username='" . $data['username'] . "'");

				if($this->modx->db->getRecordCount($sql) > 0) {
					$row = $this->modx->db->getRow($sql);

					if($row['username'] == $data['username'] && ($row['password'] == md5($data['password']) || $row['cachepwd'] == md5($data['password']))) {
						if($row['blocked'] == 1) {
							$this->error['username'] = 'Данный адрес пользователь (' . $data['email'] . ') заблокирован.';
						} else {
							$this->user = $row;
						}
					} else if($row['username'] == $data['username'] && $row['password'] != md5($data['password'])) {
						$this->error['password'] = 'Неверно указан пароль.';
					}

				} else {
					$this->error['username'] = 'Данный пользователь (' . $data['username'] . ') не найден.';
				}
			}
		}

		if((mb_strlen($data['password']) < 6) || (mb_strlen($data['password']) > 20)) {
			$this->error['password'] = 'Пароль должен содержать не менее 6 и не более 20 знаков.';
		}

		return !$this->error;
	}

	/**
	 * ajax
	 * @param $config
	 * @return array
	 */
	public function ajax($config) {
		$json = array();

		if($this->getID()) {
			$json['redirect'] = $config['controllerProfile'];

		} else {
			foreach($_POST as $key => $value) {
				$data[$key] = $this->clean($value);
			}

			if(isset($data['action'])) {
				switch($data['action']) {
					case 'login': {
						if($this->validate($data)) {
							$this->login();
							$this->SessionHandler('start');
							if(!empty($config['success'])) {
								$json['redirect'] = $config['success'];
							} else {
								$json['redirect'] = $config['controllerProfile'];
							}
						} else {
							$json['error'] = $this->error;
						}
						break;
					}
				}
			}

		}

		header('content-type: application/json');
		return json_encode($json);
	}

}
