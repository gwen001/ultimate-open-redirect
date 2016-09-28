#!/usr/bin/php
<?php


function usage( $err=null ) {
  echo 'Usage: '.$_SERVER['argv'][0]." <payloads file> <hacker url>\n";
  if( $err ) {
    echo 'Error: '.$err."!\n";
  }
  exit();
}

function signal_handler( $signal )
{
	global $n_child;

	switch( $signal )
	{
		case SIGCHLD:
			$n_child--;
			pcntl_waitpid( -1, $status, WNOHANG );
			break;
		default:
			exit( 0 );
			break;
	}
}

function testOpenRedirect( $url, $hacker )
{
	$c = curl_init();
	curl_setopt( $c, CURLOPT_URL, $url );
	curl_setopt( $c, CURLOPT_CONNECTTIMEOUT, 2 );
	curl_setopt( $c, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
	curl_exec( $c );
	$t_info = curl_getinfo( $c );
	//var_dump( $t_info );
	curl_close( $c );

	$t_url = parse_url( $t_info['url'] );

	if( $t_info['redirect_count'] && strtolower($t_url['host']) == $hacker ) {
		return true;
	} else {
		return false;
	}
}


if( $_SERVER['argc'] != 3 ) {
  usage();
}


$src = $_SERVER['argv'][1];
if( !is_file($src) || !is_readable($src) ) {
	usage('Source file not found!');
}
$t_payloads = file( $src, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
$t_payloads = array_map( 'trim', $t_payloads );
$cnt = count( $t_payloads );

$hacker = strtolower( trim($_SERVER['argv'][2]) );


$n_child = 0;
$max_child = 10;
$sleep = 1000000;
$current_pointer = 0;
$cnt_notice = 1500;
$tested = $worked = 0;


posix_setsid();
declare( ticks=1 );
//pcntl_signal( SIGHUP,  'signal_handler' );
//pcntl_signal( SIGINT,  'signal_handler' );
//pcntl_signal( SIGQUIT, 'signal_handler' );
//pcntl_signal( SIGABRT, 'signal_handler' );
//pcntl_signal( SIGKILL, 'signal_handler' );
//pcntl_signal( SIGIOT,  'signal_handler' );
pcntl_signal( SIGCHLD, 'signal_handler' );
//pcntl_signal( SIGTERM, 'signal_handler' );
//pcntl_signal( SIGTSTP, 'signal_handler' );


echo $cnt." payloads loaded\n\n";

for( $current_pointer=0 ; $current_pointer<$cnt ; )
{
	if( ($current_pointer%$cnt_notice) == 0 ) {
		echo "Current ".$current_pointer."...\n";
	}
	if( $n_child < $max_child )
	{
		$pid = pcntl_fork();

		if( $pid == -1 ) {
			// fork error
		} elseif( $pid ) {
			// father
			$n_child++;
			$current_pointer++;
		} else {
			// child process
			$tested++;
			$r = testOpenRedirect( $t_payloads[$current_pointer], $hacker );
			if( $r ) {
				$worked++;
				echo "Open redirect -> ".$t_payloads[$current_pointer]."\n";
			}
			exit( 0 );
		}
	}

	usleep( $sleep );
}

echo "\n".$tested." payloads tested, ".$worked." payloads worked\n\n";
