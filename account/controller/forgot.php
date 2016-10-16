<?php

if(!defined('MODX_BASE_PATH')) {
	die('Unauthorized access.');
}

class ControllerAccountControllerForgot extends Loader {
	private $error = array();
	private $user;

	/**
	 * render form
	 * @param $config
	 */
	public function index($config) {
		$data = $config;
		$data['json_config'] = json_encode($config);

		if($this->modx->getLoginUserID('web')) {
			$this->modx->sendRedirect($config['controllerProfile']);
		}

		foreach($_POST as $key => $value) {
			$data[$this->clean($key)] = $this->clean($value);
		}

		switch($data['action']) {
			case 'forgot':
				if($this->validate($data)) {
					$this->forgot($data);
					if($config['success']) {
						$this->modx->sendRedirect($config['success']);
					} else {
						$this->modx->sendRedirect($config['controllerLogin']);
					}
				}
				break;

			case 'login':
				$this->modx->sendRedirect($config['controllerLogin']);
				break;

			case 'register':
				$this->modx->sendRedirect($config['controllerRegister']);
				break;
		}

		foreach($this->error as $key => $value) {
			$data['error_' . $key] = $value;
		}

		echo $this->modx->load->view('assets/snippets/account/view/forgot.tpl', $data);
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
	 * mail validate
	 * @param $email
	 * @return bool
	 */
	private function mail_validate($email) {
		return preg_match('/^[^@]+@.*.[a-z]{2,15}$/i', $email) == true;
	}

	/**
	 * reset password
	 * @param $data
	 */
	private function forgot($data) {
		$data['cachepwd'] = $this->genPass(10, 'Aa0');

		$this->modx->db->update(array(
			'cachepwd' => md5($data['cachepwd'])
		), $this->modx->getFullTableName('web_users'), 'id=' . $this->user['internalKey']);

		foreach($this->user as $key => $value) {
			if(!empty($value)) {
				$data[$key] = $value;
			}
		}

		$this->send($data);
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
	private function genPass($len = 8, $data = 'Aa0.') {
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
	private function send($data) {

		$message_tpl = $this->modx->config['webpwdreminder_message'];
		$emailsender = $this->modx->config['emailsender'];
		$emailsubject = $this->modx->config['emailsubject'];
		$site_name = $this->modx->config['site_name'];
		$site_url = $this->modx->config['site_url'];

		$message = str_replace('[+uid+]', (!empty($data['username']) ? $data['username'] : $data['email']), $message_tpl);
		$message = str_replace('[+pwd+]', $data['cachepwd'], $message);
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

	public function ajax($config) {
		$json = array();

		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			if($userid = $this->modx->getLoginUserID('web')) {
				$json['redirect'] = $config['controllerProfile'];
			} else {

				$data['ajax'] = true;

				foreach($_POST as $key => $value) {
					$data[$this->clean($key)] = $this->clean($value);
				}

				switch($data['action']) {
					case 'forgot':
						if($this->validate($data)) {
							$this->forgot($data);
							if($config['success']) {
								$json['redirect'] = $config['success'];
							} else {
								$json['redirect'] = $config['controllerLogin'];
							}
						} else {
							$json['error'] = $this->error;
						}
						break;

					case 'login':
						$json['redirect'] = $config['controllerLogin'];
						break;

					case 'register':
						$json['redirect'] = $config['controllerRegister'];
						break;
				}
			}
		} else {
			$this->modx->sendRedirect('/');
		}

		header('Content-Type: application/json');
		echo json_encode($json);
	}
}