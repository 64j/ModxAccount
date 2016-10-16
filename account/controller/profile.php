<?

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

class ControllerAccountControllerProfile extends Loader {
	protected $default_field = array(
		'user' => array(
			'username' => null,
			'password' => null
		),
		'attribute' => array(
			'fullname' => null,
			'email' => null,
			'phone' => null,
			'mobilephone' => null,
			'dob' => null,
			'gender' => null,
			'country' => null,
			'state' => null,
			'city' => null,
			'zip' => null,
			'fax' => null,
			'photo' => null,
			'comment' => null
		)
	);
	private $error = array();
	private $user;

	/**
	 * render form
	 * @param $config
	 */
	public function index($config = array()) {
		$data = $config;
		$data['json_config'] = json_encode($config);

		if($userid = $this->modx->getLoginUserID('web')) {
			foreach($this->modx->getWebUserInfo($userid) as $key => $value) {
				if(!empty($value)) {
					$data[$key] = $value;
					$this->user[$key] = $value;
				}
			}
		} else {
			$this->modx->sendRedirect($config['controllerRegister']);
		}

		if(!empty($data['dob'])) {
			$data['dob'] = date('d-m-Y', $data['dob']);
		}

		foreach($_POST as $key => $value) {
			$data[$this->clean($key)] = $this->clean($value);
		}

		if($_REQUEST['action'] == 'logout') {
			$data['action'] = 'logout';
		}
		$data['controllerLogout'] = $this->modx->makeUrl($this->modx->documentIdentifier) . '?action=logout';

		switch($data['action']) {
			case 'save':
				if($this->validate($data)) {
					$this->save($data);
					if($config['success']) {
						$this->modx->sendRedirect($config['success']);
					} else {
						$this->modx->sendRedirect($config['controllerProfile']);
					}
				}
				break;

			case 'logout':
				$this->logout();
				$this->modx->sendRedirect($config['controllerLogin']);
				break;
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

		echo $this->modx->load->view('assets/snippets/account/view/profile.tpl', $data);
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
	 * mail validate
	 * @param $email
	 * @return bool
	 */
	private function mail_validate($email) {
		return preg_match('/^[^@]+@.*.[a-z]{2,15}$/i', $email) == true;
	}

	/**
	 * phone validate
	 * @param $phone
	 * @return bool
	 */
	private function phone_validate($phone) {
		return preg_match('/^\+?[7|8][\ ]?[-\(]?\d{3}\)?[\- ]?\d{3}-?\d{2}-?\d{2}$/', $phone) == true;
	}

	/**
	 * mail validate
	 * @param $date
	 * @return bool
	 */
	private function date_validate($date) {
		return date('d-m-Y', strtotime($date)) == $date;
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
			$data['photo'] = $this->image($_FILES['photo']['tmp_name']);
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
			$this->send($data);
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
			$this->modx->db->update($user, $this->modx->getFullTableName('web_users'), 'id=' . $this->user['internalKey']);
		}

		// attribute
		$attribute = array();
		foreach($this->default_field['attribute'] as $key => $value) {
			if(isset($data[$key])) {
				$attribute[$key] = $data[$key];
			}
		}
		if(count($attribute) > 0) {
			$this->modx->db->update($attribute, $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->user['internalKey']);
		}

		// custom field
		if(count($data['custom_field']) > 0) {
			foreach($data['custom_field'] as $key => $value) {
				if($key == 'address') {
					$this->modx->db->insert(array(
						'webuser' => $this->user['internalKey'],
						'setting_name' => $key,
						'setting_value' => is_array($value) ? serialize($value) : $value
					), $this->modx->getFullTableName('web_user_settings'));
				} else {
					$this->modx->db->query("REPLACE INTO " . $this->modx->getFullTableName('web_user_settings') . " (setting_name, setting_value) 
					VALUES ('" . $key . "', 
					'" . (is_array($value) ? serialize($value) : $value) . "')
					WHERE id=" . $this->user['internalKey']);
				}
			}
		}

		return $this->user['internalKey'];

	}

	/**
	 * create image
	 * @param $file
	 * @param $filename
	 * @return string
	 */
	private function image($file, $filename = '', $path = '') {
		$url = '';
		$thumb_width = 100;
		$thumb_height = 100;

		if(file_exists($file)) {

			$info = getimagesize($file);
			$width = $info[0];
			$height = $info[1];
			$mime = isset($info['mime']) ? $info['mime'] : '';

			if($mime == 'image/gif') {
				$image = imagecreatefromgif($file);
			} else if($mime == 'image/png') {
				$image = imagecreatefrompng($file);
			} else if($mime == 'image/jpeg') {
				$image = imagecreatefromjpeg($file);
			} else {
				$image = imagecreatefromjpeg($file);
			}

			if(($width / $height) >= ($thumb_width / $thumb_height)) {
				$new_height = $thumb_height;
				$new_width = $width / ($height / $thumb_height);
			} else {
				$new_width = $thumb_width;
				$new_height = $height / ($width / $thumb_width);
			}

			$xpos = 0 - ($new_width - $thumb_width) / 2;
			$ypos = 0 - ($new_height - $thumb_height) / 2;

			$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
			imagecopyresampled($thumb, $image, $xpos, $ypos, 0, 0, $new_width, $new_height, $width, $height);

			if(!file_exists($this->modx->config['rb_base_url'] . 'images/users')) {
				mkdir($this->modx->config['base_path'] . $this->modx->config['rb_base_url'] . 'images/users', 0755, true);
			}

			if(empty($filename)) {
				$filename = md5(filemtime($file));
			} else {
				$filename = $filename . '.' . substr(md5(filemtime($file)), 0, 3);
			}

			if(empty($path)) {
				$path = $this->modx->config['rb_base_url'] . 'images/users/';
			}

			$ext = '.jpg';
			$url = $path . $filename . $ext;
			$filename = $this->modx->config['base_path'] . $path . $filename . $ext;

			@unlink($file);

			imagejpeg($thumb, $filename, '100');
			imagedestroy($thumb);
			imagedestroy($image);

		} else {

			$this->error['photo'] = 'Ошибка создания изображения ' . $file . '.';

		}

		return $url;
	}

	/**
	 * send mail
	 * @param array $data
	 */
	private function send($data = array()) {

		$message_tpl = $this->modx->config['websignupemail_message'];
		$emailsender = $this->modx->config['emailsender'];
		$emailsubject = $this->modx->config['emailsubject'];
		$site_name = $this->modx->config['site_name'];
		$site_url = $this->modx->config['site_url'];

		$message = str_replace('[+uid+]', (!empty($data['username']) ? $data['username'] : $data['email']), $message_tpl);
		$message = str_replace('[+pwd+]', $data['password'], $message);
		$message = str_replace('[+ufn+]', $data['fullname'], $message);
		$message = str_replace('[+sname+]', $site_name, $message);
		$message = str_replace('[+semail+]', $emailsender, $message);
		$message = str_replace('[+surl+]', $site_url, $message);

		foreach($data as $name => $value) {
			$message = str_replace('[+post.' . $name . '+]', $value, $message);
		}

		// Bring in php mailer!
		require_once MODX_MANAGER_PATH . 'includes/controls/class.phpmailer.php';
		$mail = new PHPMailer();
		$mail->CharSet = $this->modx->config['modx_charset'];
		$mail->From = $emailsender;
		$mail->FromName = $site_name;
		$mail->Subject = $emailsubject;
		$mail->Body = $message;
		$mail->addAddress($data['email'], $data['fullname']);

		if(!$mail->send()) {
			$this->error['send_mail'] = 'Ошибка отправки письма.';
		}
	}

	/**
	 * login out
	 */
	public function logout() {
		if($userid = $this->modx->getLoginUserID('web')) {
			$this->modx->db->update(array(
				'lastlogin' => time(),
				'thislogin' => 0
			), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $userid);
		}
		$this->SessionHandlerDestroy();
	}


	/**
	 * SessionHandlerDestroy
	 * @param string $cookieName
	 */
	private function SessionHandlerDestroy($cookieName = 'WebLoginPE') {
		if(isset($_SESSION['mgrValidated'])) {
			unset($_SESSION['webShortname']);
			unset($_SESSION['webFullname']);
			unset($_SESSION['webEmail']);
			unset($_SESSION['webValidated']);
			unset($_SESSION['webInternalKey']);
			unset($_SESSION['webValid']);
			unset($_SESSION['webUser']);
			unset($_SESSION['webFailedlogins']);
			unset($_SESSION['webLastlogin']);
			unset($_SESSION['webnrlogins']);
			unset($_SESSION['webUsrConfigSet']);
			unset($_SESSION['webUserGroupNames']);
			unset($_SESSION['webDocgroups']);

			setcookie($cookieName, '', time() - 60, '/');
		} else {
			if(isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time() - 60, '/');
			}
			setcookie($cookieName, '', time() - 60, '/');
			session_destroy();
		}
	}

	/**
	 * ajax
	 * @param $config
	 */
	public function ajax($config) {
		$json = array();

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($userid = $this->modx->getLoginUserID('web')) {

				$data['ajax'] = true;

				foreach($this->modx->getWebUserInfo($userid) as $key => $value) {
					if(!empty($value)) {
						$this->user[$key] = $value;
					}
				}

				foreach($_POST as $key => $value) {
					$data[$this->clean($key)] = $this->clean($value);
				}

				switch($data['action']) {
					case 'save':
						if($this->validate($data)) {
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
						} else {
							$json['error'] = $this->error;
						}
						break;

					case 'logout':
						$this->logout();
						$json['redirect'] = $config['controllerLogin'];
						break;
				}
			} else {
				$json['redirect'] = $config['controllerRegister'];
			}
		} else {
			$this->modx->sendRedirect('/');
		}

		header('Content-Type: application/json');
		echo json_encode($json);
	}

