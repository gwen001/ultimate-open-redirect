#!/usr/bin/php
<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

function __autoload( $c ) {
	include( $c.'.php' );
}


// parse command line
{
	$testor = new TestOpenRedirect();

	$argc = $_SERVER['argc'] - 1;

	for ($i = 1; $i <= $argc; $i++) {
		switch ($_SERVER['argv'][$i]) {
			case '-h':
				Utils::help();
				break;

			case '-e':
				$testor->setMaxChild($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-r':
				$testor->setRedirect( false );
				break;

			case '-t':
				$testor->setTarget($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-u':
				$testor->setEncode( true );
				$i++;
				break;

			case '-z':
				$testor->setHacker($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			default:
				Utils::help('Unknown option: '.$_SERVER['argv'][$i]);
		}
	}

	if( !$testor->getTarget() ) {
		Utils::help('Target not found!');
	}
	if( !$testor->getHacker() ) {
		Utils::help('Hacker url not found!');
	}
}
// ---


// main loop
{
	$testor->run();
}
// ---


exit();

?>
