<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

require_once(dirname(dirname(__FILE__)) . '/Account.abstract.php');

class AccountControllerRegister extends Account {

	public function index() {

	}

	/**
	 * render form
	 * @param $config
	 */
	public function render($config = array()) {
		$data = $config;
		$data['json_config'] = json_encode($config);

		if($this->getID()) {
			$this->modx->sendRedirect($config['controllerProfile']);
		}

		foreach($_POST as $key => $value) {
			$data[$this->clean($key)] = $this->clean($value);
		}

		if($data['photo_cache']) {
			$data['photo_cache_path'] = $this->modx->config['rb_base_url'] . 'cache/images/' . $data['photo_cache'];
		}

		if($data['action'] == 'register' && $this->validate($data)) {
			$userid = $this->add($data);
			if($userid && !$this->error) {
				$this->send($data, $this->modx->config['websignupemail_message']);
				$this->SessionHandler('start');
			}
			if($config['success']) {
				$this->modx->sendRedirect($config['success']);
			} else {
				$this->modx->sendRedirect($config['controllerProfile']);
			}

		}

		foreach($this->error as $key => $value) {
			$data['error_' . $key] = $value;
		}

		include_once MODX_MANAGER_PATH . 'includes/lang/country/' . $this->modx->config['manager_language'] . '_country.inc.php';

		if(isset($_country_lang)) {
			asort($_country_lang);
			$data['country_select'] = '<option value="">-- выбрать --</option>';
			foreach($_country_lang as $key => $country) {
				$data['country_select'] .= '<option value="' . $key . '"' . (isset($data['country']) && $data['country'] == $key ? ' selected="selected"' : '') . '">' . $country . '</option>';
			}
		}
		echo $this->view('assets/snippets/account/view/register.tpl', $data);
	}

	/**
	 * validate form
	 * @param $data
	 * @return bool
	 */
	private function validate($data) {

		if(isset($data['fullname'])) {
			if(mb_strlen($data['fullname']) < 3 || mb_strlen($data['fullname']) > 32) {
				$this->error['fullname'] = 'Имя должно быть не менее 3 знаков.';
			}
		} else if(isset($data['lastname']) || isset($data['firstname'])) {
			if(isset($data['lastname'])) {
				if(mb_strlen($data['lastname']) < 3 || mb_strlen($data['lastname']) > 32) {
					$this->error['lastname'] = 'Фамилия должна быть не менее 3 знаков.';
				} else {
					$data['fullname'] .= ($data['fullname'] ? ' ' . $data['lastname'] : $data['lastname']);
				}
			}
			if(isset($data['firstname'])) {
				if(mb_strlen($data['firstname']) < 3 || mb_strlen($data['firstname']) > 32) {
					$this->error['firstname'] = 'Имя должно быть не менее 3 знаков.';
				} else {
					$data['fullname'] .= ($data['fullname'] ? ' ' . $data['firstname'] : $data['firstname']);
				}
			}
		}

		if(isset($data['username'])) {
			if(mb_strlen($data['username']) < 3 || mb_strlen($data['username']) > 32) {
				$this->error['name'] = 'Логин должен быть не менее 3 знаков.';
			} else {
				$username = $this->modx->db->getValue($this->modx->db->select('username', $this->modx->getFullTableName('web_users'), 'username="' . $data['username'] . '"'));
				if($username) {
					$this->error['username'] = 'Данный логин (' . $username . ') уже занят.';
				}
			}
		}

		if(mb_strlen($data['email']) > 96 || !$this->mail_validate($data['email'])) {
			$this->error['email'] = 'Проверьте правильность электронного адреса.';
		} else {
			$email = $this->modx->db->getValue($this->modx->db->select('email', $this->modx->getFullTableName('web_user_attributes'), 'email="' . $data['email'] . '"'));
			if($email) {
				$this->error['email'] = 'Данный адрес электронной почты (' . $email . ') уже занят.';
			}
		}

		if(isset($data['phone']) && (mb_strlen($data['phone']) < 6 || mb_strlen($data['phone']) > 32)) {
			$this->error['phone'] = 'Укажите телефон в формате +7 (xxx) xxx-xx-xx.';
		} else {
			if(!empty($data['phone']) && !$this->phone_validate($data['phone'])) {
				$this->error['phone'] = 'Неверный формат ' . $data['phone'] . ', укажите номер в формате +7 (xxx) xxx-xx-xx.';
			}
		}

		if(isset($data['mobilephone']) && (mb_strlen($data['mobilephone']) < 6 || mb_strlen($data['mobilephone']) > 32)) {
			$this->error['mobilephone'] = 'Укажите мобильный телефон в формате +7 (xxx) xxx-xx-xx.';
		} else {
			if(!empty($data['mobilephone']) && !$this->phone_validate($data['mobilephone'])) {
				$this->error['mobilephone'] = 'Неверный формат ' . $data['mobilephone'] . ', укажите номер в формате +7 (xxx) xxx-xx-xx.';
			}
		}

		if(isset($data['dob']) && empty($data['dob'])) {
			$this->error['dob'] = 'Укажите дату рождения.';
		} else {
			if(!empty($data['dob']) && !$this->date_validate($data['dob'])) {
				$this->error['dob'] = 'Неверный формат даты.';
			}
		}

		if(isset($data['gender']) && empty($data['gender'])) {
			$this->error['gender'] = 'Укажите ваш пол.';
		}

		if(isset($data['country']) && empty($data['country'])) {
			$this->error['country'] = 'Укажите страну.';
		}

		if(isset($data['city']) && (mb_strlen($data['city']) < 2 || mb_strlen($data['city']) > 128)) {
			$this->error['city'] = 'Укажите город.';
		}

		if(!empty($_FILES['photo']['tmp_name'])) {
			$info = getimagesize($_FILES['photo']['tmp_name']);
			$types = array(
				'image/gif',
				'image/png',
				'image/jpeg',
				'image/jpg'
			);
			$size = 102400;
			if(!in_array($info['mime'], $types)) {
				$this->error['photo'] = 'Выберите файл изображения. Неверный формат файла.';
			} else if($_FILES['photo']['size'] >= $size) {
				$this->error['photo'] = 'Файл изображения превышает допустимые размеры.';
			}
		}

		if(mb_strlen($data['password']) < 6 || mb_strlen($data['password']) > 20) {
			$this->error['password'] = 'Пароль должен содержать не менее 6 и не более 20 знаков.';
		}

		if(isset($data['confirm'])) {
			if(!empty($data['confirm']) && $data['confirm'] !== $data['password']) {
				$this->error['confirm'] = 'Пароли должны совпадать.';
			} else if(empty($data['confirm'])) {
				$this->error['confirm'] = 'Не заполнено подверждение пароля.';
			}
		}

		if(isset($data['captcha_' . $data['keyVeriWord']]) && $_SESSION['veriword_' . md5($data['keyVeriWord'])] !== $data['captcha_' . $data['keyVeriWord']]) {
			$this->error['captcha_' . $data['keyVeriWord']] = 'Неверный проверочный код.';
		}

		if(isset($data['captcha']) && $_SESSION['veriword'] !== $data['captcha']) {
			$this->error['captcha'] = 'Неверный проверочный код.';
		}

		if(isset($data['custom_field'])) {
			if(isset($data['ajax'])) {
				$this->custom_field_validate_ajax($data['custom_field']);
			} else {
				$this->custom_field_validate($data['custom_field']);
			}
		}

		return !$this->error;
	}

