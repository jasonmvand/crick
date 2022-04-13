<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<html>
	<head>
		<title>WebSocket</title>
		<style type="text/css">
			html,body {
				font:normal 0.9em arial,helvetica;
			}
			#log {
				width:600px; 
				height:300px; 
				border:1px solid #7F9DB9; 
				overflow:auto;
			}
			#msg {
				width:400px;
			}
		</style>
		<script type="text/javascript">
			var socket;
			function init() {
				var host = "ws://143.95.93.40:9000"; // SET THIS TO YOUR SERVER
				try {
					socket = new WebSocket(host);
					log("WebSocket - status " + socket.readyState);
					socket.onopen = function (msg) {
						log("Welcome - status " + this.readyState);
					};
					socket.onmessage = function (messageEvent) {
						var data = JSON.parse(messageEvent.data);
						log("Received: " + data.message);
					};
					socket.onclose = function (msg) {
						log("Disconnected - status " + this.readyState);
					};
				}
				catch ( ex ) {
					log(ex);
				}
				$("msg").focus();
			}
			function send() {
				var txt, msg;
				txt = $("msg");
				msg = txt.value;
				if ( !msg ) {
					alert("Message can not be empty");
					return;
				}
				txt.value = "";
				txt.focus();
				try {
					socket.send(JSON.stringify({data: msg}));
					log("Sent: " + msg);
				} catch ( ex ) {
					log(ex);
				}
			}
			function quit() {
				if ( socket != null ) {
					log("Goodbye!");
					socket.close();
					socket = null;
				}
			}
			function reconnect() {
				quit();
				init();
			}

			// Utilities
			function $(id) {
				return document.getElementById(id);
			}
			function log(msg) {
				$("log").innerHTML += "<br>" + msg;
			}
			function onkey(event) {
				if ( event.keyCode == 13 ) {
					send();
				}
			}
		</script>

	</head>
	<body onload="init()">
		<h3>WebSocket v2.00</h3>
		<div id="log"></div>
		<input id="msg" type="textbox" onkeypress="onkey(event)"/>
		<button onclick="send()">Send</button>
		<button onclick="quit()">Quit</button>
		<button onclick="reconnect()">Reconnect</button>
	</body>
</html>