	/**
	 * add photo
	 * @param array $config
	 */
	public function add_photo($config = array()) {
		$json = array();

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($userid = $this->modx->getLoginUserID('web')) {

				$this->user = $this->modx->getWebUserInfo($userid);

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
						$path = $this->image($_FILES['photo']["tmp_name"]);
						@unlink(MODX_BASE_PATH . $this->user['photo']);
						$this->modx->db->update(array(
							'photo' => $path
						), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->user['internalKey']);
						$json['name'] = basename($path);
						$json['path'] = $path;
					}
				}
			} else {
				$json['redirect'] = $config['controllerRegister'];
			}
		} else {
			$this->modx->sendRedirect('/');
		}

		header('Content-Type: application/json');
		echo json_encode($json);
	}

	/**
	 * delete photo
	 * @param $config
	 */
	public function del_photo($config) {
		$json = array();

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($userid = $this->modx->getLoginUserID('web')) {

				$this->user = $this->modx->getWebUserInfo($userid);

				if(!empty($this->user['photo'])) {
					@unlink(MODX_BASE_PATH . $this->user['photo']);
					$this->modx->db->update(array(
						'photo' => ''
					), $this->modx->getFullTableName('web_user_attributes'), 'id=' . $this->user['internalKey']);
				}
			} else {
				$json['redirect'] = $config['controllerRegister'];
			}
		} else {
			$this->modx->sendRedirect('/');
		}

		header('Content-Type: application/json');
		echo json_encode($json);
	}

}