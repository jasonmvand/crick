<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// nohup php -q /home/davan/public_html/crick/library/crick/server.php > /home/davan/shared/cache/crick/crick.output &

namespace Library\Crick;

// Setup autoload
define('Config\\DIR_APPLICATION', '/home/davan/public_html/crick/');
require_once \Config\DIR_APPLICATION . 'config/config.php';
require_once \Config\DIR_LIBRARY . 'application.php';
date_default_timezone_set(\Config\TIMEZONE);
spl_autoload_register(function($classname){ \Library\Application::import($classname); });

class Server extends \Library\WebSocket\Server {
	
	/**
	 * @var \Library\Crick\Handler
	 */
	protected $game_handler;
	protected $last_ping = 0;
	
	public function __construct($addr, $port, $bufferLength = 2048)
	{
		self::spawn_new_process(getmypid());
		$this->game_handler = new \Library\Crick\Handler();
		parent::__construct($addr, $port, $bufferLength);
	}
	
	protected function tick()
	{
		$time = time();
		if ( ( $time - $this->last_ping ) >= 10 ) {
			// every 10 seconds
			foreach ( $this->users as $user ) {
				if ( ! $user->is_authenticated() ) {
					continue;
				}
				/* @var $user \Library\WebSocket\User */
				socket_write($user->socket, $this->frame('', $user, 'ping'));
				//$this->send($user, 'ping');
				$this->last_ping = $time;
			}
			// Refresh database connection
			\Library\Database::ping();
		}
	}
			
	protected function closed($user)
	{
		$user->disconnect(); // Update connection status in database
		$this->stdout(sprintf('Client disconnected. (%s)', $user->id));
	}
	
	protected function connected($user)
	{
		/* @var $user \Library\WebSocket\User */
		if ( ! $user->is_authenticated() ) {
			$this->send_package($user, array('authentication_request' => true));
		}
		$this->stdout(sprintf('Client Connected. (%s)', $user->id));
	}
	
	protected function process($user, $message)
	{
		/* @var $user \Library\WebSocket\User */
		if ( $message AND $message != 'pong' ) { // ignore ping/pong
			try {
				$request = json_decode($message, true);
				if ( ! isset($request['action']) ) {
					return false;
				} elseif ( $request['action'] == 'authentication_response' ) {
					$this->authenticate_user($user, $request);
				} elseif ( ! $user->is_authenticated() ) {
					throw new \Exception('User has not completed authentication.');
				}
				$this->game_handler->route_request($request['action'], $this, $user, $request);
			} catch ( \Exception $e ) {
				// An error occurred
				$this->send_package($user, array('error' => $e->getMessage()));
				$this->stdout($e->getMessage());
				$this->disconnect($user->socket);
			}
		}
	}
	
	/**
	 * @param \Library\WebSocket\User $user
	 * @param array $request
	 * @return boolean
	 * @throws \Exception
	 */
	protected function authenticate_user(\Library\WebSocket\User $user, array $request)
	{
		if ( ! isset($request['username']) OR ! isset($request['authentication_token']) ) {
			throw new \Exception('Invalid authentication response. Closing connection.');
		}
		if ( ! $user->authenticate($request['username'], $request['authentication_token']) ) {
			throw new \Exception('Authentication failed. Closing connection.');
		}
		if ( $user->is_authenticated() ) {
			$this->stdout(sprintf('Client authenticated. (%s, %s)', $user->id, $user->player_id()));
			$this->send_package($user, array(
				'authentication_response' => array(
					'status' => true,
					'player_id' => $user->player_id()
				)
			));
			return true;
		}
	}
	
	/**
	 * @param \Library\WebSocket\User $user
	 * @param array $data
	 */
	public function send_package(\Library\WebSocket\User $user, array $data)
	{
		$this->send($user, json_encode($data, JSON_NUMERIC_CHECK));
	}
	
	public function users()
	{
		return $this->users;
	}
	
	// Allow only 1 instance of this script to run
	public static function spawn_new_process($pid)
	{
		$pid_file = new \Library\Primitive\File(sprintf('%s/crick.pid', rtrim(\Config\DIR_CACHE, '/')));
		if ( $pid_file->exists() ) { // If the PID file exists...
			$old_pid = $pid_file->get_contents(); // Get the old PID
			if ( posix_getpgid($old_pid) !== false ) { // Process is still running
				if ( ! posix_kill($old_pid, 15) ) { // Terminate process
					throw new \Exception(sprintf('Unable to terminate process using id: %s', $old_pid));
				}
				$proc_term_cycles = 0;
				do { // Wait for process to end
					$proc_term_cycles++;
					usleep(100000);
				} while ( posix_getpgid($old_pid) !== false AND $proc_term_cycles < 50 ); // timeout after 5s
				if ( posix_getpgid($old_pid) ) { // Process is still running after 5s
					throw new \Exception('Process shutdown timed out.');
				}
			}
		}
		$pid_file->write($pid, true); // Write new PID
		$pid_file->close(); // Close the file
		return true;
	}
	
	public static function log()
	{
		if ( func_num_args() > 1 ) {
			$arguments = func_get_args();
			vprintf(func_get_arg(0) . "\n", array_splice($arguments, 1));
		} elseif ( func_num_args() > 0 ) {
			print func_get_arg(0) . "\n";
		}
	}

}

try {
	$service = new \Library\Crick\Server("143.95.93.40","9000");
	$service->run();
	register_shutdown_function(function(){
		$file = new \Library\Primitive\File(sprintf('%s/crick.pid', rtrim(\Config\DIR_CACHE, '/')));
		if ( $file->exists() ) {
			$file->delete();
		}
	});
} catch (\Exception $e) {
	die($e->getMessage());
}