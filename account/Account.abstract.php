<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

abstract class Account {
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
	protected $error = array();
	protected $user;
	private $data = array();

	public function __construct($modx) {
		$this->modx = $modx;
	}

	public function __get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	public function __set($key, $value) {
		$this->data[$key] = $value;
	}

	/**
	 * deBug code
	 * @param $str
	 */
	public function dbug($str) {
		print('<pre>');
		print_r($str);
		print('</pre>');
	}

	/**
	 * set token
	 * @return string
	 */
	protected function setToken() {
		if(empty($_SESSION['token'])) {
			$_SESSION['token'] = uniqid('');
		}
		return $_SESSION['token'];
	}

	/**
	 * trim/striptags/escape/
	 * @param $data
	 * @return array
	 */
	protected function clean($data) {
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
	 * mail validate
	 * @param $email
	 * @return bool
	 */
	protected function mail_validate($email) {
		return preg_match('/^[^@]+@.*.[a-z]{2,15}$/i', $email) == true;
	}

	/**
	 * phone validate
	 * @param $phone
	 * @return bool
	 */
	protected function phone_validate($phone) {
		return preg_match('/^\+?[7|8][\ ]?[-\(]?\d{3}\)?[\- ]?\d{3}-?\d{2}-?\d{2}$/', $phone) == true;
	}

	/**
	 * mail validate
	 * @param $date
	 * @return bool
	 */
	protected function date_validate($date) {
		return date('d-m-Y', strtotime($date)) == $date;
	}

	/**
	 * view template
	 * @param $template
	 * @param array $data
	 * @return bool
	 * @internal param $date
	 */
	protected function view($template, $data = array()) {
		$file = MODX_BASE_PATH . $template;
		if(file_exists($file)) {
			extract($data);
			ob_start();
			require($file);
			$output = ob_get_contents();
			ob_end_clean();
		} else {
			trigger_error('Error: Could not load template ' . $file . '!');
			exit();
		}
		return $output;
	}

	/**
	 * create image
	 * @param $file
	 * @param $filename
	 * @return string
	 */
	protected function image($file, $filename = '', $path = '') {
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
				//				$filename = $filename . '.' . substr(md5(filemtime($file)), 0, 6);
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
	 * SessionHandler
	 * Starts the user session on login success. Destroys session on error or logout.
	 *
	 * @param string $directive ('start' or 'destroy')
	 * @return void
	 * @author Raymond Irving
	 * @author Scotty Delicious
	 *
	 * remeber может быть числом в секундах
	 */
	protected function SessionHandler($directive, $cookieName = 'WebLoginPE', $remember = true) {
		switch($directive) {
			case 'start':
				if($this->getID()) {
					$_SESSION['webShortname'] = $this->user['username'];
					$_SESSION['webFullname'] = $this->user['fullname'];
					$_SESSION['webEmail'] = $this->user['email'];
					$_SESSION['webValidated'] = 1;
					$_SESSION['webInternalKey'] = $this->getID();
					$_SESSION['webValid'] = base64_encode($this->user['password']);
					$_SESSION['webUser'] = base64_encode($this->user['username']);
					$_SESSION['webFailedlogins'] = $this->user['failedlogincount'];
					$_SESSION['webLastlogin'] = $this->user['lastlogin'];
					$_SESSION['webnrlogins'] = $this->user['logincount'];
					$_SESSION['webUsrConfigSet'] = array();
					$_SESSION['webUserGroupNames'] = $this->getUserGroups();
					$_SESSION['webDocgroups'] = $this->getDocumentGroups();
					if($remember) {
						$cookieValue = md5($this->user['username']) . '|' . $this->user['password'];
						$cookieExpires = time() + (is_bool($remember) ? (60 * 60 * 24 * 365 * 5) : (int) $remember);
						setcookie($cookieName, $cookieValue, $cookieExpires, '/', ($this->modx->config['server_protocol'] == 'http' ? false : true), true);
					}
				}
				break;
			case 'destroy':
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
				break;
		}
		return $this;
	}

	/**
	 * get user ID
	 * @return mixed
	 */
	protected function getID() {
		if(!empty($this->user['internalKey'])) {
			return $this->user['internalKey'];
		} else if($userid = $this->modx->getLoginUserID('web')) {
			$this->user = $this->modx->getWebUserInfo($userid);
			return $this->user['internalKey'];
		}
	}

	/**
	 * @return array
	 */
	private function getUserGroups() {
		$out = array();
		if($this->getID()) {
			$web_groups = $this->modx->getFullTableName('web_groups');
			$webgroup_names = $this->modx->getFullTableName('webgroup_names');

			$sql = "SELECT `ugn`.`name` FROM {$web_groups} as `ug`
                INNER JOIN {$webgroup_names} as `ugn` ON `ugn`.`id`=`ug`.`webgroup`
                WHERE `ug`.`webuser` = " . $this->getID();
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
		if($this->getID()) {
			$web_groups = $this->modx->getFullTableName('web_groups');
			$webgroup_access = $this->modx->getFullTableName('webgroup_access');

			$sql = "SELECT `uga`.`documentgroup` FROM {$web_groups} as `ug`
                INNER JOIN {$webgroup_access} as `uga` ON `uga`.`webgroup`=`ug`.`webgroup`
                WHERE `ug`.`webuser` = " . $this->getID();
			$sql = $this->modx->db->makeArray($this->modx->db->query($sql));

			foreach($sql as $row) {
				$out[] = $row['documentgroup'];
			}
		}
		return $out;
	}

	/**
	 * Password generate
	 *
	 * @category   generate
	 * @version   0.1
	 * @license    GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
	 * @param string $len длина пароля
	 * @param string $data правила генерации пароля
	 * @return string Строка с паролем
	 * @author Agel_Nash <Agel_Nash@xaker.ru>
	 *
	 * Расшифровка значений $data
	 * "A": A-Z буквы
	 * "a": a-z буквы
	 * "0": цифры
	 * ".": все печатные символы
	 *
	 * @example
	 * $this->genPass(10,"Aa"); //nwlTVzFdIt
	 * $this->genPass(8,"0"); //71813728
	 * $this->genPass(11,"A"); //VOLRTMEFAEV
	 * $this->genPass(5,"a0"); //4hqi7
	 * $this->genPass(5,"."); //2_Vt}
	 * $this->genPass(20,"."); //AMV,>&?J)v55,(^g}Z06
	 * $this->genPass(20,"aaa0aaa.A"); //rtvKja5xb0\KpdiRR1if
	 */
	protected function genPass($len = 8, $data = 'Aa0.') {
		$opt = strlen($data);
		$pass = array();

		for($i = $len; $i > 0; $i--) {
			switch($data[rand(0, ($opt - 1))]) {
				case 'A':
					$tmp = rand(65, 90);
					break;
				case 'a':
					$tmp = rand(97, 122);
					break;
				case '0':
					$tmp = rand(48, 57);
					break;
				default:
					$tmp = rand(33, 126);
			}
			$pass[] = chr($tmp);
		}
		$pass = implode("", $pass);
		return $pass;
	}

	/**
	 * send mail
	 */
	protected function send($data, $tpl) {

		if(empty($tpl)) {
			return $this->error['tpl'] = 'Шаблон нисьма не найден.';
		}

		$emailsender = $this->modx->config['emailsender'];
		$emailsubject = $this->modx->config['emailsubject'];
		$site_name = $this->modx->config['site_name'];
		$site_url = $this->modx->config['site_url'];

		$message = str_replace('[+uid+]', (!empty($data['username']) ? $data['username'] : $data['email']), $tpl);
		$message = str_replace('[+pwd+]', $data['password'], $message);
		$message = str_replace('[+ufn+]', $data['fullname'], $message);
		$message = str_replace('[+sname+]', $site_name, $message);
		$message = str_replace('[+semail+]', $emailsender, $message);
		$message = str_replace('[+surl+]', $site_url . ltrim($data['controllerLogin'], '/'), $message);

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
}