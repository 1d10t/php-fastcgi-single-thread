<?php

function _c_FCGI_Header($type, $reqid, $contlen, $padlen = 0){
	return pack('ccnncx', FCGI_VERSION_1, $type, $reqid, $contlen, $padlen);
};


function _c_FCGI_BeginRequestBody($role, $flags){
	return pack('ncxxxxx', $role, $flags);
}

function _c_FCGI_BeginRequest($reqid, $role, $flags = 0){
	
	$body = _c_FCGI_BeginRequestBody($role, $flags);
	
	return _c_FCGI_Header(FCGI_BEGIN_REQUEST, $reqid, strlen($body))
		.$body;
}


function _c_FCGI_EndRequestBody($appStatus, $protocolStatus){
	return pack('Ncxxx', $appStatus, $protocolStatus);
}

function _c_FCGI_EndRequest($reqid, $appStatus = 0, $protocolStatus = FCGI_REQUEST_COMPLETE){
	
	$body = _c_FCGI_EndRequestBody($appStatus, $protocolStatus);
	
	return _c_FCGI_Header(FCGI_END_REQUEST, $reqid, strlen($body))
		.$body;
}


function _c_FCGI_UnknownTypeBody($type){
	return pack('cxxxxxxx', $type);
}

function _c_FCGI_UnknownType($reqid, $type){
	
	$body = _c_FCGI_UnknownTypeBody($type);
	
	return _c_FCGI_Header(FCGI_UNKNOWN_TYPE, $reqid, strlen($body))
		.$body;
}


function _c_FCGI_StdIn($reqid, $body = ''){
	return _c_FCGI_Header(FCGI_STDIN, $reqid, strlen($body))
		.$body;
}

function _c_FCGI_StdOut($reqid, $body = ''){
	return _c_FCGI_Header(FCGI_STDOUT, $reqid, strlen($body))
		.$body;
}

function _c_FCGI_StdErr($reqid, $body = ''){
	return _c_FCGI_Header(FCGI_STDERR, $reqid, strlen($body))
		.$body;
}


function _c_FCGI_ParamsBody($params){
	$body = '';
	
	if(count($params)) foreach($params as $key => $val)
		$body .= pack('cc', $key, $val).$key.$val;
	
	return $body;
}

function _c_FCGI_Params($reqid, $params = array()){
	
	$body = _c_FCGI_ParamsBody($params);
	
	return _c_FCGI_Header(FCGI_PARAMS, $reqid, strlen($body))
		.$body;
}

?>