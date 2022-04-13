<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Crick\Rule;

class Move_Tidy extends \Library\Template\Game\Rule {
	
	public static function get_name()
	{
		return 'Tidy';
	}
	
	public static function get_description()
	{
		return '
			<details open>
				<summary>
					<q>
						Cleanliness becomes more important when godliness is unlikely.
					</q>
					<cite>
						P. J. O\'Rourke
					</cite>
				</summary>
				<p>
					You may only enter, or move within, the scoring area when the resulting move
					leaves no gaps in the soring area. The scoring area must be filled from the top
					hole, which is closes to the center, to the bottom.
				</p>
			</details>
		';
	}

	public static function is_required()
	{
		return false;
	}

	public static function precedence()
	{
		return -920;
	}
	
	public static function filter_actions(\Library\Template\I_Game $game, \Library\Template\Game\I_Player $player)
	{
		foreach ( $game->get_actions($player) as $action ) {
			if ( ! $action instanceof \Library\Crick\Action\Move ) {
				// Action is not a move, continue
				continue;
			}
			$target_position = $action->get_destination($game);
			if ( ! \Library\Crick\Board::is_end($target_position) ) {
				// Action does not enter a scoring area, continue
				continue;
			}
			$target_owner = $target_position->get_owner($game);
			if ( ! $target_owner instanceof \Library\Template\Game\I_Player ) {
				// Action enters an unowned scoring area, continue
				continue;
			}
			if ( $target_owner->player_id() != $player->player_id() ) {
				// Action enters a scoring zone owned by another player, continue
				continue;
			}
			// Tidy rule requires that no empty space is left between marbles in the scoring
			// zone. Any movement that results in an empty space will be removed.
			$marble = $action->get_marble($game);
			$current_position = $marble->get_position($game);
			$end_position = \Library\Crick\Board::get_end_position($game, $player);
			$map = array();
			for ( $step = 0, $index = $end_position->index(); $step < \Library\Crick\Board::NUM_MARBLES; $step++, $index++  ) {
				$hole = \Library\Crick\Board::get_hole_by_index($game, $index);
				$map[$hole->actor_id()] = $hole->has_contents() ? 1 : 0;
			}
			if ( isset($map[$current_position->actor_id()]) ) {
				$map[$current_position->actor_id()] = 0;
			}
			$map[$target_position->actor_id()] = 1;
			$has_marble = false;
			foreach ( $map as $actor_id => $has_contents ) {
				if ( $has_marble AND ! $has_contents ) {
					$game->remove_action($action->action_id());
					break;
				}
				$has_marble = $has_contents;
			}
		}
		return true;
	}

}