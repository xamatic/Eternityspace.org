<?php
require(__DIR__ . '/../config_session.php');

function unlinkProfileMusic($file){
	$file = trim(str_replace(array('/', '\\', '..'), '', $file));
	if($file == ''){
		return;
	}
	$path = BOOM_PATH . '/music/' . $file;
	if(file_exists($path)){
		unlink($path);
	}
}

function canProfileMusicUpload(){
	global $setting, $data;
	if(boomAllow($setting['allow_pmusic']) && !featureBlock($data['bupload'])){
		return true;
	}
}

function profileMusicGoldCost(){
	global $setting;
	if(!isset($setting['pmusic_gold_cost'])){
		return 0;
	}
	$cost = (int) $setting['pmusic_gold_cost'];
	if($cost < 0){
		$cost = 0;
	}
	return $cost;
}

function addProfileMusic(){
	global $mysqli, $data;

	if(!canProfileMusicUpload()){
		return boomCode(0);
	}
	if(fileError()){
		return boomCode(0);
	}

	$info = pathinfo($_FILES['file']['name']);
	if(!isset($info['extension'])){
		return boomCode(0);
	}
	$extension = strtolower($info['extension']);
	if(!isMusic($extension)){
		return boomCode(0);
	}

	$cost = profileMusicGoldCost();
	if($cost > 0 && !walletBalance(1, $cost)){
		return boomCode(2);
	}

	$file_name = encodeFile($extension);
	boomMoveFile('music/' . $file_name);
	if(!sourceExist('music/' . $file_name)){
		return boomCode(0);
	}

	unlinkProfileMusic($data['user_pmusic']);
	$mysqli->query("UPDATE boom_users SET user_pmusic = '$file_name' WHERE user_id = '{$data['user_id']}'");
	if($cost > 0){
		removeWallet($data, 1, $cost);
	}
	redisUpdateUser($data['user_id']);
	return boomCode(1, array('data'=> BOOM_DOMAIN . 'music/' . $file_name));
}

function removeProfileMusic(){
	global $mysqli, $data;

	if(!canProfileMusicUpload()){
		return boomCode(0);
	}
	if(empty($data['user_pmusic'])){
		return boomCode(1);
	}

	unlinkProfileMusic($data['user_pmusic']);
	$mysqli->query("UPDATE boom_users SET user_pmusic = '' WHERE user_id = '{$data['user_id']}'");
	redisUpdateUser($data['user_id']);
	return boomCode(1);
}

function staffRemoveProfileMusic(){
	global $mysqli, $setting;

	$target = escape($_POST['staff_remove_pmusic'], true);
	$user = userDetails($target);
	if(empty($user)){
		return boomCode(0);
	}
	if(!canEditUser($user, $setting['can_pmusic'])){
		return boomCode(0);
	}

	unlinkProfileMusic($user['user_pmusic']);
	$mysqli->query("UPDATE boom_users SET user_pmusic = '' WHERE user_id = '{$user['user_id']}'");
	redisUpdateUser($user['user_id']);
	return boomCode(1);
}

if(isset($_FILES['file'], $_POST['upload_music'])){
	echo addProfileMusic();
	die();
}
if(isset($_POST['remove_pmusic'])){
	echo removeProfileMusic();
	die();
}
if(isset($_POST['staff_remove_pmusic'])){
	echo staffRemoveProfileMusic();
	die();
}
die();
?>