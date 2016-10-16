<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

class ControllerAccountControllerLogin extends Loader {
	private $error = array();
	private $user;

	/**
	 * render form
	 * @param $config
	 */
	public function index($config = array()) {
		if($this->modx->getLoginUserID('web')) {
			$this->modx->sendRedirect($config['controllerProfile']);
		}

		$data = $config;
		$data['json_config'] = json_encode($config);

		foreach($_POST as $key => $value) {
			$data[$this->clean($key)] = $this->clean($value);
		}

		switch($data['action']) {
			case 'login':
				if($this->validate($data)) {
					$this->login($data);
					$this->SessionHandlerStart();
					if($config['success']) {
						$this->modx->sendRedirect($config['success']);
					} else {
						$this->modx->sendRedirect($config['controllerProfile']);
					}
				}
				break;

			case 'register':
				$this->modx->sendRedirect($config['controllerRegister']);
				break;

			case 'forgot':
				$this->modx->sendRedirect($config['controllerForgot']);
				break;
		}

		foreach($this->error as $key => $value) {
			$data['error_' . $key] = $value;
		}

		echo $this->modx->load->view('assets/snippets/account/view/login.tpl', $data);
	}

	/**
	 * trim/striptags/escape/
	 * @param $data
	 * @return array
	 */
	private function clean($data) {
		if(is_array($data)) {
			foreach($data as $key => $value) {
				unset($data[$key]);
				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
			$data = trim($this->modx->stripTags($this->modx->db->escape($data)));
		}
		return $data;
	}

	/**
	 * validate form
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
			$this->error['password'] = 'Пароль должен содержать не менее 6 знаков.';
		}

		return !$this->error;
	}

	/**
	 * mail validate
	 * @param $email
	 * @return bool
	 */
	private function mail_validate($email) {
		return preg_match('/^[^@]+@.*.[a-z]{2,15}$/i', $email) == true;
	}

	/**
	 * login
	 */
	private function login() {
		$this->modx->db->update(array(
			'password' => empty($this->user['cachepwd']) ? $this->user['password'] : $this->user['cachepwd'],
			'cachepwd' => ''
		), $this->modx->getFullTableName('web_users'), 'id=' . $this->user['internalKey']);

		$this->modx->db->update(array(
			'logincount' => ($this->user['logincount'] + 1),
			'lastlogin' => time(),
			'thislogin' => 1
		), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->user['internalKey']);
	}

	/**
	 * SessionHandlerStart
	 *
	 * @param string $cookieName
	 * @param bool $remember
	 *
	 * remeber может быть числом в секундах
	 */
	private function SessionHandlerStart($cookieName = 'WebLoginPE', $remember = true) {
		if($this->user['internalKey']) {
			$_SESSION['webShortname'] = $this->user['username'];
			$_SESSION['webFullname'] = $this->user['fullname'];
			$_SESSION['webEmail'] = $this->user['email'];
			$_SESSION['webValidated'] = 1;
			$_SESSION['webInternalKey'] = $this->user['internalKey'];
			$_SESSION['webValid'] = base64_encode($this->user['password']);
			$_SESSION['webUser'] = base64_encode($this->user['username']);
			$_SESSION['webFailedlogins'] = 0;
			$_SESSION['webLastlogin'] = 0;
			$_SESSION['webnrlogins'] = 0;
			$_SESSION['webUsrConfigSet'] = array();
			$_SESSION['webUserGroupNames'] = $this->getUserGroups();
			$_SESSION['webDocgroups'] = $this->getDocumentGroups();
			if($remember) {
				$cookieValue = md5($this->user['username']) . '|' . $this->user['password'];
				$cookieExpires = time() + (is_bool($remember) ? (60 * 60 * 24 * 365 * 5) : (int) $remember);
				setcookie($cookieName, $cookieValue, $cookieExpires, '/');
			}
		}
	}

	/**
	 * @return array
	 */
	private function getUserGroups() {
		$out = array();
		if($this->user['internalKey']) {
			$web_groups = $this->modx->getFullTableName('web_groups');
			$webgroup_names = $this->modx->getFullTableName('webgroup_names');

			$sql = "SELECT `ugn`.`name` FROM {$web_groups} as `ug`
                INNER JOIN {$webgroup_names} as `ugn` ON `ugn`.`id`=`ug`.`webgroup`
                WHERE `ug`.`webuser` = " . $this->user['internalKey'];
			$sql = $this->modx->db->makeArray($this->modx->db->query($sql));

			foreach($sql as $row) {
				$out[] = $row['name'];
			}
		}
		return $out;
	}

	/**
	 * @return array
	 */
	private function getDocumentGroups() {
		$out = array();
		if($this->user['internalKey']) {
			$web_groups = $this->modx->getFullTableName('web_groups');
			$webgroup_access = $this->modx->getFullTableName('webgroup_access');

			$sql = "SELECT `uga`.`documentgroup` FROM {$web_groups} as `ug`
                INNER JOIN {$webgroup_access} as `uga` ON `uga`.`webgroup`=`ug`.`webgroup`
                WHERE `ug`.`webuser` = " . $this->user['internalKey'];
			$sql = $this->modx->db->makeArray($this->modx->db->query($sql));

			foreach($sql as $row) {
				$out[] = $row['documentgroup'];
			}
		}
		return $out;
	}

	/**
	 * ajax
	 * @param $config
	 */
	public function ajax($config = array()) {
		global $modx;
		$json = array();

		if($this->modx->getLoginUserID('web')) {
			$json['redirect'] = $config['controllerProfile'];

		} else {
			$data['ajax'] = true;

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				foreach($_POST as $key => $value) {
					$data[$this->clean($key)] = $this->clean($value);
				}

				switch($data['action']) {
					case 'login':
						if($this->validate($data)) {
							$this->login($data);
							$this->SessionHandlerStart();
							if($config['success']) {
								$json['redirect'] = $config['success'];
							} else {
								$json['redirect'] = $config['controllerProfile'];
							}
						} else {
							$json['error'] = $this->error;
						}
						break;

					case 'register':
						$json['redirect'] = $config['controllerRegister'];
						break;

					case 'forgot':
						$json['redirect'] = $config['controllerForgot'];
						break;
				}
			}
		}

		header('Content-Type: application/json');
		echo json_encode($json);
	}

}