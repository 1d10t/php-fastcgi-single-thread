<?php

class fcgi_request{
	var $reqid;
	var $stage;
	var $time;
	var $sock;
	
	var $params = '';
	var $data = '';
	var $parsed = false;
	
	function ttime(){ $this->time = time(); }
	
	function fcgi_request($reqid, &$sock){
		$this->sock = $sock;
		$this->reqid = $reqid;
		$this->ch_stage(FCGI_BEGIN_REQUEST);
		$this->ttime();
	}
	
	function add_data($data){
		$this->data .= $data;
		$this->ttime();
	}
	
	function add_params($data){
		$this->params .= $data;
		$this->ttime();
	}
	
	function ch_stage($stage){
		$this->stage = $stage;
		$this->ttime();
	}
	
	function parse(){
		$params = array();
		
		while(strlen($this->params) > 2){
			$off = 0;
			
			$nl = ord($this->params[0]);
			if(($nl >> 7) == 1){
				$nl = (($nl & 0x7f) << 24) + (ord($this->params[1+$off]) << 16)
					+ (ord($this->params[2+$off]) << 8) + ord($this->params[3+$off]);
				$off += 3;
			}
			
			$vl = ord($this->params[1+$off]);
			if(($vl >> 7) == 1){
				$vl = (($vl & 0x7f) << 24) + (ord($this->params[2+$off]) << 16)
					+ (ord($this->params[3+$off]) << 8) + ord($this->params[4+$off]);
				$off += 3;
			}
			
			$k = substr($this->params, 2+$off, $nl);
			$v = substr($this->params, $nl+2+$off, $vl);
			
			if($k == 'HTTP_COOKIE'){
				
				if($v === '' && !isset($params[ $k ]))
					@$params[ $k ] = array();
				else
					@$params[ $k ][] = $v;
				
			}elseif($k == 'HTTP_AUTHORIZATION'){
				
				if(preg_match('#\s*basic\s*([^\s]+)#i', $v, $r))
					@list($params['PHP_AUTH_USER'], $params['PHP_AUTH_PW']) = explode(':', base64_decode($r[1]));
				
			}else
				$params[ $k ] = $v;
			
			$this->params = substr($this->params, $nl+$vl+2+$off);
		}
	//	print_r($params);
		$this->params = $params;
		$this->parsed = true;
	}
	
}




class fcgi_server{
	var $ready = false;
	var $master;
	var $sockets = array();
	var $requests = array();
	
