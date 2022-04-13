<?php

namespace Template\Crick_Lobby;
	
function render_rule($rule, $rel = null)
{
?>
<li class="<?php printf('game-rule %s %s', $rule['hidden'] ? 'hidden' : '',  $rule['required'] ? 'required' : ''); ?>">
	<label>
<?php if ( $rel !== null ): ?>
		<input autocomplete="off" rel="<?php echo $rel ?>" id="<?php printf('rule-%s', $rule['index']); ?>" name="rules[]" value="<?php echo $rule['index'] ?>" type="checkbox" checked />
<?php else: ?>
		<input autocomplete="off" id="<?php printf('rule-%s', $rule['index']); ?>" name="rules[]" value="<?php echo $rule['index'] ?>" type="checkbox" checked />
<?php endif; ?>
		<div class="name"><?php echo $rule['name'] ?></div>
		<div class="description"><?php echo $rule['description'] ?></div>
<?php if ( isset($rule['children']) ): ?>
		<ul class="dependents">
<?php foreach ( $rule['children'] as $child ): ?>
			<?php echo \Template\Crick_Lobby\render_rule($child, sprintf('rule-%d', $rule['index'])); ?>
<?php endforeach; ?>
		</ul>
	</label>
<?php endif; ?>
</li>
<?php		
}

?>
<div id="wrapper">
	<form autocomplete="off" id="game-create-form" action="<?php echo $url_create ?>" method="post" enctype="multipart/form-data">
		<div id="game-create">
			<h1>Start a Game</h1>
			<ul id="game-rules-root">
<?php foreach ($rules as $rule): ?>
				<?php echo \Template\Crick_Lobby\render_rule($rule); ?>
<?php endforeach; ?>
			</ul>
			<div id="button-submit">
				<button type="submit">Start Game!</button>
			</div>
		</div>
	</form>
	<div id="links-lobby">
		<h1>Join a Game</h1>
		<ul>
			<li><a href="<?php echo $url_rejoin ?>">Re-Join Previous Game</a></li>
<?php foreach ( $active_games as $game ): ?>
			<li><a href="<?php echo $game['url_join'] ?>">Join Game: <?php echo $game['game_id'] ?></a> (<?php echo $game['player_count'] ?> active players).</li>
<?php endforeach; ?>
		</ul>
	</div>
</div>
<style>
	body { overflow-y: auto !important; }
	#wrapper {
		padding: 1em;
	}
	#links-lobby,
	#game-create {
		width: 50%;
		float: left;
	}
	#links-lobby h1,
	#game-create h1 {
		font-family: Helvetica, sans-serif;
		font-weight: bold;
		color: #111;
		text-align: center;
		margin: 0.5em 0px;
	}
	#game-rules-root,
	#game-rules-root ul {
		margin: 0px;
		padding: 0px;
		list-style: none;
		margin-top: 1em;
	}
	#game-rules-root {
		width: 100%;
		margin: 0px;
	}
	#game-rules-root .game-rule {
		background-color: #FED;
		box-sizing: border-box;
		padding: 1em;
		margin-bottom: 1em;
		border-radius: 0.5em;
		border: solid 0.125em #444;
		box-shadow: 0.25em 0.25em 0.5em #987;
	}
	#game-rules-root .game-rule label {
		overflow: hidden;
	}
	#game-rules-root .game-rule.hidden {
		display: none;
	}
	#game-rules-root .game-rule input {
		margin: 0px;
		padding: 0px;
		width: 2em;
		height: 2em;
		vertical-align: middle;
		display: inline-block;
	}
	#game-rules-root .game-rule .name {
		line-height: 1em;
		font-size: 2em;
		vertical-align: middle;
		display: inline-block;
		font-family: Helvetica, sans-serif;
		margin-left: 0.5em;
		color: #111;
		font-weight: bold;
	}
	#game-rules-root .game-rule .description {
		margin: 0.5em 1em;
		font-size: 1.25em;
		font-family: Helvetica, serif;
		color: #333;
	}
	#game-rules-root .game-rule .description details summary {
		text-align: center;
	}
	#game-rules-root .game-rule .description details summary q {
		font-family: Georgia, serif;
		font-style: italic;
		quotes: "“" "”" "‘" "’";
	}
	#game-rules-root .game-rule .description details summary q:before {
		color: #987;
		content: open-quote;
		display: inline-block;
		font-size: 2em;
		height: 0px;
		left: -1ex;
		line-height: 0px;
		margin-left: 0.5ex;
		position: relative;
		top: 0.333em;
		width: 0px;
	}
	#game-rules-root .game-rule .description details summary q:after {
		color: #987;
		content: close-quote;
		display: inline-block;
		font-size: 2em;
		height: 0px;
		line-height: 0px;
		position: relative;
		top: 0.333em;
		width: 0px;
	}
	#game-rules-root .game-rule .description details summary cite {
		display: block;
		text-align: right;
		font-weight: bold;
		margin-top: 0.25em;
	}
	#game-rules-root .game-rule .description details > p {
		text-indent: 1.5em;
		font-size: 0.85em;
		line-height: 1.2em;
	}
	#button-submit {
		text-align: right;
		margin-bottom: 2em;
	}
	#button-submit > button {
		color: #333;
		box-sizing: border-box;
		border-radius: 0.5em;
		border: solid 0.125em #444;
		box-shadow: 0.25em 0.25em 0.5em #987;
		padding: 0.25em 1em;
		font-size: 1.25em;
		font-family: Helvetica, sans-serif;
		font-weight: bold;
		background-color: #FED;
		cursor: pointer;
	}
	#button-submit > button:hover {
		background-color: #987;
		border-color: #987;
		color: #FFF;
	}
	#links-lobby > ul {
		list-style: none;
	}
	#links-lobby > ul > li {
		font-size: 1.5em;
		font-family: Helvetica, sans-serif;
		box-sizing: border-box;
		padding: 0.25em 1em;
		border: solid 0.1em #444;
		border-radius: 0.5em;
		background-color: #FED;
		margin-bottom: 0.5em;
	}
	#links-lobby > ul > li > a {
		display: block;
		text-decoration: none;
		color: #333;
	}
	#links-lobby > ul > li:hover {
		background-color: #987;
		border-color: #987;
	}
	#links-lobby > ul > li:hover > a {
		color: #FFF;
	}
</style>
<script>
$(document).ready(function(){
	$(document).on('change', '.game-rule > label > input', function(event){
		$('> label > .dependents input', this.parentNode.parentNode).prop('disabled', !this.checked);
	});
});
</script>