	/**
	 * custom filed validate ajax
	 * @param $data
	 * @param array $parents
	 */
	private function custom_field_validate_ajax($data, $parents = array()) {
		foreach($data as $key => $value) {
			$group = $parents;
			array_push($group, $key);
			if(is_array($value)) {
				$this->custom_field_validate_ajax($value, $group);
				continue;
			}
			if(!empty($parents)) {
				if(empty($value)) {
					$this->error['custom_field[' . implode('][', $group) . ']'] = 'Не заполнено.';
				}
				continue;
			}
		}
	}

	/**
	 * custom filed validate
	 * @param $data
	 * @return array|string
	 */
	private function custom_field_validate($data) {
		if(is_array($data)) {
			foreach($data as $key => $value) {
				$data[$key] = $this->custom_field_validate($value);
				$this->error['custom_field'] = $data;
			}
		} else {
			if(empty($data)) {
				$data = 'Не заполнено.';
			} else {
				$data = '';
			}
		}
		return $data;
	}

	/**
	 * add User
	 * @return mixed|void
	 */
	private function add($data) {

		// data format
		if(!empty($_FILES['photo']['tmp_name'])) {
			$data['photo'] = $this->image($_FILES['photo']['tmp_name'], $data['email']);
			if(!empty($data['photo_cache_path'])) {
				@unlink(MODX_BASE_PATH . $data['photo_cache_path']);
			}
		} else if(!empty($data['photo_cache'])) {
			$data['photo'] = $this->image(MODX_BASE_PATH . $data['photo_cache_path'], $data['email']);
		}

		if(!empty($data['dob'])) {
			$data['dob'] = strtotime($data['dob']);
		}
		//

		$this->user = array(
			'username' => (!empty($data['username']) ? $data['username'] : $data['email']),
			'password' => md5($data['password']),
			'cachepwd' => ''
		);

		$this->user['internalKey'] = $this->modx->db->insert($this->user, $this->modx->getFullTableName('web_users'));

		if(empty($this->user['internalKey'])) {
			$this->error['user_id'] = 'Ошибка создания пользователя.';
		}

		$this->user['email'] = $data['email'];
		$this->user['fullname'] = $data['fullname'];

		$user_attributes = array(
			'internalKey' => $this->user['internalKey'],
			'fullname' => $data['fullname'],
			'email' => $data['email'],
			'phone' => $data['phone'],
			'mobilephone' => $data['mobilephone'],
			'dob' => $data['dob'],
			'gender' => $data['gender'],
			'country' => $data['country'],
			'state' => $data['state'],
			'city' => $data['city'],
			'zip' => $data['zip'],
			'fax' => $data['fax'],
			'photo' => $data['photo'],
			'comment' => $data['comment']
		);

		$web_user_attributes = $this->modx->db->insert($user_attributes, $this->modx->getFullTableName('web_user_attributes'));

		if(empty($web_user_attributes)) {
			$this->error['web_user_attributes'] = 'Ошибка создания пользовательских данных.';
		}

		if(!empty($data['userGroupId'])) {
			$data['userGroupId'] = explode(',', $data['userGroupId']);
			foreach($data['userGroupId'] as $v) {
				$web_groups = $this->modx->db->query("REPLACE INTO " . $this->modx->getFullTableName('web_groups') . " (webgroup, webuser) VALUES ('" . $v . "', '" . $this->user['internalKey'] . "')");
			}

			if(empty($web_groups)) {
				$this->error['web_groups'] = 'Ошибка создания группы пользователя.';
			}
		}

		if(count($data['custom_field']) > 0) {
			foreach($data['custom_field'] as $key => $value) {
				$web_custom_field = $this->modx->db->insert(array(
					'webuser' => $this->user['internalKey'],
					'setting_name' => $key,
					'setting_value' => is_array($value) ? serialize($value) : $value
				), $this->modx->getFullTableName('web_user_settings'));
			}

			if(empty($web_custom_field)) {
				$this->error['web_custom_field'] = 'Ошибка создания дополнительных данных пользователя.';
			}
		}

		return $this->user['internalKey'];
	}

