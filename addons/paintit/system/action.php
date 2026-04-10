<?php
$load_addons = 'paintit';
require(__DIR__ . '/../../../system/config_addons.php');

if(isset($_POST['set_paint_access'], $_POST['set_paint_main'], $_POST['set_paint_private'])){
	if(!canManageAddons()){
		die();
	}
	$paint_access = escape($_POST['set_paint_access'], true);
	$paint_main = escape($_POST['set_paint_main'], true);
	$paint_private = escape($_POST['set_paint_private'], true);
	$mysqli->query("UPDATE boom_addons set addons_access = '$paint_access', custom1 = '$paint_main', custom2 = '$paint_private' WHERE addons = 'paintit' ");
	redisUpdateAddons('paintit');
	echo 5;
}

?>