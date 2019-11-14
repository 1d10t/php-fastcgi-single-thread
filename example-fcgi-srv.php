#!/usr/bin/php
<?php

require __DIR__.'/lib/fcgi/fcgi_h.php';

set_time_limit(0);
ob_implicit_flush();

fcgi_clean_shit();

function generate_body($R){
	
	fcgi_assign_globals($R, $a_get = true, $a_post = true, $a_mpost = true, $upload_files = false, $a_sess = false);
	
	ob_start();
	echo "Status: 200\r\n";
	//
	
	switch($_SERVER['SCRIPT_NAME']){
	case '/hash';
		echo "Content-Type: text/javascript; charset=utf-8\r\n\r\n";
		
		if(empty($_GET['hash'])){
			echo json_encode(array('error' => 'empty hash param'));
			break;
		}
		
		$hash = strtolower($_GET['hash']);
		
		echo json_encode(compact('hash'));
		
		break;
	default:
		echo "Content-Type: text/plain; charset=utf-8\r\n\r\n";
		echo json_encode(array('_GET' => $_GET, '_SERVER' => $_SERVER));
	}
	
	
	return ob_get_clean();
}

$s = new fcgi_server('generate_body', '/var/run/fcgi.sock', 0, AF_UNIX, SOCK_STREAM, 0);

if($s->ready) $s->run();

?>
