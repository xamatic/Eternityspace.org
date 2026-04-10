<?php
require('../config_session.php');

if(!isset($_POST['scope'], $_POST['target']) || (!isset($_POST['react']) && !isset($_POST['react_key']))){
	echo boomCode(0);
	die();
}

$scope_name = boomSanitize($_POST['scope']);
$target = escape($_POST['target'], true);
$react_key = '';
$legacy_react = 0;

if(isset($_POST['react_key'])){
	$react_key = trim(escape($_POST['react_key']));
}
else if(isset($_POST['react'])){
	$legacy_react = (int) escape($_POST['react'], true);
	if(validMessageReaction($legacy_react)){
		$react_key = legacyReactionKey($legacy_react);
	}
	else {
		$react_key = trim(escape($_POST['react']));
	}
}

$react_value = reactionValueFromKey($react_key);
$scope = 0;

if($scope_name == 'chat'){
	$scope = 1;
}
else if($scope_name == 'private'){
	$scope = 2;
}

if($target < 1 || $scope == 0 || !validReactionKey($react_key)){
	echo boomCode(0);
	die();
}

if($scope == 1){
	$log = getChatLog($target);
	if(empty($log) || (int) $log['post_roomid'] !== (int) $data['user_roomid'] || (int) $log['syslog'] > 0){
		echo boomCode(0);
		die();
	}
}
if($scope == 2){
	$log = getPrivateLog($target);
	if(empty($log)){
		echo boomCode(0);
		die();
	}
	if((int) $log['hunter'] !== (int) $data['user_id'] && (int) $log['target'] !== (int) $data['user_id']){
		echo boomCode(0);
		die();
	}
}

try {
	ensureMessageReactionTable();
	$existing = $mysqli->query("SELECT id, react_value, react_key FROM boom_message_react WHERE react_scope = '$scope' AND react_target = '$target' AND react_user = '{$data['user_id']}' LIMIT 1");

	if($existing && $existing->num_rows > 0){
		$current = $existing->fetch_assoc();
		$current_key = reactionKeyFromRow($current);
		if($current_key === $react_key){
			$mysqli->query("DELETE FROM boom_message_react WHERE id = '{$current['id']}'");
		}
		else {
			$mysqli->query("UPDATE boom_message_react SET react_value = '$react_value', react_key = '$react_key', react_time = '" . time() . "' WHERE id = '{$current['id']}'");
		}
	}
	else {
		$mysqli->query("INSERT INTO boom_message_react (react_scope, react_target, react_user, react_value, react_key, react_time) VALUES ('$scope', '$target', '{$data['user_id']}', '$react_value', '$react_key', '" . time() . "')");
	}
}
catch(Throwable $e) {
	echo boomCode(0);
	die();
}

echo boomCode(1, array(
	'scope'=> $scope_name,
	'target'=> (int) $target,
	'reaction'=> messageReactionData($scope, $target),
));
die();
?>