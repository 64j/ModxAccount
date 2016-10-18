<?

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

require_once(dirname(dirname(__FILE__)) . '/Account.abstract.php');

class AccountControllerProfile extends Account {

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
			foreach($this->user as $key => $value) {
				if(!empty($value)) {
					$data[$key] = $value;
				}
			}
		} else {
			$this->modx->sendRedirect($config['controllerRegister']);
		}

		if(!empty($data['dob'])) {
			$data['dob'] = date('d-m-Y', $data['dob']);
		}

		if(!empty($data['photo'])) {
			$data['photo_no_cache'] = $data['photo'] . '?time=' . time();
		}

		foreach($_POST as $key => $value) {
			$data[$this->clean($key)] = $this->clean($value);
		}

		if($_REQUEST['action'] == 'logout') {
			$data['action'] = 'logout';
		}

		$data['controllerLogout'] = $config['controller'] . '?action=logout';

		if($data['action'] == 'save' && $this->validate($data)) {
			$this->save($data);

			if($config['success']) {
				$this->modx->sendRedirect($config['success']);
			} else {
				$this->modx->sendRedirect($config['controllerProfile']);
			}

		} else if($data['action'] == 'logout') {
			$this->logout();
			$this->modx->sendRedirect($config['controllerLogin']);
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

		echo $this->view('assets/snippets/account/view/profile.tpl', $data);
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
				$username = $this->modx->db->getValue($this->modx->db->select('username', $this->modx->getFullTableName('web_users'), 'username="' . $data['username'] . '" AND username!="' . $this->user['username'] . '"'));
				if($username) {
					$this->error['username'] = 'Данный логин (' . $username . ') уже занят.';
				}
			}
		}

		if(mb_strlen($data['email']) > 96 || !$this->mail_validate($data['email'])) {
			$this->error['email'] = 'Проверьте правильность электронного адреса.';
		} else {
			$email = $this->modx->db->getValue($this->modx->db->select('email', $this->modx->getFullTableName('web_user_attributes'), 'email="' . $data['email'] . '" AND email!="' . $this->user['email'] . '"'));
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

		if(empty($data['dob'])) {
			$this->error['dob'] = 'Укажите дату рождения.';
		} else {
			if(!empty($data['dob']) && !$this->date_validate($data['dob'])) {
				$this->error['dob'] = 'Неверный формат даты.';
			}
		}

		if(empty($data['gender'])) {
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

		if(!empty($data['password']) && (mb_strlen($data['password']) < 6 || mb_strlen($data['password']) > 20)) {
			$this->error['password'] = 'Пароль должен содержать не менее 6 знаков.';
		}

		if(!empty($data['confirm']) && $data['confirm'] !== $data['password']) {
			$this->error['confirm'] = 'Пароли должны совпадать.';
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
	 * save data
	 * @param $data
	 * @return mixed
	 */
	private function save($data) {

		// data format
		if(!empty($_FILES['photo']['tmp_name'])) {
			$data['photo'] = $this->image($_FILES['photo']['tmp_name'], $data['email']);
		}

		if(!empty($data['photo_delete'])) {
			@unlink(MODX_BASE_PATH . $this->user['photo']);
			$data['photo'] = '';
		}

		if(!empty($data['dob'])) {
			$data['dob'] = strtotime($data['dob']);
		}

		if($this->user['username'] == $this->user['email']) {
			$data['username'] = $data['email'];
		}

		if(!empty($data['password'])) {
			$this->send($data, $this->modx->config['websignupemail_message']);
			$data['password'] = md5($data['password']);
		}
		//

		// user
		$user = array();
		foreach($this->default_field['user'] as $key => $value) {
			if(!empty($data[$key])) {
				$user[$key] = $data[$key];
			}
		}
		if(count($user) > 0) {
			$this->modx->db->update($user, $this->modx->getFullTableName('web_users'), 'id=' . $this->getID());
		}

		// attribute
		$attribute = array();
		foreach($this->default_field['attribute'] as $key => $value) {
			if(isset($data[$key])) {
				$attribute[$key] = $data[$key];
			}
		}
		if(count($attribute) > 0) {
			$this->modx->db->update($attribute, $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->getID());
		}

		// custom field
		if(count($data['custom_field']) > 0) {
			foreach($data['custom_field'] as $key => $value) {
				if($key == 'address') {
					$this->modx->db->insert(array(
						'webuser' => $this->getID(),
						'setting_name' => $key,
						'setting_value' => is_array($value) ? serialize($value) : $value
					), $this->modx->getFullTableName('web_user_settings'));
				} else {
					$this->modx->db->query("REPLACE INTO " . $this->modx->getFullTableName('web_user_settings') . " (setting_name, setting_value) 
					VALUES ('" . $key . "', 
					'" . (is_array($value) ? serialize($value) : $value) . "')
					WHERE id=" . $this->getID());
				}
			}
		}

		return $this->getID();
	}

	/**
	 * login out
	 */
	public function logout() {
		if($this->getID()) {
			$this->modx->db->update(array(
				'lastlogin' => time(),
				'thislogin' => 0
			), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->getID());
		}
		$this->SessionHandler('destroy');
	}

	/**
	 * ajax
	 * @param $config
	 * @return string
	 */
	public function ajax($config) {
		$json = array();

		if($this->getID()) {
			$data['ajax'] = true;

			foreach($_POST as $key => $value) {
				$data[$this->clean($key)] = $this->clean($value);
			}

			if($data['action'] == 'save' && $this->validate($data)) {
				$userid = $this->save($data);

				if($userid && !$this->error) {
					$json['success']['password'] = '';
					$json['success']['confirm'] = '';
					if($config['success']) {
						$json['redirect'] = $config['success'];
					}

				} else {
					$json['error'] = $this->error;

				}

			} else if($data['action'] == 'logout') {
				$this->logout();
				$json['redirect'] = $config['controllerLogin'];

			} else {
				$json['error'] = $this->error;

			}
		} else {
			$json['redirect'] = $config['controllerRegister'];

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
					$path = $this->image($_FILES['photo']["tmp_name"], $this->user['email']);
					@unlink(MODX_BASE_PATH . $this->user['photo']);
					$this->modx->db->update(array(
						'photo' => $path
					), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->getID());
					$json['name'] = basename($path);
					$json['path'] = $path;
				}
			}
		} else {
			$json['redirect'] = $config['controllerRegister'];
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
			if(!empty($this->user['photo'])) {
				@unlink(MODX_BASE_PATH . $this->user['photo']);
				$this->modx->db->update(array(
					'photo' => ''
				), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->getID());
			}
		} else {
			$json['redirect'] = $config['controllerRegister'];
		}

		header('content-type: application/json');
		return json_encode($json);
	}

}