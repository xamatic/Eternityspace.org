<?php
$load_addons = 'voice_record';
require(__DIR__ . '/../../../system/config_addons.php');

function saveVoiceRecord(){
	global $mysqli, $data, $lang;
	$voice_access = escape($_POST['set_voice_access'], true);
	$voice_main = escape($_POST['set_voice_main'], true);
	$voice_main_time = escape($_POST['set_voice_main_time'], true);
	$voice_private = escape($_POST['set_voice_private'], true);
	$voice_private_time = escape($_POST['set_voice_private_time'], true);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$voice_access', custom2 = '$voice_main', custom3 = '$voice_private', custom4 = '$voice_main_time', custom5 = '$voice_private_time' WHERE addons = 'voice_record'");
	redisUpdateAddons('voice_record');
	return boomSuccess('saved');
}
if(isset($_POST['set_voice_access'], $_POST['set_voice_main'], $_POST['set_voice_main_time'], $_POST['set_voice_private'], $_POST['set_voice_private_time']) && canManageAddons()){
	echo saveVoiceRecord();
	die();
}
?>