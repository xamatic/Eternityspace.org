<?php
$load_addons = 'giphy';
require(__DIR__ . '/../../../system/config_addons.php');

if(privateBlocked()){
	die();
}

session_write_close();

function sendGiphyPrivate(){
	global $mysqli, $data;
	
	$gid = escape($_POST['id']);
	$chat = escape($_POST['chat']);
	$origin = escape($_POST['origin']);
	$target = escape($_POST['target'], true);
	
	$user = userRelationDetails($target);
	if(!canSendPrivate($user)){
		return boomCode(99);
	}
	
	if(stripos($chat, $gid) === false){
		return boomCode(0);
	}
	if(!preg_match('@^https?:\/\/(www\.)?media([0-9]+)?.giphy.com/([\w/_\.\%\+#\-\?:\=\&\;\(\)]*)?@ui', $origin)){
		return boomCode(0);
	}
	
	$content = uploadProcess('tumb', $origin, $chat);
	$logs = userPostPrivate($user, $content);
	return boomCode(1, array('logs'=> $logs));
}

if(isset($_POST['origin'], $_POST['chat'], $_POST['target'], $_POST['id']) && boomAllow($addons['addons_access'])){
	echo sendGiphyPrivate();
	die();
}
else {
	echo boomCode(0);
	die();
}
?>