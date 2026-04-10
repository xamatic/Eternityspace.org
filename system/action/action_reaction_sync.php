<?php
require('../config_session.php');

function parseReactionSyncTargets($raw){
	$targets = [];
	$clean = trim((string) $raw);
	if($clean === ''){
		return $targets;
	}
	$list = explode(',', $clean);
	foreach($list as $item){
		$id = (int) $item;
		if($id < 1){
			continue;
		}
		$targets[$id] = 1;
		if(count($targets) >= 60){
			break;
		}
	}
	return array_keys($targets);
}

$chat_ids = parseReactionSyncTargets(isset($_POST['chat']) ? $_POST['chat'] : '');
$private_ids = parseReactionSyncTargets(isset($_POST['private']) ? $_POST['private'] : '');

$chat_reaction = [];
$private_reaction = [];

foreach($chat_ids as $chat_id){
	$log = getChatLog($chat_id);
	if(empty($log)){
		continue;
	}
	if((int) $log['post_roomid'] !== (int) $data['user_roomid']){
		continue;
	}
	if((int) $log['syslog'] > 0){
		continue;
	}
	$chat_reaction[$chat_id] = messageReactionData(1, $chat_id);
}

foreach($private_ids as $private_id){
	$log = getPrivateLog($private_id);
	if(empty($log)){
		continue;
	}
	if((int) $log['hunter'] !== (int) $data['user_id'] && (int) $log['target'] !== (int) $data['user_id']){
		continue;
	}
	$private_reaction[$private_id] = messageReactionData(2, $private_id);
}

echo boomCode(1, [
	'chat' => $chat_reaction,
	'private' => $private_reaction,
]);
die();
?>