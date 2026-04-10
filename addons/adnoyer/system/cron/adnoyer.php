<?php
$load_addons = 'adnoyer';
require(__DIR__ . '/../../../../system/config_cron.php');

if($addons['custom1'] == 0){
	die();
}

$addons = addonsDetails('adnoyer');
if(empty($addons)){
	die();
}
$data = userDetails($addons['bot_id']);
if(empty($data)){
	die();
}

function adnoyerPostChat($list, $content, $custom = array()){
	global $mysqli, $data;
	$values = '';
	foreach($list as $target){
		$c = $content[array_rand($content)];
		botPostChat($data['user_id'], $target, $c);
	}
	$list = listThisArray($list);
	$mysqli->query("UPDATE boom_rooms SET rcaction = rcaction + 1, room_action = '" . time() . "', adnoyer_time = '" . time() . "' WHERE room_id IN ($list)");
	redisUpdateRoomList($list);
}

mysqli_close($mysqli);

function runAdnoyer(){
	global $mysqli, $setting, $addons, $data;
	
	$prep = '';
	$list = [];
	$rlist = [];
	
	$mysqli->query("DELETE FROM boom_chat WHERE user_id = '{$addons['bot_id']}'");
	$get_adnoyer = $mysqli->query("SELECT * FROM boom_adnoyer WHERE adnoyer_id > 0");
	if($get_adnoyer->num_rows > 0){
		while($adno = $get_adnoyer->fetch_assoc()){
			array_push($list, $adno['adnoyer_content']);
		}
	}
	else {
		return false;
	}
	
	
	$check_room = $mysqli->query("
	SELECT boom_rooms.room_id, boom_rooms.adnoyer_time,
	(SELECT count(post_id) FROM boom_chat WHERE post_date >= boom_rooms.adnoyer_time AND user_id != '{$addons['bot_id']}' AND user_id != '{$setting['system_id']}' AND post_roomid = boom_rooms.room_id) AS log_count,
	(SELECT count(user_id) FROM boom_users WHERE user_roomid = boom_rooms.room_id AND last_action >= '" . getDelay() . "') AS user_count
	FROM boom_rooms
	");
	
	$later = time() - $addons['custom2'];
	
	while($r = $check_room->fetch_assoc()){
		if($r['adnoyer_time'] <= $later && $r['log_count'] >= $addons['custom4'] && $r['user_count'] >= $addons['custom5']){
			array_push($rlist, $r['room_id']);
		}
	}
	if(!empty($rlist) && !empty($list)){
		adnoyerPostChat($rlist, $list);
	}
}

$i = 0;
$max = 3;
while($i <= $max){
	$mysqli = @new mysqli(BOOM_DHOST, BOOM_DUSER, BOOM_DPASS, BOOM_DNAME);
	runAdnoyer();
	$i++;
	mysqli_close($mysqli);
	if($i <= $max){
		sleep(15);
	}
}
die();
?>