	var $gen_body_callback;
	
	
	function fcgi_server($gbcb, $host, $port = null, $af = AF_INET, $type = SOCK_STREAM, $proto = SOL_TCP){
		
		$this->gen_body_callback = $gbcb;
		
		if(($this->master = socket_create($af, $type, $proto)) !== false)
			socket_set_option($this->master, SOL_SOCKET,SO_REUSEADDR, 1);
		else return;
		
		if($af == AF_UNIX && file_exists($host) && filetype($host) == 'socket')
			unlink($host);
		
		if(@socket_bind($this->master, $host, $port)){
			
			if($af == AF_UNIX) chmod($host, 0777);
			
			if(!socket_listen($this->master)) return;
			
			$this->sockets = array($this->master);
			
			$this->ready = true;
			
		}
	}
	
	
	function process_request(&$sock, $header){
		$Rs = &$this->requests;
		$Ss = &$this->sockets;
		$h = unpack('Cver/Ctype/nreqid/ncontlen/Cpaddlen/x', $header);
	//	print_r($h);
		
		$key = (string)$sock.':'.$h['reqid'];
		
		$c = '';
	//	echo "receiving requet body...\r\n";
		if($h['contlen']) socket_recv($sock, $c, $h['contlen'], 0);
		
		if(isset($Rs[$key])){
			
			if($h['contlen'])
			switch($h['type']){
			case FCGI_PARAMS:
				$Rs[$key]->add_params($c);
			break;
			case FCGI_STDIN:
				$Rs[$key]->add_data($c);
			break;
			}
			
			$Rs[$key]->ch_stage($h['type']);
			
			if(!$h['contlen'] && $h['type'] == FCGI_STDIN)
				$Rs[$key]->parse();
			
		}elseif($h['type'] == FCGI_BEGIN_REQUEST)
			$Rs[$key] = new fcgi_request($h['reqid'], $sock);
		
		
		
		//
		if($h['paddlen']) socket_recv($sock, $c, $h['paddlen'], 0);
	//	die;
		
	}
	
	
	// DELETE SOCKET
	function delete_socket(&$sock, $shutdown = true){
		
		unset($this->sockets[ array_search($sock, $this->sockets) ]);
	//	echo "shutdown socket...\r\n";
		if($shutdown) @socket_shutdown($sock);
	//	echo "closing socket...\r\n";
		@socket_close($sock);
		
	}
	
	
	// DELETE REQUEST
	function delete_request(&$sock, $reqid){
		
	//	echo "searching for keys and doubles...\n";
	//	print_r($this->requests);
		
		$psock = (string)$sock.':';
		
		$has_double = false; $key = false;
		$need_key = $psock.$reqid;
		foreach(array_keys($this->requests) as $k){
			
			if($k === $need_key) $key = $k;
			elseif(strpos($k,$psock) === 0) $has_double = true;
		//	elseif(strcmp($k,$psock) > 1) $has_double = true;
			
			if($key !== false && $has_double)
				break;
			
		}
		
		
	//	echo "unsetting request [$key]...\n";
		if($key !== false)
			unset($this->requests[$key]);
		
	//	echo "deleting socket [$sock]...\n";
		if(!$has_double)
			$this->delete_socket($sock);
		
		
	//	print_r($this->requests);
		
		
	//	echo "complete.\n";
	//	die;
	}
	
	
	function send_packet($sock, &$out){
		$l = strlen($out);
		
		$sl = 0;
		while($sl < $l && is_resource($sock)){
			
			$tsl = socket_write($sock, substr($out, $sl), $l-$sl);
			
			if($tsl === false) return false;
			
			$sl += $tsl;
			
		}
		
		return true;
	}
	
	
	function send_responce($sock, &$body, $reqid){
		
		// sending responce body
		for($i=0; $i<ceil(strlen($body)/FCGI_CHUNK_SIZE); $i++){
			//
			$out = _c_FCGI_StdOut($reqid, substr($body, $i*FCGI_CHUNK_SIZE, FCGI_CHUNK_SIZE));
			if(!$this->send_packet($sock, $out)) return false;
			//
		}
		
		// ending fcgi request
		$out = _c_FCGI_StdOut($reqid)._c_FCGI_EndRequest($reqid);
		if(!$this->send_packet($sock, $out)) return false;
		
		return true;
	}
	
	
	function step(){
		$Rs = &$this->requests;
		$Ss = &$this->sockets;
		
		$changed_sockets = $Ss;
		$num_changed_sockets = @socket_select($changed_sockets, $write = NULL, $except = NULL, FCGI_SEC_SLEEP, FCGI_MSEC_SLEEP);
		
		if($num_changed_sockets)
		foreach($changed_sockets as $socket){
			
			// accept new connections
			if($socket == $this->master){
				
			//	echo "accepting connection...\r\n";
				if(($client = @socket_accept($this->master)) === false){
				//	echo "socket_accept() failed: reason: ", socket_strerror($client), "\r\n";
					continue;
				}else{
					$Ss[] = $client;
				//	print_r($Ss);
				}
				
			// client handling
			}else{
				
			//	echo "receiving request header...\r\n";
				$bytes = socket_recv($socket, $header, FCGI_HEADER_LEN, 0);
				
				if($bytes == 0 || strlen($header) < FCGI_HEADER_LEN){
					
					$this->delete_socket($socket);
					
				}else{
					
					$this->process_request($socket, $header);
					
				}
				
			}
			
		}
		
		if(count($Rs))
		foreach(array_keys($Rs) as $k){
			$r = &$Rs[$k];
			
			if($r->stage == FCGI_ABORT_REQUEST || time()-FCGI_TIMEOUT > $r->time){
				
				$this->delete_request($r->sock, $r->reqid);
				
			}elseif($r->parsed){
				
			//	echo 'FOUND PARSED ', $r->reqid, ' AT SOCKET ', $r->sock, "\r\n";
				
				
				$body = call_user_func($this->gen_body_callback, $r);
				
				if(($sname = session_name()) !== '' && ($sid = session_id()) !== ''
				&& ($dbl_crlf_pos = strpos($body, "\r\n\r\n")) !== false){
					
					$sprm = session_get_cookie_params();
					
					$body = substr($body, 0, $dbl_crlf_pos)
						."\r\nSet-Cookie: ".$sname.'='.$sid
							.(strlen($sprm['domain']) ? '; Domain='.$sprm['domain'] : '')
							.(is_int($sprm['lifetime']) ? '; Max-Age='.$sprm['lifetime'] : '')
							.(strlen($sprm['path']) ? '; Path='.$sprm['path'] : '')
							.($sprm['secure'] ? '; Secure' : '')
							.'; Version=1'
						.substr($body, $dbl_crlf_pos);
					
				}
				
				fcgi_clean_shit();
				
				
			//	echo "writing output...\r\n";
				$this->send_responce($r->sock, $body, $r->reqid);
				
				$this->delete_request($r->sock, $r->reqid);
				
			}
			
		}
		
	}
	
	
	
