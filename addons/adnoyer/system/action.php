<?php
$load_addons = 'adnoyer';
require('../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}

if(isset($_POST['adnoyer_status'], $_POST['adnoyer_delay'], $_POST['adnoyer_hide'], $_POST['adnoyer_users'], $_POST['adnoyer_mlogs'])){
	$status = escape($_POST['adnoyer_status'], true);
	$delay = escape($_POST['adnoyer_delay'], true);
	$hide = escape($_POST['adnoyer_hide'], true);
	$users = escape($_POST['adnoyer_users'], true);
	$mlogs = escape($_POST['adnoyer_mlogs'], true);
	$mysqli->query("UPDATE boom_addons SET custom1 = '$status', custom2 = '$delay', custom3 = '$hide', custom4 = '$mlogs', custom5 = '$users' WHERE addons = '$load_addons'");
	redisUpdateAddons('adnoyer');
	echo 5;
	die();
}
if(isset($_POST['adnoyer_save'], $_POST['adnoyer_title'], $_POST['adnoyer_content'])){
	$id = escape($_POST['adnoyer_save'], true);
	$title = escape($_POST['adnoyer_title']);
	$content = clearBreak($_POST['adnoyer_content']);
	$content = adnoyerEscape($content);
	$mysqli->query("UPDATE boom_adnoyer SET adnoyer_title = '$title', adnoyer_content = '$content' WHERE adnoyer_id = '$id'");
	$get_back = $mysqli->query("SELECT * FROM boom_adnoyer WHERE adnoyer_id = '$id'");
	if($get_back->num_rows == 1){
		$adnoyer = $get_back->fetch_assoc();
		$content = boomAddonsTemplate('../addons/adnoyer/system/template/adnoyer_data', $adnoyer);
		echo boomCode(1, array('data'=> $content));
		die();
	}
	else {
		echo boomCode(0);
		die();
	}
}
if(isset($_POST['delete_adnoyer'])){
	$id = escape($_POST['delete_adnoyer'], true);
	$mysqli->query("DELETE FROM boom_adnoyer WHERE adnoyer_id = '$id'");
	echo 1;
	die();
}
if(isset($_POST['adnoyer_new'], $_POST['adnoyer_title'], $_POST['adnoyer_content'])){
	$title = escape($_POST['adnoyer_title']);
	$content = clearBreak($_POST['adnoyer_content']);
	$content = adnoyerEscape($content);
	$mysqli->query("INSERT INTO boom_adnoyer (adnoyer_title, adnoyer_content, adnoyer_date) VALUES ('$title', '$content', '" . time() . "')");
	$last_id = $mysqli->insert_id;
	$get_back = $mysqli->query("SELECT * FROM boom_adnoyer WHERE adnoyer_id = '$last_id'");
	if($get_back->num_rows == 1){
		$adnoyer = $get_back->fetch_assoc();
		$content = boomAddonsTemplate('../addons/adnoyer/system/template/adnoyer_data', $adnoyer);
		echo boomCode(1, array('data'=> $content));
		die();
	}
	else {
		echo boomCode(0);
		die();
	}
}
?>