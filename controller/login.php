<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Controller;

class Login extends \Library\Application\Controller {

	const PASSWORD_SALT_LENGTH = 16;
	const PASSWORD_HASH_FUNCTION = 'sha256';
	
	public function index()
	{
		$output = new \View\Webpage($this->client, array(
			'title' => 'Please Log In',
			'meta' => array(
				array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8'),
				array('name' => 'description', 'content' => 'log in form')
			),
			'link' => array(
				array('rel' => 'shortcut icon', 'href' => \Library\URL::get('http://static.davandisplay.com/common/favicon.ico'))
			),
			'script' => array()
		));
		$output->render('Template/Login_form', array(
			'action' => \Library\URL::get('login/submit'),
			'session' => $this->client->session
		));
	}

	public function submit()
	{
		try {
			if ( ! $this->client->request->has_value('login', 'username') ) {
				throw new \Exception('Please enter your username.');
			}
			if ( ! $this->client->request->has_value('login', 'password') ) {
				throw new \Exception('Please enter your password.');
			}
			if ( $this->client->request->has_value('login', 'register') ) {
				$this->do_register(
					trim($this->client->request->get('login', 'username')),
					trim($this->client->request->get('login', 'password'))
				);
			} else {
				$this->do_login(
					trim($this->client->request->get('login', 'username')),
					trim($this->client->request->get('login', 'password'))
				);
			}
			\Library\URL::redirect(\Config\HTTP_HOST);
		} catch ( \Exception $e ) {
			$this->client->session->set('notification', 'login', array(
				'status' => false,
				'type' => 'failure',
				'content' => $e->getMessage()
			));
			\Library\URL::redirect('login');
		}
	}

	private function do_login($username, $password)
	{
		$user = new \Library\Database\Record\User($username);
		if ( ! $user->database_record_exists() ) {
			throw new \Exception('That user does not exist.');
		}
		$pass = new \Library\Primitive\Password($password, self::PASSWORD_SALT_LENGTH, self::PASSWORD_HASH_FUNCTION);
		if ( ! $pass->compare($user->password_salt(), $user->password_hash()) ) {
			throw new \Exception('Username or password is incorrect.');
		}
		$authentication = md5(microtime());
		$this->client->session->set('login-state', true);
		$this->client->session->set('user_id', $user->user_id());
		$this->client->session->set('username', $user->email());
		$this->client->session->set('authentication', $authentication);
		$user->authentication($authentication);
	}

	private function do_register($username, $password)
	{
		$user = new \Library\Database\Record\User($username);
		if ( $user->database_record_exists() ) {
			throw new \Exception('That user already exists.');
		}
		$pass = new \Library\Primitive\Password($password, self::PASSWORD_SALT_LENGTH, self::PASSWORD_HASH_FUNCTION);
		$user->email($username);
		$user->password_hash($pass->hash);
		$user->password_salt($pass->salt);
		$authentication = md5(microtime());
		$this->client->session->set('login-state', true);
		$this->client->session->set('user_id', $user->user_id());
		$this->client->session->set('username', $user->email());
		$this->client->session->set('authentication', $authentication);
		$user->authentication($authentication);
	}

}
