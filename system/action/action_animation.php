<?php
require('../config_session.php');

if(!isset($_POST['save_animation'])){
	echo boomCode(0);
	die();
}

$master = isset($_POST['anim_master']) ? (int) escape($_POST['anim_master'], true) : 1;
$chatfx = isset($_POST['anim_chatfx']) ? (int) escape($_POST['anim_chatfx'], true) : 1;
$goofy = isset($_POST['anim_goofy']) ? (int) escape($_POST['anim_goofy'], true) : 1;
$overlay = isset($_POST['anim_overlay']) ? (int) escape($_POST['anim_overlay'], true) : 1;

saveUserData($data, 'anim_master', ($master > 0 ? 1 : 0));
saveUserData($data, 'anim_chatfx', ($chatfx > 0 ? 1 : 0));
saveUserData($data, 'anim_goofy', ($goofy > 0 ? 1 : 0));
saveUserData($data, 'anim_overlay', ($overlay > 0 ? 1 : 0));

$config = userAnimationConfig($data);

echo boomCode(1, [
	'config' => $config,
]);
die();
?>