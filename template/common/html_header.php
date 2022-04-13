<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<!doctype html>
<html>
	<head>
		<title><?php echo $head['title'] ?></title>
		<meta name="robots" content="none" />
<?php
	foreach ( (array)$head['meta'] as $tag ) {
		printf('<meta %s />', implode(' ', array_map(function($property, $value){ return sprintf('%s="%s"', $property, htmlentities($value)); }, array_keys($tag), $tag)));
	}
	foreach ( (array)$head['link'] as $tag ) {
		printf('<link %s />', implode(' ', array_map(function($property, $value){ return sprintf('%s="%s"', $property, htmlentities($value)); }, array_keys($tag), $tag)));
	}
	foreach ( (array)$head['script'] as $tag ) {
		printf('<script src="%s"></script>', $tag);
	}
	foreach ( $head as $element ) {
		if ( $element instanceof Library\Primitive\HTMLElement ) {
			print $element->render();
		}
	}
?>
	</head>
	<body style="width: 100%; height: 100%; margin: 0px; padding: 0px; overflow: hidden;">
	<!-- BEGIN CONTENT -->