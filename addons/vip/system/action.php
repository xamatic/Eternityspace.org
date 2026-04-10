<?php
$load_addons = 'vip';
require(__DIR__ . '/../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}

function saveVipConfig(){
	global $mysqli, $data;
	
	$paypal_id = escape($_POST['paypal_id']);
	$paypal_secret = escape($_POST['paypal_secret']);
	$paypal_mode = escape($_POST['paypal_mode']);
	$currency = escape($_POST['currency']);
	$plan1 = escape($_POST['plan1']);
	$plan2 = escape($_POST['plan2']);
	$plan3 = escape($_POST['plan3']);
	$plan4 = escape($_POST['plan4']);
	$plan5 = escape($_POST['plan5']);

	if(!is_numeric($plan1)){
		return 0;
	}
	if(!is_numeric($plan2)){
		return 0;
	}
	if(!is_numeric($plan3)){
		return 0;
	}
	if(!is_numeric($plan4)){
		return 0;
	}
	if(!is_numeric($plan5)){
		return 0;
	}
	if($paypal_secret == '' || $paypal_id == ''){
		$paypal_mode = 0;
	}
	$plan1 = vipFormat(escape($_POST['plan1']), $currency);
	$plan2 = vipFormat(escape($_POST['plan2']), $currency);
	$plan3 = vipFormat(escape($_POST['plan3']), $currency);
	$plan4 = vipFormat(escape($_POST['plan4']), $currency);
	$plan5 = vipFormat(escape($_POST['plan5']), $currency);
	$mysqli->query("
	UPDATE boom_addons SET 
	custom1 = '$plan1', custom2 = '$plan2', custom3 = '$plan3', custom4 = '$plan4', custom5 = '$plan5',
	custom6 = '$paypal_mode', custom7 = '$currency', custom9 = '$paypal_id', custom10 = '$paypal_secret'
	WHERE addons = 'vip'
	");
	redisUpdateAddons('vip');
	return 5;
}
function vipAddUserPlan(){
	global $mysqli, $data, $addons, $setting, $lang;
	
	$target = escape($_POST['vip_user']);
	$plan = escape($_POST['vip_plan']);

	if(!vipValidPlan($plan)){
		return 0;
	}
	$user = userNameDetails($target);
	if(empty($user)){
		return 2;
	}
	if(isGuest($user)){
		return 3;
	}
	if($user['user_rank'] > 2){
		return 3;
	}
	if(isPaidVip($user)){
		return 3;
	}
	require(vipLang($user));
	$new_time = vipNewTime($plan, $user);
	if($plan == 5){
		$message = escape($lang['vip_gift2']);
	}
	else {
		$message = str_replace('%vipdate%', longDate($new_time), escape($lang['vip_gift']));
	}
	$mysqli->query("UPDATE boom_users SET user_rank = 50, vip_end = '$new_time', user_action = user_action + 1 WHERE user_id = '{$user['user_id']}'");
	clearNotifyAction($user['user_id'], 'rank_change');
	systemPostPrivate($user['user_id'], $message);
	boomNotify('rank_change', array('target'=> $user['user_id'], 'source'=> 'rank_change', 'rank'=> 50));
	redisUpdateUser($user['user_id']);
	$gift_code = 'SYSTEM' . time() . $data['user_id'];
	$sale = array(
		'user'=> $user['user_id'],
		'userp'=> $data['user_id'],
		'plan'=> $plan,
		'price'=> '0.00',
		'currency'=> $addons['custom7'],
		'gateway'=> 'System',
		'invoice'=> $gift_code,
		'order_id'=> $gift_code,
		'email'=> $setting['site_email'],
		'vdate'=> time(),
		'status'=> 'completed',
	);
	$record = vipTransaction($sale);
	$user2 = userDetails($user['user_id']);
	if(!empty($user2)){
		return boomAddonsTemplate('../addons/vip/system/template/vip_user', $user2);
	}
	else {
		return 0;
	}
}
function vipFindUserTransaction(){
	global $mysqli, $data, $lang;
	$find = escape($_POST['search_vip']);
	$list = '';
	if($find == ''){
		return emptyZone($lang['empty']);
	}
	$user = userNameDetails($find);
	if(!empty($user)){
		$get_info = $mysqli->query("SELECT * FROM vip_transaction WHERE userid = '{$user['user_id']}' OR order_id = '$find' OR invoice = '$find' ORDER BY id DESC LIMIT 100");
	}
	else {
		$get_info = $mysqli->query("SELECT * FROM vip_transaction WHERE order_id = '$find' OR invoice = '$find' ORDER BY id DESC LIMIT 100");
	}
	if($get_info->num_rows > 0){
		while($result = $get_info->fetch_assoc()){
			$list .= boomAddonsTemplate('../addons/vip/system/template/vip_transaction', $result);
		}
		return $list;
	}
	else {
		return emptyZone($lang['empty']);
	}
}
function vipSingleClean(){
	global $mysqli, $data;
	
	$target = escape($_POST['vip_cancel']);
	
	$user = userDetails($target);
	if(empty($user)){
		return 2;
	}
	$new_rank = 1;
	if($user['user_rank'] != 50){
		$new_rank = $user['user_rank'];
	}
	userReset($user, $new_rank);
	$mysqli->query("UPDATE boom_users SET vip_end = 0 WHERE user_id = '{$user['user_id']}'");
	redisUpdateUser($user['user_id']);
	return 1;
}
function vipSearchUser(){
	global $mysqli, $data, $lang;
	
	$target = escape($_POST['vip_search_user']);
	
	$t = time();
	$list = '';
	$get_user = $mysqli->query("SELECT * FROM boom_users WHERE user_name LIKE '$target%' AND vip_end > $t ORDER BY user_name LIMIT 1000");
	if($get_user->num_rows > 0){
		while($user = $get_user->fetch_assoc()){
			$list .= boomAddonsTemplate('../addons/vip/system/template/vip_user', $user);
		}
		return $list;
	}
	else {
		return emptyZone($lang['empty']);
	}
}
if(isset($_POST['plan1'], $_POST['plan2'], $_POST['plan3'], $_POST['plan4'], $_POST['plan5'], $_POST['paypal_id'], $_POST['paypal_secret'], $_POST['currency'], $_POST['paypal_mode'])){
	echo saveVipConfig();
	die();
}
if(isset($_POST['load_transaction'])){
	echo vipLoadTransaction();
	die();
}
if(isset($_POST['vip_user'], $_POST['vip_plan'], $_POST['vip_add'])){
	echo vipAddUserPlan();
	die();
}
if(isset($_POST['search_vip'])){
	echo vipFindUserTransaction();
	die();
}
if(isset($_POST['vip_search_user'])){
	echo vipSearchUser();
	die();
}
if(isset($_POST['vip_load_list'])){
	echo vipLoadList();
	die();
}
if(isset($_POST['vip_cancel'])){
	echo vipSingleClean();
	die();
}
?>