<?php
// vip functions

function useVip(){
	global $addons;
	if($addons['custom6'] > 0){
		return true;
	}
}
function vipValidPlan($plan){
	$valid = array(1,2,3,4,5,6,7,8,9);
	if(in_array($plan, $valid)){
		return true;
	}
}
function validVipPrice($v){
	if(is_numeric($v) && $v >= 0 && $v < 2147483647){
		return true;
	}
}
function vipPrice($plan){
	global $addons;
	switch($plan){
		case 1:
		case 2:
		case 3:
		case 4:
		case 5:
			return $addons['custom' . $plan];
		case 6:
			return (isset($addons['custom7']) && is_numeric($addons['custom7'])) ? $addons['custom7'] : 10;
		case 7:
			return (isset($addons['custom8']) && is_numeric($addons['custom8'])) ? $addons['custom8'] : 15;
		case 8:
			return (isset($addons['custom9']) && is_numeric($addons['custom9'])) ? $addons['custom9'] : 30;
		case 9:
			return (isset($addons['custom10']) && is_numeric($addons['custom10'])) ? $addons['custom10'] : 60;
		default:
			return 0;
	}
}
function vipPlanName($plan){
	global $lang;
	return $lang['vplan' . $plan];
}
function vipNewTime($plan, $user){
	$ctime = time();
	if($user['vip_end'] > time()){
		$ctime = $user['vip_end'];
	}
	switch($plan){
		case 1:
			return strtotime('+7 day', $ctime);
		case 2: 
			return strtotime('+1 month', $ctime);
		case 3:
			return strtotime('+3 month', $ctime);
		case 4:
			return strtotime('+12 month', $ctime);
		case 5:
			return 2147483647;
		case 6:
			return strtotime('+1 month', $ctime);
		case 7:
			return strtotime('+3 month', $ctime);
		case 8:
			return strtotime('+12 month', $ctime);
		case 9:
			return 2147483647;
		default:
			return $user['vip_end'];
	}
}
function recordVip($user, $plan, $paid = 1){
	global $mysqli, $data, $lang;
	if($paid == 0){
		$price = '0';
	}
	else {
		$price = vipPrice($plan);
	}
	$mysqli->query("INSERT INTO boom_vip (userid, userp, plan, price, vdate) VALUES ('{$data['user_id']}', '{$user['user_id']}', '{$plan}', '{$price}', '" . time() . "')");
	$mysqli->query("UPDATE boom_users SET user_rank = 50, vip_end = '" . vipNewTime($plan, $user) . "', user_action = user_action + 1 WHERE user_id = '{$user['user_id']}'");
	clearNotifyAction($user['user_id'], 'rank_change');
	if(!myself($user['user_id']) && $paid == 1){
		boomNotify('vipgift', array('hunter'=> $data['user_id'], 'target'=> $user['user_id'], 'source'=> 'vgift', 'icon'=> 'vip'));
	}
	if($paid == 0){
		boomNotify('vipsys', array('target'=> $user['user_id'], 'source'=> 'vgift', 'icon'=> 'vip'));
	}
	if($paid == 1){
		removeGold($data, $price);
	}
	redisUpdateUser($data['user_id']);
	redisUpdateUser($user['user_id']);
}
function vipEndingDate($val){
	global $lang;
	if($val == 2147483647){
		return $lang['vip_life'];
	}
	else {
		return vipDate($val);
	}
}
function maxVip($user){
	if($user['vip_end'] >= 2147483647){
		return true;
	}
}
function vipDate($date){
	return date("Y-m-d", $date);
}
function validVipUser($user, $me = 0){
	if($me == 1 && myself($user['user_id'])){
		return false;
	}
	if(isGuest($user) || isBot($user) || maxVip($user)){
		return false;
	}
	if($user['user_rank'] > 50){
		return false;
	}
	if($user['user_rank'] == 50 && $user['vip_end'] == 0){
		return false;
	}
	return true;
}
?>