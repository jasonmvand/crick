<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/* @var $session \Library\Client\Session */

$smack_talk_phrase = array(
	'Belittle your adversaries...', 'Trash-talk your opponents...', 'Disparage your rivals...',
	'Smack-talk your competitors...', 'Bad-mouth your foes...', 'Irritate your enemies...',
	'Annoy your assailants...', 'Aggravate your challengers...', 'Bother your contestants...'
);

?>
<div id="crick-board-container">
	<div id="crick-start" style="display: none; z-index: 99; position: absolute;"><button>Start Game</button></div>
	<canvas id="crick-board-canvas" width="0" height="0"></canvas>
</div>
<div id="crick-player-container">
	<div id="player-bin"></div>
	<div id="chat-input"><input type="text" placeholder="<?php echo $smack_talk_phrase[array_rand($smack_talk_phrase)]; ?>" /></div>
	<div id="chat-bin"></div>
</div>
<div id="template" style="display: none">
	<div class="player">
		<div class="color-swatch"></div>
		<div class="dice-bin"></div>
		<div class="name"><span></span></div>
	</div>
	<div class="chat-message">
		<span class="name"></span>
		<p class="message"></p>
	</div>
</div>
<style>#crick-board-container .target { visibility: visible; }</style>
<script>
	window.crick = {
		actions: [],
		actors: {},
		authentication_token: '',
		boardPadding: 0.02,
		canvas: document.getElementById('crick-board-canvas'),
		holeArea: 1/15,
		holeMargin: 0.25,
		holeSize: 0.5,
		moves: {},
		player_id: '',
		players: {},
		username: '',
		websocket: false,
		start: function(username, authentication_token, ws_address)
		{
			window.crick.username = username;
			window.crick.authentication_token = authentication_token;
			window.crick.canvas.height = Math.min(window.innerHeight, window.innerWidth);
			window.crick.canvas.width = window.crick.canvas.height;
			window.crick.boardPadding = window.crick.boardPadding * window.crick.canvas.width;
			window.crick.holeArea = window.crick.holeArea * ( window.crick.canvas.width - window.crick.boardPadding * 2 );
			window.crick.holeSize = window.crick.holeSize * window.crick.holeArea;
			window.crick.holeMargin = window.crick.holeMargin * window.crick.holeArea;
			window.crick.websocket = new WebSocket(ws_address);
			window.crick.websocket.onopen = function(event){};
			window.crick.websocket.onclose = function(event){};
			window.crick.websocket.onmessage = function(event){ 
				if ( event.data == 'ping' ) {
					window.crick.websocket.send('pong');
				} else {
					window.crick.parse_message(JSON.parse(event.data));
				}
			};
		},
		// WEBSOCKETS
		send_message: function(data)
		{
			window.crick.websocket.send(JSON.stringify(data));
		},
		parse_message: function(data)
		{
			if ( data.hasOwnProperty('error') ) {
				alert(data['error']);
				window.crick.websocket.close();
				return;
			}
			if ( data.hasOwnProperty('authentication_request') ) {
				window.crick.send_message({
					action: 'authentication_response',
					username: window.crick.username,
					authentication_token: window.crick.authentication_token
				});
			}
			if ( data.hasOwnProperty('authentication_response') ) {
				window.crick.player_id = data.authentication_response.player_id;
				window.crick.send_message({ action: 'join' });
			}
			if ( data.hasOwnProperty('players') ) {
				for ( var i in data.players ) {
					if ( ! data.players.hasOwnProperty(i) ) {
						continue;
					}
					window.crick.set_player(data.players[i]);
				}
			}
			if ( data.hasOwnProperty('actors') ) {
				for ( var i in data.actors ) {
					if ( ! data.actors.hasOwnProperty(i) ) {
						continue;
					}
					window.crick.set_actor(data.actors[i]);
				}
			}
			if ( data.hasOwnProperty('actions') ) {
				for ( var i in window.crick.actions ) {
					if ( ! window.crick.actions.hasOwnProperty(i) ) {
						continue;
					}
					window.crick.delete_action(window.crick.actions[i]);
					delete window.crick.actions[i];
				}
				for ( var i in data.actions ) {
					if ( ! data.actions.hasOwnProperty(i) ) {
						continue;
					}
					window.crick.add_action(data.actions[i]);
				}
				window.crick.preformed_action = false;
			}
			if ( data.hasOwnProperty('message') ) {
				window.crick.add_message(data.message);
			}
			if ( data.hasOwnProperty('started') ) {
				window.crick.set_started(data.started);
			}
			if ( data.hasOwnProperty('active') ) {
				window.crick.set_active(data.active);
			}
		},
		add_message: function(message)
		{
			if ( ! window.crick.players.hasOwnProperty(message.player_id) ) {
				return;
			}
			var chat_message = $('#template .chat-message').clone();
			$('.name', chat_message).text(window.crick.players[message.player_id].name);
			$('.message', chat_message).text(message.message);
			$('#chat-bin').prepend(chat_message);
		},
		// CREATE
		add_player: function(player)
		{
			if ( window.crick.players.hasOwnProperty(player.player_id) ) {
				return;
			}
			var p = {
				name: player.name,
				color: player.color,
				element: $('#template .player').clone().get(0)
			};
			p.element.id = player.player_id;
			p.element.className = 'player';
			$('.color-swatch', p.element).css({ backgroundColor: p.color });
			$('.name span', p.element).text(p.name);
			$('#player-bin').append(p.element);
			window.crick.players[player.player_id] = p;
			if ( player.player_id === window.crick.player_id ) {
				$('.color-swatch', p.element).spectrum({
					showPaletteOnly: true,
					showPalette:true,
					color: p.color,
					palette: [
						//[ '#FFFFFF', '#000000', '#333333', '#666666', '#999999', '#CCCCCC', '#CCCC99', '#9999CC', '#666699' ],
						//[ '#660000', '#663300', '#996633', '#003300', '#003333', '#003399', '#000066', '#330066', '#660066' ],
						[ '#990000', '#993300', '#CC9900', '#006600', '#336666', '#0033FF', '#000099', '#660099', '#990066' ],
						//[ '#CC0000', '#CC3300', '#FFCC00', '#009900', '#006666', '#0066FF', '#0000CC', '#663399', '#CC0099' ],
						[ '#FF0000', '#FF3300', '#FFFF00', '#00CC00', '#009999', '#0099FF', '#0000FF', '#9900CC', '#FF0099' ],
						//[ '#CC3333', '#FF6600', '#FFFF33', '#00FF00', '#00CCCC', '#00CCFF', '#3366FF', '#9933FF', '#FF00FF' ],
						[ '#FF6666', '#FF6633', '#FFFF66', '#66FF66', '#66CCCC', '#00FFFF', '#3399FF', '#9966FF', '#FF66FF' ],
						//[ '#FF9999', '#FF9966', '#FFFF99', '#99FF99', '#66FFCC', '#99FFFF', '#66CCFF', '#9999FF', '#FF99FF' ],
						[ '#FFCCCC', '#FFCC99', '#FFFFCC', '#CCFFCC', '#99FFCC', '#CCFFFF', '#99CCFF', '#CCCCFF', '#FFCCFF' ]
					],
					change: function(color){
						window.crick.send_message({
							action: 'set_color',
							color: color.toHexString()
						});
					}
				});
				$('.name', p.element).on('click', function(event){
					var new_name = window.prompt('Please enter your new name', window.crick.players[player.player_id].name);
					if ( new_name != null && new_name != '' ) {
						window.crick.send_message({
							action: 'set_name',
							name: new_name
						});
					}
				});
			}
		},
		add_actor: function(actor)
		{
			if ( window.crick.actors.hasOwnProperty(actor.actor_id) ) {
				return;
			}
			switch ( actor.type ) {
				case 'dice':
					window.crick.add_dice(actor);
					break;
				case 'hole':
					window.crick.add_hole(actor);
					break;
				case 'marble':
					window.crick.add_marble(actor);
					break;
			}
		},
		add_dice: function(actor)
		{
			var die = document.createElement('div');
			die.id = actor.actor_id;
			die.className = 'die';
			window.crick.actors[actor.actor_id] = die;
			if ( window.crick.players.hasOwnProperty(actor.owner) ) {
				$('.dice-bin', window.crick.players[actor.owner].element).append(die);
			}
		},
		add_hole: function(actor)
		{
			window.crick.actors[actor.actor_id] = { x: actor.x, y: actor.y };
			var pen = window.crick.canvas.getContext('2d');
			pen.clearRect(
				window.crick.get_hole_offset(actor.x),
				window.crick.get_hole_offset(actor.y),
				window.crick.holeArea,
				window.crick.holeArea
			);
			pen.beginPath();
			pen.arc(
				window.crick.get_hole_offset(actor.x, true) + window.crick.holeSize / 2,
				window.crick.get_hole_offset(actor.y, true) + window.crick.holeSize / 2,
				window.crick.holeSize / 2,
				0,
				Math.PI * 2
			);
			pen.fillStyle = '#332211';
			pen.fill();
		},
		add_marble: function(actor)
		{
			var marble = document.createElement('div');
			marble.id = actor.actor_id;
			marble.className = 'marble';
			marble.style.width = window.crick.holeSize + 'px';
			marble.style.height = window.crick.holeSize + 'px';
			$('#crick-board-container').append(marble);
			if ( window.crick.players.hasOwnProperty(actor.owner) ) {
				marble.style.backgroundColor = window.crick.players[actor.owner].color;
			}
			window.crick.actors[actor.actor_id] = marble;
		},
		// UPDATE
		set_started: function(started)
		{
			if ( started ) {
				$('#crick-start').hide();
			} else {
				$('#crick-start').position({ my: 'center', at: 'center', of: window.crick.canvas }).show();
				$('#crick-start').on('click', function(event){
					$('#crick-start').off('click');
					window.crick.send_message({ action: 'start' });
				});
			}
		},
		set_player: function(player)
		{
			if ( ! window.crick.players.hasOwnProperty(player.player_id) ) {
				window.crick.add_player(player);
				return;
			}
			window.crick.players[player.player_id].name = player.name;
			window.crick.players[player.player_id].color = player.color;
			$('.color-swatch', window.crick.players[player.player_id].element).css({ backgroundColor: player.color });
			$('.name span', window.crick.players[player.player_id].element).text(player.name);
			for ( var i in player.marbles ) {
				if ( window.crick.actors.hasOwnProperty(player.marbles[i]) ) {
					window.crick.actors[player.marbles[i]].style.backgroundColor = player.color;
				}
			}
		},
		set_actor: function(actor)
		{
			if ( ! window.crick.actors.hasOwnProperty(actor.actor_id) ) {
				window.crick.add_actor(actor);
			}
			switch ( actor.type ) {
				case 'hole':
					window.crick.set_hole(actor);
					break;
				case 'die':
					window.crick.set_dice(actor);
					break;
				case 'marble':
					window.crick.set_marble(actor);
					break;
			}
		},
		set_dice: function(actor)
		{
			if ( ! window.crick.actors.hasOwnProperty(actor.actor_id) ) {
				window.crick.add_dice(actor);
			}
			var d = $(window.crick.actors[actor.actor_id]);
			d.toggleClass('can-roll', actor.can_roll ? true : false);
			d.toggleClass('can-move', actor.can_move ? true : false);
			d.toggleClass('value-0 value-1 value-2 value-3 value-4 value-5 value-6', false);
			d.toggleClass('value-' + actor.value.toString(), true);
		},
		set_hole: function(actor)
		{
			if ( ! window.crick.actors.hasOwnProperty(actor.actor_id) ) {
				window.crick.add_hole(actor);
			}
		},
		set_marble: function(actor)
		{
			if ( ! window.crick.actors.hasOwnProperty(actor.actor_id) ) {
				window.crick.add_marble(actor);
			}
			var m = $(window.crick.actors[actor.actor_id]);
			if ( window.crick.actors.hasOwnProperty(actor.position) ) {
				m.css({
					top: window.crick.get_hole_offset(window.crick.actors[actor.position].y, true) + 'px',
					left: window.crick.get_hole_offset(window.crick.actors[actor.position].x, true) + 'px'
				});
			} else {
				m.css({ top: '-999px', left: '-999px' });
			}
		},
		set_active: function(player_id)
		{
			$('#player-bin .player.active').toggleClass('active', false);
			if ( window.crick.players.hasOwnProperty(player_id) ) {
				$(window.crick.players[player_id].element).toggleClass('active', true);
			}
			if ( player_id === window.crick.player_id ) {
				window.crick.send_message({ action: 'get_actions' });
			} else {
				for ( var i in window.crick.actions ) {
					if ( ! window.crick.actions.hasOwnProperty(i) ) {
						continue;
					}
					window.crick.delete_action(window.crick.actions[i]);
					delete window.crick.actions[i];
				}
			}
		},
		// ACTIONS
		add_action: function(action)
		{
			switch ( action.type ) {
				case 'group':
					if ( action.hasOwnProperty('sub_actions') && action.sub_actions.length > 0 ) {
						for ( var i in action.sub_actions ) {
							if ( ! action.sub_actions[i].hasOwnProperty('type') ) {
								continue;
							}
							window.crick.add_action(action.sub_actions[i]);
						}
					}
					break;
				case 'move':
					window.crick.add_move(action);
					break;
				case 'roll':
					window.crick.add_roll(action);
					break;
				case 'skip':
					window.crick.add_skip(action);
					break;
			}
		},
		add_move: function(action)
		{
			var dice = [];
			$.each(action.dice, function(index, element){
				dice.push(document.getElementById(element));
			});
			var new_action = {
				action_id: action.action_id,
				marble: document.getElementById(action.marble),
				dice: dice,
				target: document.createElement('div'),
				type: action.type
			};
			if ( ! $(new_action.marble).is('.ui-draggable') ) {
				$(new_action.marble).draggable({
					containment: 'parent',
					distance: 10,
					revert: 'invalid',
					revertDuration: 200,
					scope: action.marble,
					start: function(event, ui){ $(this).toggleClass('held', true); },
					stop: function(event, ui){ $(this).toggleClass('held', false); }
				});
			}
			new_action.target.id = action.action_id;
			new_action.target.className = 'target';
			new_action.target.style.width = window.crick.holeArea + 'px';
			new_action.target.style.height = window.crick.holeArea + 'px';
			new_action.target.style.top = window.crick.get_hole_offset(window.crick.actors[action.target].y) + 'px';
			new_action.target.style.left = window.crick.get_hole_offset(window.crick.actors[action.target].x) + 'px';
			$(new_action.target).appendTo('#crick-board-container');
			$(new_action.target).droppable({
				scope: action.marble,
				tolerance: 'intersect',
				hoverClass: 'hover',
				drop: function(event, ui){
					//ui.draggable.toggleClass('hover', false);
					//$(this).toggleClass('hover', false);
					ui.draggable.position({ my: 'center', at: 'center', of: $(this) });
					$(new_action.dice).toggleClass('selected', false);
					window.crick.preform(action.action_id);
				},
				out: function(event, ui){
					//ui.draggable.toggleClass('hover', false);
					//$(this).toggleClass('hover', false);
					$(new_action.dice).toggleClass('selected', false);
				},
				over: function(event, ui){
					//ui.draggable.toggleClass('hover', true);
					//$(this).toggleClass('hover', true);
					$(new_action.dice).toggleClass('selected', true);
				}
			});
			window.crick.actions.push(new_action);
		},
		add_roll: function(action)
		{
			var targets = [];
			$.each(action.dice, function(index, element){
				targets.push(document.getElementById(element));
			});
			var new_action = {
				action_id: action.action_id,
				targets: targets,
				type: action.type
			};
			$(new_action.targets).off('click');
			$(new_action.targets).on('click', function(event){
				window.crick.preform(action.action_id);
			});
			window.crick.actions.push(new_action);
		},
		add_skip: function(action)
		{
			var new_action = {
				action_id: action.action_id,
				button: $('<button />', { text: 'Skip', id: action.action_id, class: 'crick-skip-button' }),
				type: action.type
			};
			new_action.button.button({ icons: { primary: 'ui-icon-arrowrefresh-1-n' }});
			new_action.button.on('click', function(event){
				window.crick.preform(action.action_id);
			});
			new_action.button.appendTo('body').position({ my: 'center', at: 'center', of: '#crick-board-canvas' });
			window.crick.actions.push(new_action);
		},
		delete_action: function(action)
		{
			switch ( action.type ) {
				case 'group':
					if ( action.hasOwnProperty('sub_actions') && action.sub_actions.length > 0 ) {
						for ( var i in action.sub_actions ) {
							if ( ! action.sub_actions[i].hasOwnProperty('type') ) {
								continue;
							}
							window.crick.delete_action(action.sub_actions[i]);
						}
					}
					break;
				case 'move':
					window.crick.delete_move(action);
					break;
				case 'roll':
					window.crick.delete_roll(action);
					break;
				case 'skip':
					window.crick.delete_skip(action);
					break;
			}
		},
		delete_move: function(action)
		{
			if ( $(action.marble).is('.ui-draggable') ) { $(action.marble).draggable('destroy'); }
			$(action.target).droppable('destroy');
			$(action.target).remove();
		},
		delete_roll: function(action){
			$(action.targets).off('click');
		},
		delete_skip: function(action)
		{
			action.button.button('destroy');
			action.button.off('click');
			action.button.remove();
		},
		// PREFORM ACTION
		preformed_action: false,
		preform: function(action_id)
		{
			// Prevent submission of multiple actions
			if ( ! window.crick.preformed_action ) {
				window.crick.send_message({
					action: 'preform',
					action_id: action_id
				});
				window.crick.preformed_action = true;
			}
		},
		// UTILITIES
		get_hole_offset: function(position, margin)
		{
			return position * window.crick.holeArea + window.crick.boardPadding + ( margin === true ? window.crick.holeMargin : 0 );
		},
		warn_high_latency: function()
		{
			console.log('Experiencing higher than average network latency, please be patient.');
		}
	};
	$(document).ready(function(){
		$('#crick-start > button').button({ icons: { primary: 'ui-icon-star' } });
		if ( window.innerWidth <= window.innerHeight ) {
			$('body').css({ 'overflow-y': 'scroll' });
		}
		window.crick.start('<?php echo $session->get('username') ?>', '<?php echo $session->get('authentication') ?>', 'ws://143.95.93.40:9000');
		$('#chat-input input').on('keyup', function(event){
			if ( event.keyCode == 13 && this.value != '' ) {
				window.crick.send_message({ action: 'send_message', message: this.value });
				this.value = '';
				this.placeholder = <?php echo json_encode($smack_talk_phrase); ?>[Math.floor(Math.random()*9)];
			}
		});
		if ( window.innerWidth > window.innerHeight ) {
			$('#crick-player-container').css({ width: ( window.innerWidth - window.crick.canvas.width ) + 'px' });
		} else {
			$('#crick-player-container').css({ width: window.innerWidth + 'px' });
		}
	});
</script>