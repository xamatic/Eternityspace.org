<?php
$load_addons = 'superbot';
require(__DIR__ . '/../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}
function addSuperbotQuestion(){
	global $mysqli, $data;
	
	$addsuperbot = '';
	$question = superbotQuestion($_POST['question']);
	if(boomAllow(100)){
		$answer1 = softEscape($_POST['answer1']);
		$answer2 = softEscape($_POST['answer2']);
		$answer3 = softEscape($_POST['answer3']);
		$answer4 = softEscape($_POST['answer4']);
		$answer5 = softEscape($_POST['answer5']);
	}
	else {
		$answer1 = escape($_POST['answer1']);
		$answer2 = escape($_POST['answer2']);
		$answer3 = escape($_POST['answer3']);
		$answer4 = escape($_POST['answer4']);
		$answer5 = escape($_POST['answer5']);
	}
	$answer1 = preg_replace('!\s+!', ' ', $answer1);
	$answer2 = preg_replace('!\s+!', ' ', $answer2);
	$answer3 = preg_replace('!\s+!', ' ', $answer3);
	$answer4 = preg_replace('!\s+!', ' ', $answer4);
	$answer5 = preg_replace('!\s+!', ' ', $answer5);
	
	$query = '';
	if($answer1 != ''){
		$query .= "('$question', '$answer1')";
	}
	if($answer2 != ''){
		if($query != ''){
			$query .= ',';
		}
		$query .= "('$question', '$answer2')";
	}
	if($answer3 != ''){
		if($query != ''){
			$query .= ',';
		}
		$query .= "('$question', '$answer3')";
	}
	if($answer4 != ''){
		if($query != ''){
			$query .= ',';
		}
		$query .= "('$question', '$answer4')";
	}
	if($answer5 != ''){
		if($query != ''){
			$query .= ',';
		}
		$query .= "('$question', '$answer5')";
	}
	if($query != ''){
		$mysqli->query("INSERT INTO superbot_data (superbot_question, superbot_answer) VALUES $query");
		return 1;
	}
	else {
		return 0;
	}
}
function findSuperbot(){
	global $mysqli, $data, $lang;
	if(trim($_POST['find_in_bot']) == ''){
		return 0;
	}
	$search = escape($_POST['find_in_bot']);
	$list_bot = '';
	$search_answer = $mysqli->query("SELECT * FROM superbot_data WHERE superbot_answer LIKE '%$search%' OR superbot_question LIKE '%$search%' ORDER BY superbot_answer LIMIT 250");
	if( $search_answer->num_rows > 0){
		if($search_answer->num_rows > 0){
			while($ra = $search_answer->fetch_array()){
				$list_bot .= boomAddonsTemplate('../addons/superbot/system/template/superbot_data', $ra);			
			}
		}
		return $list_bot;
	}
	else {
		return emptyZone($lang['no_data']);
	}
}
if(isset($_POST['question'], $_POST['answer1'], $_POST['answer2'], $_POST['answer3'], $_POST['answer4'], $_POST['answer5'])){
	echo addSuperbotQuestion();
	die();
}
else if (isset($_POST['find_in_bot'])){
	echo findSuperbot();
	die();
}
else if(isset($_POST['sbdelete'])){
	$sbdelete = escape($_POST['sbdelete'], true);
	$mysqli->query("DELETE FROM superbot_data WHERE id = '$sbdelete'");
	echo 2452432;
}
else if(isset($_POST['set_superbot_access']) && canManageAddons()){
	$superbot_access = escape($_POST['set_superbot_access'], true);
	$mysqli->query("UPDATE boom_addons SET addons_access = '$superbot_access'WHERE addons = 'superbot'");
	redisUpdateAddons('superbot');
	echo 5;
	die();
}
else {
	echo 2;
	die();
}
?>