	/**
	 * ajax
	 * @param $config
	 * @return string
	 */
	public function ajax($config = array()) {
		$json = array();

		if($this->getID()) {
			$json['redirect'] = $config['controllerProfile'];

		} else {
			$data['ajax'] = true;

			foreach($_POST as $key => $value) {
				$data[$this->clean($key)] = $this->clean($value);
			}

			if($data['action'] == 'register' && $this->validate($data)) {
				$userid = $this->add($data);
				if($userid && !$this->error) {
					$this->send($data, $this->modx->config['websignupemail_message']);
					$this->SessionHandler('start');
					if($config['success']) {
						$json['redirect'] = $config['success'];
					} else {
						$json['redirect'] = $config['controllerProfile'];
					}
				} else {
					$json['error'] = $this->error;
				}
			} else {
				$json['error'] = $this->error;
			}
		}

		header('content-type: application/json');
		return json_encode($json);
	}

	/**
	 * add photo
	 * @param array $config
	 * @return string
	 */
	public function add_photo($config = array()) {
		$json = array();

		if($this->getID()) {
			$json['redirect'] = $config['controllerProfile'];

		} else {
			if(!empty($_FILES['photo']['tmp_name'])) {
				$info = getimagesize($_FILES['photo']['tmp_name']);
				$types = array(
					'image/gif',
					'image/png',
					'image/jpeg',
					'image/jpg'
				);
				$size = 102400;

				if(!in_array($info['mime'], $types)) {
					$json['error'] = 'Выберите файл изображения. Неверный формат файла.';
				} else if($_FILES['photo']['size'] >= $size) {
					$json['error'] = 'Файл изображения превышает допустимые размеры.';
				} else {
					$path = $this->image($_FILES['photo']["tmp_name"], '', $this->modx->config['rb_base_url'] . 'cache/images/');
					$json['name'] = basename($path);
					$json['path'] = $path;
				}
			}
		}

		header('content-type: application/json');
		return json_encode($json);
	}


	/**
	 * delete photo
	 * @param $config
	 * @return string
	 */
	public function del_photo($config) {
		$json = array();

		if($this->getID()) {
			$json['redirect'] = $config['controllerRegister'];

		} else {
			if(!empty($config['photo'])) {
				@unlink(MODX_BASE_PATH . $config['photo']);
			}
		}

		header('content-type: application/json');
		return json_encode($json);
	}

}