	function run(){
		while(true) $this->step();
	}
	
	
}


function fcgi_assign_globals($R, $a_get = true, $a_post = true, $a_mpost = true, $upload_files = true , $a_sess = true){
	$P = &$R->params;
	$D = &$R->data;
	
	// assign _SERVER
	$_SERVER = $P;
	
	// assign _GET
	$_GET = array();
	if($a_get) parse_str(@$P['QUERY_STRING'], $_GET);
	
	// assign _POST
	$_POST = array();
	$_FILES = array();
	if($a_post) if(@$P['REQUEST_METHOD'] == 'POST'){
		
		if($a_mpost && strcasecmp('multipart/form-data', @$P['CONTENT_TYPE']) < 0){
			
			if(preg_match('/;\s*boundary\s*=\s*("([^\"]+)"|\'([^\']+)\'|[^\s;]+)/i', $P['CONTENT_TYPE'], $r))
				$bound = @$r[2] ? $r[2] : (@$r[3] ? $r[3] : $r[1]);
			
			$D = explode("\r\n", $D);
			
			$fields = array();
			
			for($i=0; $i<count($D); $i++){
				$s = &$D[$i]; $is_new = false;
				
				if($s == '--'.$bound.'--') break;
				elseif($s == '--'.$bound){
					
					// reading headers
					$headers = array();
					while(isset($D[++$i]) && ($s = &$D[$i]) !== '')
					if(preg_match('#^([a-z\d_-]+)\s*\:\s*(.+)$#msi', $s, $r)){
						$args = array();
						
						if(($quant = strpos($r[2], ';')) !== false){
							
							$value = substr($r[2], 0, $quant);
							
							if(preg_match_all('#;\s*([a-z\d_-]+)\s*=\s*("([^\"]*)"|\'([^\']*)\'|[^\s;]*)#si',
							substr($r[2], $quant), $tt, PREG_SET_ORDER)) foreach($tt as $rr)
								$args[ strtolower($rr[1]) ] = isset($rr[3]) ? $rr[3] : (isset($rr[4]) ? $rr[4] : $rr[2]);
							
						}else
							$value = $r[2];
						
						$headers[ strtolower($r[1]) ] = array('value' => rtrim($value), 'args' => $args);
						
					}
					
					// reading body
					$body = array();
					while(isset($D[++$i]) && ($s = &$D[$i]) != '--'.$bound){
						if(($s = &$D[$i]) == '--'.$bound.'--') break;
						$body[] = $s;
					}
					
					$fields[] = array($headers, join("\r\n", $body));
					
					if(--$i+2 == count($D)) break;
				}
				
			}
			
			$post = array();
			$files = array();
			
			for($i=0; $i<count($fields); $i++)
			if(strcasecmp('form-data', @$fields[$i][0]['content-disposition']['value']) == 0
			&& strlen(@$fields[$i][0]['content-disposition']['args']['name'])){
				$f = &$fields[$i];
				
				if(!isset($f[0]['content-disposition']['args']['filename'])
				|| !isset($f[0]['content-type'])){
					
					$post[] = urlencode($f[0]['content-disposition']['args']['name']).'='.urlencode($f[1]);
					
				}elseif($upload_files){
					
					if(($tfn = @tempnam('/tmp', 'php-fcgi-upload-')) !== false && strlen($f[1])){
						$tf = fopen($tfn, 'w');
						fwrite($tf, $f[1]);
						fclose($tf);
					}
					
					preg_match('#^([^\[ ].*)(\[.*\].*)?$#U', $f[0]['content-disposition']['args']['name'], $r);
					$r[1] = strtr($r[1], '[', '_');
					$files[] = urlencode($r[1]).'[name]'.@$r[2].'='.urlencode($f[0]['content-disposition']['args']['filename']);
					$files[] = urlencode($r[1]).'[type]'.@$r[2].'='.urlencode($f[0]['content-type']['value']);
					$files[] = urlencode($r[1]).'[size]'.@$r[2].'='.strlen($f[1]);
					$files[] = urlencode($r[1]).'[tmp_name]'.@$r[2].'='.urlencode($tfn);
					$files[] = urlencode($r[1]).'[error]'.@$r[2].'='.($tfn === false || !strlen($f[1])
						? UPLOAD_ERR_NO_FILE : UPLOAD_ERR_OK);
					
				}
				
			}
			
			if(count($files)) parse_str(join('&', $files), $_FILES);
			if(count($post)) parse_str(join('&', $post), $_POST);
			
		}elseif(strcasecmp('application/x-www-form-urlencoded', @$P['CONTENT_TYPE']) <= 0)
			parse_str($D, $_POST);
		
	}
	
	// assign _REQUEST
	$_REQUEST = $_GET + $_POST;
	
	// assign _SESSION
	if($a_sess){
		
		$sname = get_cfg_var('session.name');
		$sid = false;
		
		if(isset($P['HTTP_COOKIE']) && count($P['HTTP_COOKIE']))
		foreach($P['HTTP_COOKIE'] as $c)
		if(preg_match('#^\s*'.preg_quote($sname).'\s*=\s*("([a-z\d]+)"|\'([a-z\d]+)\'|[a-z\d]+)#i', $c, $r)
		or preg_match('#;\s*'.preg_quote($sname).'\s*=\s*("([a-z\d]+)"|\'([a-z\d]+)\'|[a-z\d]+)#i', $c, $r))
			$sid = isset($r[2]) ? $r[2] : (isset($r[3]) ? $r[3] : $r[1]);
		
		session_name($sname);
		if($sid !== false) session_id($sid);
		session_start();
		
	}
	
}

function fcgi_clean_shit(){
	
	// cleanup uploaded files
	if(count($_FILES)) foreach($_FILES as $f)
		@unlink($f['tmp_name']);
	
	// write and close session
	if(($sname = session_name()) !== '' && ($sid = session_id()) !== ''){
		
		$sdata = session_encode();
		session_destroy();
		
		$fn = session_save_path().'/sess_'.$sid;
		
		if(strlen($sdata) && ($f = @fopen($fn, 'w')) !== false){
			fwrite($f, $sdata);
			fclose($f);
		}
		
		session_unset();
		
	}
}

?>