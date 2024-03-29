<?php

/*
 * Number of bytes in a FCGI_Header.  Future versions of the protocol
 * will not reduce this number.
 */
define('FCGI_HEADER_LEN', 8);


/*
 * Values for type component of FCGI_Header
 */
define('FCGI_BEGIN_REQUEST', 1);
define('FCGI_ABORT_REQUEST', 2);
define('FCGI_END_REQUEST', 3);
define('FCGI_PARAMS', 4);
define('FCGI_STDIN', 5);
define('FCGI_STDOUT', 6);
define('FCGI_STDERR', 7);
define('FCGI_DATA', 8);
define('FCGI_GET_VALUES', 9);
define('FCGI_GET_VALUES_RESULT', 10);
define('FCGI_UNKNOWN_TYPE', 11);
define('FCGI_MAXTYPE', FCGI_UNKNOWN_TYPE);


/*
 * Value for requestId component of FCGI_Header
 */
define('FCGI_NULL_REQUEST_ID', 0);


/*
 * Mask for flags component of FCGI_BeginRequestBody
 */
define('FCGI_KEEP_CONN', 1);


/*
 * Values for role component of FCGI_BeginRequestBody
 */
define('FCGI_RESPONDER', 1);
define('FCGI_AUTHORIZER', 2);
define('FCGI_FILTER', 3);


/*
 * Values for protocolStatus component of FCGI_EndRequestBody
 */
define('FCGI_REQUEST_COMPLETE', 0);
define('FCGI_CANT_MPX_CONN', 1);
define('FCGI_OVERLOADED', 2);
define('FCGI_UNKNOWN_ROLE', 3);


/*
 * Variable names for FCGI_GET_VALUES / FCGI_GET_VALUES_RESULT records
 
define('FCGI_MAX_CONNS', 'FCGI_MAX_CONNS');
define('FCGI_MAX_REQS', 'FCGI_MAX_REQS');
define('FCGI_MPXS_CONNS', 'FCGI_MPXS_CONNS');
*/
//
define('FCGI_VERSION_1',	1);

if(!defined('FCGI_TIMEOUT'))	define('FCGI_TIMEOUT',		10);
if(!defined('FCGI_CHUNK_SIZE'))	define('FCGI_CHUNK_SIZE',	32768);
if(!defined('FCGI_SEC_SLEEP'))	define('FCGI_SEC_SLEEP',	0);
if(!defined('FCGI_MSEC_SLEEP'))	define('FCGI_MSEC_SLEEP',	100000);
//if(!defined('FCGI_SRV_HOST'))	define('FCGI_SRV_HOST',		'10.0.0.1');
//if(!defined('FCGI_SRV_PORT'))	define('FCGI_SRV_PORT',		3000);

require dirname(__FILE__).'/fcgi_c.php';
require dirname(__FILE__).'/fcgi_srv.php';

?>