<?php
$load_addons = 'giphy';
require(__DIR__ . '/../../../system/config_addons.php');

if(mainBlocked()){
	echo boomCode(0);
	die();
}

session_write_close();

function sendGiphyChat(){
	global $mysqli, $data;
	
	$gid = escape($_POST['id']);
	$chat = escape($_POST['chat']);
	$origin = escape($_POST['origin']);
	
	if(stripos($chat, $gid) === false){
		return boomCode(0);
	}
	if(!preg_match('@^https?:\/\/(www\.)?media([0-9]+)?.giphy.com/([\w/_\.\%\+#\-\?:\=\&\;\(\)]*)?@ui', $origin)){
		return boomCode(0);
	}
	
	$content = uploadProcess('tumb', $origin, $chat);
	$logs = userPostChat($content);
	return boomCode(1, array('logs'=> $logs));
}

if(isset($_POST['origin'], $_POST['chat'], $_POST['id']) && boomAllow($addons['addons_access'])){
	echo sendGiphyChat();
	die();
}
else {
	die();
}
?>