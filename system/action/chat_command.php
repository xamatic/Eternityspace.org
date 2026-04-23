<?php
require("../config_session.php");

if(mainBlocked()){
	die();
}

session_write_close();

if (!isset($_POST['content'])){
	echo boomCode(99);
	die();
}
if(isTooLong($_POST['content'], $setting['max_main'])){
	echo boomCode(99);
	die();
}

$content = escape($_POST['content']);
$content = wordFilter($content, 1);
$content = textFilter($content);
$command = preg_split('/\s+/', trim($content));
$base_command = strtolower($command[0]);

function slashCommandUserByToken($token){
	$token = trim((string) $token);
	$token = ltrim($token, '@');
	if($token === ''){
		return array();
	}
	return userNameDetails($token);
}
function slashCommandActionCode($result){
	if($result == 1){
		return boomCode(1);
	}
	if($result == 2 || $result == 3){
		return boomCode(4);
	}
	return boomCode(4);
}
function slashCommandReason($parts, $start){
	if(count($parts) <= $start){
		return '';
	}
	return trim(implode(' ', array_slice($parts, $start)));
}
function slashCommandDeny(){
	echo boomCode(4);
	die();
}
function slashCommandDelay($parts, $index, $default){
	$delay = $default;
	$reason_index = $index;
	if(isset($parts[$index]) && ctype_digit((string) $parts[$index])){
		$delay = (int) $parts[$index];
		$reason_index = $index + 1;
	}
	return array($delay, $reason_index);
}

if(empty($content) && $content !== '0' || !inRoom()){
	echo boomCode(4);
	die();
}

if(substr($command[0], 0, 1) !== '/'){
	echo boomCode(200);
	die();
}	
else if($base_command == '/topic'){
	if(!canTopic()){
		slashCommandDeny();
	}
	$topic = trim(implode(' ', array_slice($command, 1)));
	changeTopic($topic);
	$room = roomDetails($data['user_roomid']);
	if(!empty($room)){
		echo boomCode(14, array('data'=> getTopic($room)));
	}
	else {
		slashCommandDeny();
	}
	die();
}
else if(in_array($base_command, array('/clear', '/clearall', '/nuke', '/purge'), true)){
	if(!canClearRoom()){
		slashCommandDeny();
	}
	clearRoom($data['user_roomid']);
	echo boomCode(99);
	die();
}
else if($base_command == '/mute'){
	if(!canMute() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		list($delay, $reason_index) = slashCommandDelay($command, 2, 30);
		$reason = slashCommandReason($command, $reason_index);
		echo slashCommandActionCode(muteAccount($user['user_id'], $delay, $reason));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/unmute'){
	if(!canMute() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		echo slashCommandActionCode(unmuteAccount($user['user_id']));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/kick'){
	if(!canKick() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		list($delay, $reason_index) = slashCommandDelay($command, 2, 5);
		$reason = slashCommandReason($command, $reason_index);
		echo slashCommandActionCode(kickAccount($user['user_id'], $delay, $reason));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/unkick'){
	if(!canKick() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		echo slashCommandActionCode(unkickAccount($user['user_id']));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/ban'){
	if(!canBan() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		$reason = slashCommandReason($command, 2);
		echo slashCommandActionCode(banAccount($user['user_id'], $reason));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/unban'){
	if(!canBan() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		echo slashCommandActionCode(unbanAccount($user['user_id']));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/warn'){
	if(!canWarn() || !isset($command[1])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	if(!empty($user)){
		$reason = slashCommandReason($command, 2);
		echo slashCommandActionCode(warnAccount($user['user_id'], $reason));
		die();
	}
	slashCommandDeny();
}
else if($base_command == '/rename'){
	if(!canName() || !isset($command[1], $command[2])){
		slashCommandDeny();
	}
	$user = slashCommandUserByToken($command[1]);
	$new_name = trim(implode(' ', array_slice($command, 2)));
	if(!empty($user) && $new_name !== ''){
		if(canModifyName($user) && validName($new_name) && freeUsername($new_name, $user['user_id'])){
			$safe_name = escape($new_name);
			$mysqli->query("UPDATE boom_users SET user_name = '$safe_name', user_action = user_action + 1, pcount = pcount + 1, naction = naction + 1 WHERE user_id = '{$user['user_id']}'");
			changeNameLog($user, $new_name);
			redisUpdateUser($user['user_id']);
			echo boomCode(1);
			die();
		}
	}
	slashCommandDeny();
}
else if($base_command == '/logout'){
	if(!boomAllow(100)){
		slashCommandDeny();
	}
	$u = trim(implode(' ', array_slice($command, 1)));
	$user = userNameDetails($u);
	if(!empty($user)){
		if(canEditUser($user, 100)){
			updateUserSession($user);
			echo boomCode(1);
			die();
		}
	}
	echo boomCode(200);
	die();
}
else if($base_command == '/clearcache'){
	if(!boomAllow(100)){
		slashCommandDeny();
	}
	boomCacheUpdate();
	echo boomCode(1);
	die();
}
else {
	echo boomCode(200);
	die();
}
?>