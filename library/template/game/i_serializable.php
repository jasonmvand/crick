<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Template\Game;

interface I_Serializable {
	
	public function serialize(\Library\Template\I_Game $game);
	
}