<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* @var $session \Library\Client\Session */

?>
<style>
body { background-color: #FFD; }
#login-form {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	font-size: 1.5em;
	font-family: Verdana, sans-serif;
}
#login-form fieldset {
	box-sizing: border-box;
	padding: 1em;
	border: solid 0.125em #CCC;
	border-radius: 0.5em;
	box-shadow: 0.25em 0.25em 0.5em #444;
	background-color: #FFF;
}
#login-form legend {
    font-size: 0.75em;
    padding: 0.5em;
    border-radius: 0.5em;
    line-height: 0.75em;
    vertical-align: top;
    background-color: #EEE;
}
#login-form label {
	font-weight: bold;
}
#login-form .username,
#login-form .password,
#login-form .register {
	margin-bottom: 0.25em;
}
#login-form .username input,
#login-form .password input {
    box-sizing: border-box;
    border: none;
    border-radius: 0.25em;
    height: 1.25em;
    padding: 0.125em;
    vertical-align: middle;
    margin: 0px;
    font-size: inherit;
    background-color: #EEE;
}
#login-form .register {
	font-size: 0.75em;
	text-align: right;
	font-style: italic;
}
#login-form .register input {
    margin: 0px;
    padding: 0px;
    box-sizing: border-box;
    vertical-align: middle;
    height: 1em;
    width: 1em;
    font-size: inherit;
	cursor: pointer;
}
#login-form .submit {
	text-align: right;
}
#login-form .submit button {
    font-size: inherit;
    box-sizing: border-box;
    border-radius: 0.25em;
    border: outset 0.125em;
	background-color: #CCC;
    padding: 0.125em 0.5em;
    margin: 0px;
    cursor: pointer;
}
#login-form .submit button:hover,
#login-form .submit button:active {
	border-style: inset;
	background-color: #EEE;
}
</style>
<div id="login-form">
	<form action="<?php echo $action ?>" method="post" enctype="multipart/form-data">
		<fieldset>
			<legend>Log In</legend>
			<div class="username">
				<label>Email Address: <input type="email" name="login[username]" placeholder="some_one@email.com" /></label>
			</div>
			<div class="password">
				<label>Password: <input type="password" name="login[password]" placeholder="super good password" /></label>
			</div>
			<div class="register">
				<label>Register Account <input type="checkbox" name="login[register]" /></label>
			</div>
			<div class="submit">
				<button type="submit">Continue</button>
			</div>
		</fieldset>
<?php if ( $session->has_value('notification', 'login', 'content') ): ?>
		<p id="message"><?php echo $session->get('notification', 'login', 'content'); ?></p>
<?php endif; ?>
	</form>
</div>
<?php 

$session->clear('notification', 'login');