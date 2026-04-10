<?php
function superbot($string){
	global $data, $addons;
	if(stripos($string, '%user%') !== false){
		$string = str_replace('%user%', $data['user_name'], $string);
	}
	if(stripos($string, '%bot%') !== false){
		$string = str_replace('%bot%', $addons['bot_name'], $string);
	}
	if(stripos($string, '%time%') !== false){
		$string = str_replace('%time%', date("H:i"), $string);
	}
	if(stripos($string, '%members%') !== false){
		$string = str_replace('%members%', countRegister(), $string);
	}
	if(stripos($string, '%male%') !== false){
		$string = str_replace('%male%', countMale(), $string);
	}
	if(stripos($string, '%female%') !== false){
		$string = str_replace('%female%', countFemale(), $string);
	}
	return softEscape($string);
}
function superbotReg($string){
	global $mysqli, $addons;
	$result = '';
	$search = trim(str_ireplace(array('?', $addons['bot_name']), '', $string));
	$sbot = $mysqli->query("SELECT * FROM superbot_data WHERE superbot_question = '$search' ORDER BY RAND() LIMIT 1");
	if($sbot->num_rows > 0){
		$search_result = $sbot->fetch_array(MYSQLI_BOTH);
		$result = $search_result['superbot_answer'];		
	}
	return $result;
}
function superbotParse($result){
	$result = superbot($result);
	$result = emoprocess($result);
	$result = linking($result);
	$result = emoticon($result);
	return $result;
}
function superbotQuestion($question){
	$question = escape($_POST['question']);
	$question = str_replace(array('?'), '', $question);
	$question = preg_replace('!\s+!', ' ', $question);
	$question = trim($question);
	return $question;
}
function countRegister(){
	global $mysqli;
	$count = $mysqli->query("SELECT count(user_id) FROM boom_users WHERE user_id > 0");
	$cal = $count->fetch_array(MYSQLI_BOTH);
	return $cal[0];
}
function countFemale(){
	global $mysqli;
	$count = $mysqli->query("SELECT count(user_id) FROM boom_users WHERE user_sex = 2");
	$cal = $count->fetch_array(MYSQLI_BOTH);
	return $cal[0];
}
function countMale(){
	global $mysqli;
	$count = $mysqli->query("SELECT count(user_id) FROM boom_users WHERE user_sex = 1");
	$cal = $count->fetch_array(MYSQLI_BOTH);
	return $cal[0];
}
?>