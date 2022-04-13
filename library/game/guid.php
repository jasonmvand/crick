<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Library\Game;

class GUID {
	
	public static function get()
	{ // Disclaimer: Will not work after year 90499 A.D.
		$timestamp = microtime(true);
		$time = floor($timestamp);
		$quotient = base_convert(floor($time / 1679615), 10, 36); // 868-1679615 => 00O4-ZZZZ
		$remainder = base_convert($time % 1679615, 10, 36); // 0-1679615 => 0000-ZZZZ
		$microseconds = base_convert(floor(($timestamp - $time) * 1679615), 10, 36); // 0-1679615 => 0000-ZZZZ
		$random = base_convert(mt_rand(0, 1679615), 10, 36); // 0-1679615 => // 0000-ZZZZ
		return sprintf('%04s%04s%04s%04s', $quotient, $remainder, $microseconds, $random);
	}
	
}