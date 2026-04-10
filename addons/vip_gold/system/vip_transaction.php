<?php
$load_addons = 'vip_gold';
require(__DIR__ . '/../../../system/config_addons.php');

if(!isset($_POST['type'], $_POST['plan'], $_POST['username'])){
	echo boomCode(0);
	die();
}

$type = escape($_POST['type'], true);
$plan = escape($_POST['plan'], true);
$target = escape($_POST['username']);

if(!vipValidPlan($plan)){
	echo boomCode(0);
	die();
}

$amount = vipPrice($plan);

if($amount == 0){
	echo boomCode(0);
	die();
}

if(!goldBalance($amount)){
	echo boomCode(4);
	die();
}
if($type == 1){
	if(!validVipUser($data)){
		echo boomCode(0);
		die();
	}
	$account = $data;
}
else if($type == 2){
	if(empty($target)){
		echo boomCode(3);
		die();
	}
	$user = userNameDetails($target);
	if(empty($user)){
		echo boomCode(3);
		die();
	}
	if(!validVipUser($user, 1)){
		echo 2;
		die();
	}
	$account = $user;
}
else {
	echo boomCode(0);
	die();
}
recordVip($account, $plan);
echo boomCode(1);
die();
?>