<?php

/* 
 * client
 * Copyright (C) 2015 Jason Van Dyke <jason@yellowlabwebdesign.com>.
 * Unauthorized redistribution of this file, in part or in in whole, is strictly prohibited.
 */

namespace Library;

class Client {
	
	/**
	 * @var \Library\Client\Request
	 */
	public $request;
	
	/**
	 * @var \Library\Client\Session
	 */
	public $session;
	
	/**
	 * @var \Library\Client\Account
	 */
	public $account;
	
	/**
	 * @var \Library\Client\Order
	 */
	public $order;
	
	public function __construct()
	{
		
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL & ~E_NOTICE);
		
		\Library\Application::set_client($this);
		$this->request = new \Library\Client\Request();
		$this->session = new \Library\Client\Session($this->request);
		$this->account = new \Library\Client\Account(\Library\Client\Account::get_account_id($this->session));
		
		/*// If the order object exists within the session, restore it, otherwise create a new order object
		if ( $this->session->has_value(\Library\Client\Order::SESSION_NAME) AND $this->session->get(\Library\Client\Order::SESSION_NAME) instanceof \Library\Client\Order ) {
			$this->order = $this->session->get(\Library\Client\Order::SESSION_NAME);
		} else {
			$this->order = new \Library\Client\Order(array(
					new \Library\Checkout_Step\Account(),
					new \Library\Checkout_Step\Shipping(),
					new \Library\Checkout_Step\Delivery(),
					new \Library\Checkout_Step\Payment()
				), array(
					new \Library\Distributor\Davan()
				), array(
					new \Library\Carrier\UPS()
				), array(
					new \Library\Payment_Method\Net1()
				)
			);
		}
		\Library\Application\Shutdown::register(new \Library\Primitive\Callback($this, 'save_order_data'));
		//*/
		
		\Library\Event_Handler::create_event($this, 'client.load');
	}
	
	/*//
	public function save_order_data()
	{
		$this->session->set(\Library\Client\Order::SESSION_NAME, $this->order);
	}
	//*/
	
}