<?php
$load_addons = 'vip_gold';
require(__DIR__ . '/../../../system/config_addons.php');

if(!boomAllow(100)){
	die();
}

function vipAddUserPlan(){
	global $mysqli, $data, $lang;
	
	$target = escape($_POST['vip_user']);
	$plan = escape($_POST['vip_plan'], true);

	if(!vipValidPlan($plan)){
		return 0;
	}
	$user = userNameDetails($target);
	if(empty($user)){
		return 2;
	}
	if(!validVipUser($user, 1)){
		return 3;
	}
	recordVip($user, $plan, 0);
	$user = userDetails($user['user_id']);
	if(!empty($user)){
		return boomTemplate('../addons/vip_gold/system/template/vip_user', $user);
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
	if(empty($user)){
		return emptyZone($lang['empty']);
	}
	$get_info = $mysqli->query("
		SELECT boom_vip.*, boom_users.user_id, boom_users.user_name, boom_users.user_tumb
		FROM boom_vip
		LEFT JOIN boom_users
		ON boom_users.user_id = boom_vip.userid
		WHERE boom_vip.userid = '{$user['user_id']}'
		ORDER BY boom_vip.id DESC LIMIT 100
	");
	if($get_info->num_rows > 0){
		while($result = $get_info->fetch_assoc()){
			$list .= boomTemplate('../addons/vip_gold/system/template/vip_transaction', $result);
		}
		return $list;
	}
	else {
		return emptyZone($lang['empty']);
	}
}
function vipLoadTransaction(){
	global $mysqli, $data, $lang;

	$list = '';
	$get_transaction = $mysqli->query("
		SELECT boom_vip.*, boom_users.user_id, boom_users.user_name, boom_users.user_tumb
		FROM boom_vip
		LEFT JOIN boom_users
		ON boom_users.user_id = boom_vip.userid
		ORDER BY boom_vip.id DESC LIMIT 100
	");
	if($get_transaction->num_rows > 0){
		while($result = $get_transaction->fetch_assoc()){
			$list .= boomTemplate('../addons/vip_gold/system/template/vip_transaction', $result);
		}
		return $list;
	}
	else {
		return emptyZone($lang['empty']);
	}
}
function saveVipGold(){
	global $mysqli;
	$plan1 = round(escape($_POST['plan1'], true));
	$plan2 = round(escape($_POST['plan2'], true));
	$plan3 = round(escape($_POST['plan3'], true));
	$plan4 = round(escape($_POST['plan4'], true));
	$plan5 = round(escape($_POST['plan5'], true));
	$status = escape($_POST['status'], true);

	if(!validVipPrice($plan1) || !validVipPrice($plan2) || !validVipPrice($plan3) || !validVipPrice($plan4) || !validVipPrice($plan5)){
		return 99;
	}
	
	$mysqli->query("UPDATE boom_addons SET custom1 = '$plan1', custom2 = '$plan2', custom3 = '$plan3', custom4 = '$plan4', custom5 = '$plan5', custom6 = '$status' WHERE addons = 'vip_gold'");
	redisUpdateAddons('vip_gold');
	return 1;
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
if(isset($_POST['plan1'], $_POST['plan2'], $_POST['plan3'], $_POST['plan4'], $_POST['plan5'], $_POST['status'])){
	echo saveVipGold();
	die();
}
?>