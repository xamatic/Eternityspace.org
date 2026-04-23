<?php
function setToken(){
	global $data, $mysqli;
	if(!empty($_SESSION[BOOM_PREFIX . 'token'])){
		$session = $_SESSION[BOOM_PREFIX . 'token'];
	}
	else {
		$session = generateToken(60);
		$_SESSION[BOOM_PREFIX . 'token'] = $session;
	}
	return $session;
}

function generateToken($s) {
    $ch = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cl = strlen($ch);
    $key = '';
    for ($i = 0; $i < $s; $i++) {
        $key .= $ch[random_int(0, $cl - 1)];
    }
    return $key;
}
function sessionCleanup(){
	unset($_SESSION[BOOM_PREFIX . 'token']);
}
function closeSession(){
	session_write_close();
}
function checkToken(){
	if(!isset($_COOKIE[BOOM_PREFIX . 'userid'], $_COOKIE[BOOM_PREFIX . 'utk'], $_COOKIE[BOOM_PREFIX . 'ssid'])){
		return false;
	}
    if (!isset($_POST['token'], $_SESSION[BOOM_PREFIX . 'token'])) {
        return false;
    }
	if(empty($_SESSION[BOOM_PREFIX . 'token'])){
		return false;
	}
	if($_POST['token'] == $_SESSION[BOOM_PREFIX . 'token']){
		return true;
	}
}
function clearUserSession(){
	unsetBoomCookie();
	sessionCleanup();
}
function genSession(){
	return rand(1111, 9999) . rand(11111,99999);
}
function validSession(){
	global $data;
	if(isset($_COOKIE[BOOM_PREFIX . 'ssid']) && $data['session_id'] == $_COOKIE[BOOM_PREFIX . 'ssid']){
		return true;
	}
}
function updateUserSession($user, $c = false){
	global $mysqli;
	$new_session = genSession();
	$mysqli->query("UPDATE boom_users SET session_id = '$new_session' WHERE user_id = '{$user['user_id']}'");
	if($c == true){
		setBoomCookie($user, ['session'=> $new_session]);
	}
}
function validAjax(){
	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		return true;
	}
}
function mainBlocked(){
	if(mainMuted() || checkFlood()){
		return true;
	}
}
function privateBlocked(){
	if(privateMuted() || checkFlood()){
		return true;
	}
}
function postBlocked(){
	if(muted() || checkFlood()){
		return true;
	}
}
function privCheck(){
	global $setting;
	if(!boomAllow($setting['allow_private'])){
		return 'fhide';
	}
}
function getIp(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $cloud =   @$_SERVER["HTTP_CF_CONNECTING_IP"];
    $remote  = $_SERVER['REMOTE_ADDR'];
    if(filter_var($cloud, FILTER_VALIDATE_IP)) {
        $ip = $cloud;
    }
    else if(filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    }
    else{
        $ip = $remote;
    }
    return escape($ip);
}
function createInfo($v){
	return '<i class="fa fa-question-circle theme_color infopop" data="' . $v . '"></i>';
}
function boomTemplate($getpage, $boom = '') {
	global $mysqli, $setting, $data, $lang;
    $page = BOOM_PATH . '/system/' . $getpage . '.php';
    $structure = '';
    ob_start();
    require($page);
    $structure = ob_get_clean();
    return $structure;
}
function boomAddonsTemplate($getpage, $boom = '') {
	global $mysqli, $setting, $data, $addons, $lang;
    $page = BOOM_PATH . '/system/' . $getpage . '.php';
    $structure = '';
    ob_start();
    require($page);
    $structure = ob_get_clean();
    return $structure;
}
function calHour($h){
	return time() - ($h * 3600);
}
function calWeek($w){
	return time() - ( 3600 * 24 * 7 * $w);
}
function calmonth($m){
	return time() - ( 3600 * 24 * 30 * $m);
}
function calDay($d){
	return time() - ($d * 86400);
}
function calSecond($sec){
	return time() - $sec;
}
function calMinutes($min){
	return time() - ($min * 60);
}
function calHourUp($h){
	return time() + ($h * 3600);
}
function calWeekUp($w){
	return time() + ( 3600 * 24 * 7 * $w);
}
function calmonthUp($m){
	return time() + ( 3600 * 24 * 30 * $m);
}
function calDayUp($d){
	return time() + ($d * 86400);
}
function calMinutesUp($min){
	return time() + ($min * 60);
}
function calSecondUp($sec){
	return time() + $sec;
}
function myColor($u){
	return $u['user_color'];
}
function myColorFont($u){
	return $u['user_color'] . ' ' . $u['user_font'];
}
function myBubbleColor($u){
	return $u['bccolor'] . ' ' . $u['bcbold'] . ' ' . $u['bcfont'];
}
function myPrivateBubbleColor($u){
	return $u['bccolor'];
}
function vCheck($val){
	if(strlen($val) == 36){
		return true;
	}
}
function boomFileVersion(){
	global $setting;
	if($setting['bbfv'] > 1.0){
		return '?v=' . $setting['bbfv'];
	}
	return '';
}
function checkAvatar($a){
	return myAvatar($a);
}
function avatarFile($a){
	if(empty($a) || defaultAvatar($a)){
		return 'default_avatar.png';
	}
	static $avatar_cache = [];
	if(isset($avatar_cache[$a])){
		return $avatar_cache[$a];
	}
	$avatar_path = BOOM_PATH . '/avatar/' . $a;
	if(!file_exists($avatar_path)){
		$avatar_cache[$a] = 'default_avatar.png';
	}
	else {
		$avatar_cache[$a] = $a;
	}
	return $avatar_cache[$a];
}
function checkUsername($n){
	if(empty($n)){
		$n = 'N/A';
	}
	return $n;
}
function myAvatar($a){
	$a = avatarFile($a);
	if(defaultAvatar($a)){
		return 'default_images/avatar/' . $a;
	}
	return BOOM_DOMAIN . 'avatar/' . $a;
}
function imgLoader(){
	return 'default_images/misc/holder.png';
}
function defaultAvatar($a){
	if(stripos($a, 'default') !== false){
		return true;
	}
}
function myRoomIcon($a){
	if(defaultRoomIcon($a)){
		return 'default_images/rooms/' . $a;
	}
	return BOOM_DOMAIN . 'room_icon/' . $a;
}
function defaultRoomIcon($a){
	if(stripos($a, 'default') !== false){
		return true;
	}
}
function myCover($a){
	return BOOM_DOMAIN . 'cover/' . $a;
}
function getCover($user){
	if(userHaveCover($user)){
		return 'style="background-image: url(' . myCover($user['user_cover']) . ');"';
	}
}
function coverClass($user){
	if(userHaveCover($user)){
		return 'cover_size';
	}
}
function userHaveCover($user){
	if($user['user_cover'] != ''){
		return true;
	}
}

// mobile function

function getMobile() {
	$list = array('mobile','phone','iphone','ipad','ipod','android','silk','kindle','blackberry','opera Mini','opera Mobi','symb');
	foreach($list as $val){
		if(stripos($_SERVER['HTTP_USER_AGENT'], $val) !== false){
			return 1;
		}
	}
	return 0;
} 

function getIcon($icon, $c){
	return '<img class="' . $c . '" src="default_images/icons/' . $icon . boomFileVersion() . '"/>';
}
function boomCode($code, $custom = array()){
	$def = array('code'=> $code);
	$res = array_merge($def, $custom);
	return json_encode( $res, JSON_UNESCAPED_UNICODE);
}
function escape($t, $i = false){
	global $mysqli;
	if($i === true){
		return intval($t);
	}
	else {
		return $mysqli->real_escape_string(trim(htmlspecialchars($t, ENT_QUOTES)));
	}
}
function boomSanitize($t){
	global $mysqli;
	$t = str_replace(array('\\', '/', '.', '<', '>', '%', '#'), '', $t);
	return $mysqli->real_escape_string(trim(htmlspecialchars($t, ENT_QUOTES)));
}
function softEscape($t){
	global $mysqli;
	$atags = '<a><p><h1><h2><h3><h4><img><b><strong><br><ul><li><div><i><span><u><th><td><tr><table><strike><small><ol><hr><font><center><blink><audio><marquee><script><style>';
	$t = strip_tags($t, $atags);
	return $mysqli->real_escape_string(trim($t));
}
function systemReplace($text){
	global $lang;
	$text = str_replace('%bcjoin%', $lang['join_message'], $text);
	$text = str_replace('%bcclear%', $lang['clear_message'], $text);
	$text = str_replace('%spam%', $lang['spam_content'], $text);
	$text = str_replace('%bcname%', $lang['name_message'], $text);
	$text = str_replace('%bckick%', $lang['kick_message'], $text);
	$text = str_replace('%bcban%', $lang['ban_message'], $text);
	$text = str_replace('%bcmute%', $lang['mute_message'], $text);
	$text = str_replace('%bcblock%', $lang['block_message'], $text);
	return $text;
}
function textReplace($text){
	global $data;
	$text = str_replace('%user%', $data['user_name'], $text);
	return $text;
}
function systemSpecial($content, $type, $custom = array()){
	global $lang;
	$def = array(
		'content'=> $content,
		'type'=> $type,
		'delete'=> 1,
		'title'=> $lang['default_title'],
		'icon'=> 'default.svg',
	);
	$template = array_merge($def, $custom);
	return boomTemplate('element/system_log', $template);
}
function specialLogIcon($icon){
	return 'default_images/special/' . $icon . boomFileVersion();
}
function userShareAge($user){
	if($user['ashare'] > 0){
		return true;
	}
}
function userShareGender($user){
	if($user['sshare'] > 0){
		return true;
	}
}
function userShareLocation($user){
	if($user['country'] != '' && $user['country'] != 'ZZ' && $user['lshare'] > 0){
		return true;
	}
}
function userShareFriend($user){
	if(!isMember($user)){
		return false;
	}
	if($user['fshare'] > 0){
		return true;
	}
}
function userShareGift($user){
	if(!useGift() || isBot($user)){
		return false;
	}
	if($user['gshare'] > 0){
		return true;
	}
}
function getUserAge($age){
	global $lang;
	return $age . ' ' . $lang['years_old'];
}
function delExpired($d){
	if($d < calSecond(20)){
		return true;
	}
}
function chatAction($room){
	global $mysqli;
	$mysqli->query("UPDATE boom_rooms SET rcaction = rcaction + 1, room_action = '" . time() . "' WHERE room_id = '$room'");
	redisUpdateChat($room);
}
function useQuote(){
	global $setting;
	if($setting['allow_quote'] < 999){
		return true;
	}
}
function usePrivateQuote(){
	global $setting;
	if($setting['allow_pquote'] < 999){
		return true;
	}
}

// chat post functions

function systemPostChat($room, $user, $type, $custom = ''){
	global $mysqli, $setting;
	$mysqli->query("INSERT INTO `boom_chat` (post_date, user_id, post_roomid, type, syslog, tid, tname, custom) VALUES ('" . time() . "', '{$setting['system_id']}', '$room', '$type', '1', '{$user['user_id']}', '{$user['user_name']}', '$custom')");
	$last_id = $mysqli->insert_id;
	chatAction($room);
	return true;
}
function convId($hunter, $target){
	return $hunter.'.'.$target;
}
function updateConv($hunter, $target){
	global $mysqli;
	$mysqli->query("
	INSERT INTO `boom_conversation` (`cid`, `hunter`, `target`, `unread`, `cdate`) VALUES ('" . convId($hunter, $target) . "','$hunter','$target',1,'" . time() . "') ON DUPLICATE KEY UPDATE `unread` = `unread` + 1, `cdate` = '" . time() . "'
	");
	redisUpdatePrivate($target);
}
function readConv($hunter, $target){
	global $mysqli;
	$mysqli->query("UPDATE boom_conversation SET unread = 0, cdate = '". time() . "' WHERE hunter = '$hunter' AND target = '$target'");
	redisUpdatePrivate($target);
}
function readAllConv($id){
	global $mysqli;
	$mysqli->query("UPDATE boom_conversation SET unread = 0, cdate = '". time() . "' WHERE target = '$id'");
	redisUpdatePrivate($id);
}
function descConv($hunter, $target){
	global $mysqli;
	$mysqli->query("UPDATE boom_conversation SET unread = unread - 1 WHERE hunter = '$hunter' AND target = '$target' AND unread > 0");
	redisUpdatePrivate($target);
}
function quoteDetails($id){
	global $mysqli;
	$log = [];
	if(($cache = redisGetObject('quote:' . $id))){
		return $cache;
	}
	$get_log = $mysqli->query("
		SELECT 
		boom_chat.post_id, boom_chat.post_roomid, boom_chat.user_id, boom_chat.post_message, boom_chat.pghost,
		boom_users.user_name, boom_users.user_tumb, boom_users.user_bot
		FROM boom_chat, boom_users
		WHERE boom_chat.post_id = '$id' AND boom_users.user_id = boom_chat.user_id
	");
	if($get_log->num_rows > 0){
		$log = $get_log->fetch_assoc();
		redisSetObject('quote:' . $id, $log, 30);
	}
	return $log;
}
function privateQuoteDetails($id){
	global $mysqli;
	$log = [];
	if(($cache = redisGetObject('pquote:' . $id))){
		return $cache;
	}
	$get_log = $mysqli->query("
		SELECT boom_private.id, boom_private.hunter, boom_private.target, boom_private.message
		FROM boom_private
		WHERE boom_private.id = '$id'
	");
	if($get_log->num_rows > 0){
		$log = $get_log->fetch_assoc();
		redisSetObject('pquote:' . $id, $log);
	}
	return $log;
}
function boomListNotify($list, $type, $custom = array()){
	global $mysqli, $setting, $data;
	if(!empty($list)){
		$values = '';
		foreach($list as $user){
			$def = array(
				'hunter'=> $setting['system_id'],
				'room'=> $data['user_roomid'],
				'rank'=> 0,
				'delay'=> 0,
				'reason'=> '',
				'source'=> 'system',
				'sourceid'=> 0,
				'custom' => '',
				'custom2' => '',
				'icon'=> '',
				'class'=> '',
				'data'=> '',
			);
			$c = array_merge($def, $custom);
			$values .= "('{$c['hunter']}', '$user', '$type', '" . time() . "', '{$c['source']}', '{$c['sourceid']}', '{$c['rank']}', '{$c['delay']}', '{$c['reason']}', '{$c['custom']}', '{$c['custom2']}', '{$c['icon']}', '{$c['class']}', '{$c['data']}'),";
		}
		$values = rtrim($values, ',');
		$mysqli->query("INSERT INTO boom_notification ( notifier, notified, notify_type, notify_date, notify_source, notify_id, notify_rank, notify_delay, notify_reason, notify_custom, notify_custom2, notify_icon, notify_class, notify_data) VALUES $values");
		updateListNotify($list);
	}
}
function boomNotify($type, $custom = array()){
	global $mysqli, $setting, $data;
	$def = array(
		'hunter'=> $setting['system_id'],
		'target'=> 0,
		'room'=> $data['user_roomid'],
		'rank'=> 0,
		'delay'=> 0,
		'reason'=> '',
		'source'=> 'system',
		'sourceid'=> 0,
		'custom' => '',
		'custom2' => '',
		'icon'=> '',
		'class'=> '',
		'data'=> '',
	);
	$c = array_merge($def, $custom);
	if($c['target'] == 0){
		return false;
	}
	$mysqli->query("INSERT INTO boom_notification ( notifier, notified, notify_type, notify_date, notify_source, notify_id, notify_rank, notify_delay, notify_reason, notify_custom, notify_custom2, notify_icon, notify_class, notify_data) 
	VALUE ('{$c['hunter']}', '{$c['target']}', '$type', '" . time() . "', '{$c['source']}', '{$c['sourceid']}', '{$c['rank']}', '{$c['delay']}', '{$c['reason']}', '{$c['custom']}', '{$c['custom2']}', '{$c['icon']}', '{$c['class']}', '{$c['data']}')");
	updateNotify($c['target']); 
}
function updateNotify($id){
	global $mysqli;
	$mysqli->query("UPDATE boom_users SET naction = naction + 1 WHERE user_id = '$id'");
	redisUpdateNotify($id);
}
function updateListNotify($list){
	global $mysqli;
	if(empty($list)){
		return false;
	}
	$delay = getDelay();
	$ulist = implode(", ", $list);
	$mysqli->query("UPDATE boom_users SET naction = naction + 1 WHERE user_id IN ($ulist) AND last_action > '$delay'");
	redisListNotify($list);
}
function updateStaffNotify(){
	global $mysqli;
	$delay = getDelay();
	$mysqli->query("UPDATE boom_users SET naction = naction + 1 WHERE user_rank >= 70 AND last_action > '$delay'");
	redisUpdateStaffNotify();
}
function updateAllNotify(){
	global $mysqli;
	$delay = getDelay();
	$mysqli->query("UPDATE boom_users SET naction = naction + 1 WHERE last_action > '$delay'");
	redisUpdateAllNotify();
}
function loadIgnore($id){
	global $mysqli, $data;
	$list = [];
	if(is_array($cache = redisGetObject('ignore:' . $id))){
		return $cache;
	}
	$get_ignore = $mysqli->query("SELECT ignored FROM boom_ignore WHERE ignorer = '{$data['user_id']}'");
	while($ignore = $get_ignore->fetch_assoc()){
		$list[] = (int) $ignore['ignored'];
	}
	redisSetObject('ignore:' . $id, $list);
	return $list;
}
function processQuoteMessage($message) {
	return mb_convert_encoding(systemReplace($message), 'UTF-8', 'auto');
}
function processChatMessage($post) {
	return mb_convert_encoding(systemReplace($post['post_message']), 'UTF-8', 'auto');
}
function processPrivateMessage($post) {
	return mb_convert_encoding(systemReplace($post['message']), 'UTF-8', 'auto');
}
function spamText(){
	return '<div class="system_text">%spam%</div>';
}
function zalgoText(){
	return '<div class="system_text">****</div>';
}
function privDel(){
	if(canDeletePrivate()){
		return 1;
	}
	else {
		return 0;
	}
}
function chatDate($date){
	return date("j/m G:i", $date);
}
function displayDate($date){
	return date("j/m G:i", $date);
}
function longDate($date){
	return date("Y-m-d ", $date);
}
function longDateTime($date){
	return date("Y-m-d G:i ", $date);
}

function userTime($user){          
	$d = new DateTime(date("d F Y H:i:s",time()));
	$d->setTimezone(new DateTimeZone($user['user_timezone']));
	$r =$d->format('G:i');
	return $r;
}
function useLogs($val){
	global $setting;
	if(preg_match('@[' . $val . ']@i', $setting['use_logs'])){
		return true;
	}
}
function boomAllow($rank){
	global $data;
	if($data['user_rank'] >= $rank){
		return true;
	}
}
function boomRank($rank){
	global $data;
	if($data['user_rank'] == $rank){ 
		return true;
	}
}
function userBoomAllow($user, $val){
	if($user['user_rank'] >= $val){
		return true;
	}
}
function boomRole($role){
	global $data;
	if($data['user_role'] >= $role){
		return true;
	}
}
function haveRole($role){
	if($role > 0){
		return true;
	}
}
function isGreater($rank){
	global $data;
	if($data['user_rank'] > $rank){
		return true;
	}
}
function mySelf($id){
	global $data;
	if($id == $data['user_id']){
		return true;
	}
}
function isBot($user){
	if($user['user_bot'] > 0){
		return true;
	}
}
function systemBot($user){
	if($user == 9){
		return true;
	}
}
function isSystem($id){
	global $setting;
	if($id == $setting['system_id']){
		return true;
	}
}
function myElement($id, $c){
	if(mySelf($id)){
		return $c;
	}
}
function checkMute($data){
	$r = 'c';
	if(isMuted($data)){
		$r .= 'mws';
		if(!canPrivate()){
			$r .= 'p';
		}
		return $r;
	}
	if(!canPrivate()){
		$r .= 'p';
	}
	if(isMainMuted($data) || isRoomMuted($data) || !canMain()){
		$r .= 'm';
	}
	return $r;
}
function muted(){
	global $data;
	if(isMuted($data) || !inChat($data)){
		return true;
	}
}
function mainMuted(){
	global $data;
	if(isMuted($data) || isMainMuted($data) || !inChat($data) || isRoomMuted($data) || !canMain() || isWarned($data)){
		return true;
	}
}
function privateMuted(){
	global $data;
	if(isMuted($data) || !inChat($data) || !canPrivate() || isWarned($data)){
		return true;
	}
}
function isRoomMuted($user){
	if($user['room_mute'] > time()){
		return true;
	}
}
function isMuted($user){
	if($user['user_mute'] > time() || isRegmuted($user)){
		return true;
	}
}
function isMainMuted($user){
	if($user['user_mmute'] > time()){
		return true;
	}
}
function isPrivateMuted($user){
	if($user['user_pmute'] > time()){
		return true;
	}
}
function isBanned($user){
	if($user['user_banned'] > 0){
		return true;
	}
}
function isKicked($user){
	if($user['user_kick'] > time()){
		return true;
	}
}
function isGhosted($user){
	if($user['user_ghost'] > time()){
		return true;
	}
	else {
		return false;
	}
}
function isWarned($user){
	if(!empty($user['warn_msg'])){
		return true;
	}
	else {
		return false;
	}
}
function isRegmuted($user){
	if($user['user_rmute'] > time()){
		return true;
	}
}
function isOnAir($user){
	if($user['user_onair'] > 0){
		return true;
	}
}
function systemNameFilter($user){
	return '<span onclick="getProfile(' . $user['user_id'] . ')"; class="sysname bclick">' . $user['user_name'] . '</span>';
}
function moveMessage($user){
	if($user['user_move'] < calSecond(6)){
		return true;
	}
}
function setUserRoom(){
	global $data, $mysqli;
	$room = myRoomDetails($data['user_roomid']);
	if(joinMessage($data)){
		joinRoomMessage($data['user_roomid']);
	}
	$mysqli->query("UPDATE boom_users SET user_roomid = '{$data['user_roomid']}', user_move = '" . time() . "', last_action = '" . time() . "', room_mute = '{$room['room_muted']}', user_role = '{$room['room_ranking']}' WHERE user_id = '{$data['user_id']}'");
	redisInitUser($data);
}
function joinMessage($user){
	global $data;
	if($user['user_move'] == 0 || $user['last_action'] < calMinutes(60)){
		return true;
	}
}
function joinRoomMessage($room){
	global $lang, $data;
	if(useLogs(1) && moveMessage($data) && isVisible($data)){
		$content = str_replace('%user%', systemNameFilter($data), $lang['system_join_room']);
		systemPostChat($room, $data, 'system__join');
	}
}
function changeNameLog($user, $n){
	global $lang;
	nameRecord($user, $n);
	if(useLogs(2) && isVisible($user)){
		$old = $user['user_name'];
		$user['user_name'] = $n;
		systemPostChat($user['user_roomid'], $user, 'system__name', $old);
	}
}
function kickLog($user){
	global $lang;
	if(useLogs(3) && userInRoom($user)){
		systemPostChat($user['user_roomid'], $user, 'system__kick');
	}
}
function banLog($user){
	global $lang;
	if(useLogs(3) && userInRoom($user)){
		systemPostChat($user['user_roomid'], $user, 'system__ban');
	}
}
function muteLog($user){
	global $lang;
	if(useLogs(3) && userInRoom($user)){
		systemPostChat($user['user_roomid'], $user, 'system__mute');
	}
}
function blockLog($user){
	global $lang;
	if(useLogs(3) && userInRoom($user)){
		systemPostChat($user['user_roomid'], $user, 'system__block');
	}
}
function processUserData($t){
	global $data;
	return str_replace(array('%user%'), array($data['user_name']), $t);
}
function autoRoomStaff(){
	return true;
}
function roomStaff(){
	if(boomRole(4)){
		return true;
	}
}
function userRoomStaff($rank){
	if($rank >= 4){
		return true;
	}
}
function isVisible($user){
	if($user['user_status'] != 99){
		return true;
	}
}
function isSecure($user){
	if(isEmail($user['user_email'])){
		return true;
	}
}
function isMember($user){
	if(!isGuest($user) && !isBot($user)){
		return true;
	}
}
function isGuest($user){
	if($user['user_rank'] == 0){
		return true;
	}
}
function isPaidVip($user){
	if($user['user_rank'] == 50 && $user['vip_end'] > time()){
		return true;
	}
}
function guestForm(){
	global $setting;
	if($setting['guest_form'] == 1){
		return true;
	}
}
function userDj($user){
	if($user['user_dj'] == 1){
		return true;
	}
}
function boomRecaptcha(){
	global $setting;
	if($setting['use_recapt'] > 0){
		return true;
	}
}
function minCall(){
	global $setting;
	return min($setting['can_vcall'], $setting['can_acall']);
}

function encrypt($d){
	return sha1(str_rot13($d . BOOM_CRYPT));
}
function boomEncrypt($d, $encr){
	return sha1(str_rot13($d . $encr));
}
function getDelay(){
	return time() - 75;
}
function delDelay(){
	return calDayUp(7);
}
function getMinutes($t){
	return $t / 60;
}
function isOwner($user){
	if($user['user_rank'] == 100){
		return true;
	}
}
function isStaff($user){
	if($user['user_rank'] >= 70){
		return true;
	}
	else {
		return false;
	}
}
function isStaffRank($rank){
	if($rank >= 70){
		return true;
	}
}
function genKey(){
	return md5(rand(10000,99999) . rand(10000,99999));
}
function genCode(){
	return rand(111111,999999);
}
function randomKey($v){
	$text = 'abcdefghijklmnopqrstuvwxyz01234567890';
	$text = substr(str_shuffle($text), 0, $v);
	return $text;
}
function boomUnderClear($t){
	return str_replace('_', ' ', $t);
}
function allowGuest(){
	global $setting;
	if($setting['allow_guest'] == 1){
		return true;
	}
}
function clearNotifyAction($id, $type){
	global $mysqli;
	$mysqli->query("DELETE FROM boom_notification WHERE notified = '$id' AND notify_source = '$type'");
}
function clearNotifyType($id, $type){
	global $mysqli;
	$mysqli->query("DELETE FROM boom_notification WHERE notified = '$id' AND notify_type = '$type'");
}
function boomDuplicateIp($val){
	global $mysqli;
	$dupli = $mysqli->query("SELECT * FROM `boom_banned` WHERE `ip` = '$val'");
	if($dupli->num_rows > 0){
		return true;
	}
}

// sex and gender and status functions

function genderList(){
	return array(1,2,3);
}

function genderOption($current, $val){
	return '<option value="' . $val . '" ' . selCurrent($current, $val) . '>' . genderTitle($val) . '</option>';
}

function listGender($current){
	$gender = '';
	foreach(genderList() as $val){
		$gender .= genderOption($current, $val);
	}
	return $gender;
}
function validGender($sex){
	$gender = genderList();
	if(in_array($sex, $gender)){
		return true;
	}
}
function genderTitle($s){
	global $lang;
	switch($s){
		case 1:
			return $lang['male'];
		case 2:
			return $lang['female'];
		case 3:
			return $lang['other'];
		default:
			return $lang['other'];
	}
}

function relationTitle($s){
	global $lang;
	$notsay = isset($lang['notsay']) ? $lang['notsay'] : 'Not specified';
	switch((int)$s){
		case 1:
			return isset($lang['single']) ? $lang['single'] : 'Single';
		case 2:
			return isset($lang['in_relation']) ? $lang['in_relation'] : 'In relationship';
		case 3:
			return isset($lang['married']) ? $lang['married'] : 'Married';
		case 4:
			return isset($lang['engaged']) ? $lang['engaged'] : 'Engaged';
		default:
			return $notsay;
	}
}

function genderBorder($s){
	global $setting;
	if($setting['use_gender'] > 0){
		switch($s){
			case 1:
				return 'avagen genmale';
			case 2:
				return 'avagen genfemale';
			case 3:
				return 'avagen genother';
			default:
				return 'avagen genother';
		}
	}
}

// card share data

function uGender($user){
	if(userShareGender($user)){
		return $user['user_sex'];
	}
	return 0;
}
function uAge($user){
	if(userShareAge($user)){
		return $user['user_age'];
	}
	return 0;
}

function uCountry($user){
	if(userShareLocation($user)){
		return $user['country'];
	}
	return 'ZZ';
}

/* main ranking functions */

function systemRank($rank, $type){
	return '<img src="default_images/rank/' . rankIcon($rank) . '" data-r="' . $rank . '" class="' . $type . '"/>';
}
function roomRank($rank, $type){
	if($rank > 0){
		return '<img src="default_images/rank/' . roomRankIcon($rank) . '" data-r="' . $rank . '" class="' . $type . '"/>';
	}
}
function chatRank($user, $type = 'chat_rank'){
	if(isBot($user)){
		return;
	}
	return systemRank($user['user_rank'], $type);
}
function jsonRankTitle(){
	$s = [];
	foreach(rankList() as $r){
		$s[$r] =  rankTitle($r);
	}
	return json_encode($s);
}
function jsonRankIcon(){
	$s = [];
	foreach(rankList() as $r){
		$s[$r] =  rankIcon($r);
	}
	return json_encode($s);
}
function jsonRoomRankIcon(){
	$s = [];
	foreach(roomRankList() as $r){
		$s[$r] =  roomRankIcon($r);
	}
	return json_encode($s);
}
function jsonRoomRankTitle(){
	$s = [];
	foreach(roomRankList() as $r){
		$s[$r] =  roomRankTitle($r);
	}
	return json_encode($s);
}
function jsonStatusIcon(){
	$s = [];
	foreach(statusList() as $r){
		$s[$r] =  statusIcon($r);
	}
	return json_encode($s);
}
function jsonStatusTitle(){
	$s = [];
	foreach(statusList() as $r){
		$s[$r] =  statusTitle($r);
	}
	return json_encode($s);
}
function jsonGenderTitle(){
	$s = [];
	foreach(genderList() as $r){
		$s[$r] =  genderTitle($r);
	}
	return json_encode($s, JSON_FORCE_OBJECT);
}

// blocked feature

function featureBlock($v){
	if($v == 1){
		return true;
	}
}

// permission functions

function canGhost(){
	global $setting;
	if(boomAllow($setting['can_ghost'])){
		return true;
	}
}
function canEditRoom(){
	global $setting;
	if(boomRole(6) || boomAllow($setting['can_raction'])){
		return true;
	}
}
function canPrivate(){
	global $setting, $data;
	if(boomAllow($setting['allow_private']) && !isPrivateMuted($data)){
		return true;
	}
}
function userCanPrivate($user){
	global $setting, $data;
	if(userBoomAllow($user, $setting['allow_private']) && !isPrivateMuted($user)){
		return true;
	}
}
function canMain(){
	global $setting;
	if(boomAllow($setting['allow_main'])){
		return true;
	}
}
function isActProof(){
	if(boomAllow(70)){
		return true;
	}
}
function isWordProof(){
	global $setting;
	if(boomAllow($setting['word_proof'])){
		return true;
	}
}
function canQuote(){
	global $setting;
	if(boomAllow($setting['allow_quote'])){
		return true;
	}
}
function canPrivateQuote(){
	global $setting;
	if(boomAllow($setting['allow_pquote'])){
		return true;
	}
}
function canViewInvisible(){
	if(boomAllow(100)){
		return true;
	}
}
function canViewWallet($user){
	global $setting;
	if(mySelf($user['user_id']) || isBot($user) || isGuest($user)){
		return false;
	}
	if(useWallet() && boomAllow($setting['can_vwallet'])){
		return true;
	}
}
function canDeleteSelfLog($p){
	global $setting, $data;
	if($p['user_id'] == $data['user_id'] && boomAllow($setting['allow_scontent'])){
		return true;
	}
}
function canDeleteContent(){
	global $setting;
	if(boomAllow($setting['can_content'])){
		return true;
	}
}
function canReport(){
	global $setting;
	if(boomAllow($setting['allow_report'])){
		return true;
	}
}
function canManageReport(){
	if(boomAllow(70)){
		return true;
	}
}
function canDeletePrivate(){
	global $setting;
	if(boomAllow($setting['allow_scontent'])){
		return true;
	}
}
function canDeleteRoomLog(){
	global $setting;
	if(boomRole($setting['can_rlogs'])){
		return true;
	}
}
function canClearRoom(){
	global $setting;
	if(boomAllow($setting['can_clear']) || boomRole($setting['can_rclear'])){
		return true;
	}
}
function canViewGhost(){
	global $setting;
	if(boomAllow($setting['can_vghost'])){
		return true;
	}
	else {
		return false;
	}
}

/* icons function */

function rubyIcon(){
	return 'default_images/icons/ruby.svg';
}
function goldIcon(){
	return 'default_images/icons/gold.svg';
}
function giftIcon(){
	return 'default_images/icons/gift.svg';
}
function levelIcon(){
	return 'default_images/icons/level.svg';
}
function likeIcon(){
	return 'default_images/icons/like.svg';
}
function xpIcon(){
	return 'default_images/icons/xp.svg';
}

/* feature */

function featureCost($a, $t){
	return $a . ' ' . walletTitle($t);
}

/* bank functions */

function useWallet(){
	global $setting;
	if($setting['use_wallet'] > 0){
		return true;
	}
}
function walletBalance($type, $amount){
	global $data;
	if($type == 1 && goldBalance($amount)){
		return true;
	}
	if($type == 2 && rubyBalance($amount)){
		return true;
	}
}
function removeWallet($user, $type, $amount){
	if($type == 1){
		removeGold($user, $amount);
	}
	else if($type == 2){
		removeRuby($user, $amount);
	}
}
function walletTitle($type){
	global $lang;
	switch($type){
		case 1:
			return $lang['gold'];
		case 2:
			return $lang['ruby'];
		default:
			return $lang['gold'];
	}
}
function canShareWallet($user){
	global $setting;
	if(!useWallet() || isBot($user) || ignored($user) || ignoring($user)){
		return false;
	}
	if(boomAllow($setting['can_swallet'])){
		return true;
	}
}
function walletIcon($type){
	switch($type){
		case 1:
			return goldIcon();
		case 2:
			return rubyIcon();
		default:
			return goldIcon();
	}
}
function canGoldReward(){
	global $setting, $data;
	if($setting['gold_base'] > 0 && boomAllow($setting['can_gold']) && $data['last_gold'] <= calMinutes($setting['gold_delay'])){
		return true;
	}
}
function canRubyReward(){
	global $setting, $data;
	if($setting['ruby_base'] > 0 && boomAllow($setting['can_ruby']) && $data['last_ruby'] <= calMinutes($setting['ruby_delay'])){
		return true;
	}
}
function canReceiveGold($user){
	global $setting;
	if(!isBot($user)){
		return true;
	}
}
function goldBalance($gold){
	global $data;
	if($data['user_gold'] >= $gold){
		return true;
	}
}
function rubyBalance($ruby){
	global $data;
	if($data['user_ruby'] >= $ruby){
		return true;
	}
}
function addRuby($user, $ruby){
	global $mysqli;
	$mysqli->query("UPDATE boom_users SET user_ruby = user_ruby + '$ruby' WHERE user_id = '{$user['user_id']}'");
	redisUpdateUser($user['user_id']);
}
function removeRuby($user, $ruby){
	global $mysqli;
	$mysqli->query("UPDATE boom_users SET user_ruby = user_ruby - '$ruby', user_sruby = user_sruby + '$ruby' WHERE user_id = '{$user['user_id']}'");
	redisUpdateUser($user['user_id']);
}
function addGold($user, $gold){
	global $mysqli;
	$mysqli->query("UPDATE boom_users SET user_gold = user_gold + '$gold' WHERE user_id = '{$user['user_id']}'");
	redisUpdateUser($user['user_id']);
}
function removeGold($user, $gold){
	global $mysqli;
	$mysqli->query("UPDATE boom_users SET user_gold = user_gold - '$gold', user_sgold = user_sgold + '$gold' WHERE user_id = '{$user['user_id']}'");
	redisUpdateUser($user['user_id']);
}
function minGold(){
	return 250;
}
function maxGold(){
	return 25000;
}
function minRuby(){
	return 20;
}
function maxRuby(){
	return 5000;
}
function validGold($n){
	if($n >= minGold() && $n <= maxGold()){
		return true;
	}
}
function validRuby($n){
	if($n >= minRuby() && $n <= maxRuby()){
		return true;
	}
}
function costTag($type, $amount, $class = ''){
	$tg = [
		'icon' 	=> walletIcon($type),
		'amount' => $amount,
		'class' => $class,
	];
	return boomTemplate('element/cost_tag', $tg);
}
function costTags($type, $amount, $args = array()){
	$tg = [
		'icon' 	=> walletIcon($type),
		'amount' => $amount,
		'class' => '',
		'text' => '',
	];
	$r = array_merge($tg, $args);
	return boomTemplate('element/cost_tags', $r);
}

/* gift functions */

function giftImage($i){
	return 'gift/' . $i;
}
function useGift(){
	global $setting;
	if(!useWallet()){
		return false;
	}
	if($setting['use_gift'] > 0){
		return true;
	}
}
function canSendGift($user){
	if(!useGift()){
		return false;
	}
	if(isBot($user)){
		return false;
	}
	if(ignored($user) || ignoring($user)){
		return false;
	}
	return true;
}
function giftDetails($id){
	global $mysqli;
	$gift = [];
	$get_gift = $mysqli->query("SELECT * FROM boom_gift WHERE id = '$id'");
	if($get_gift->num_rows > 0){
		$gift = $get_gift->fetch_assoc();
	}
	return $gift;
}
function giftRecord($user, $gift){
	global $mysqli;
	$check_gift = $mysqli->query("SELECT id FROM boom_users_gift WHERE target = '{$user['user_id']}' AND gift = '{$gift['id']}'");
	if($check_gift->num_rows > 0){
		$mysqli->query("UPDATE boom_users_gift SET gift_count = gift_count + 1, gift_date = '" . time() . "' WHERE target = '{$user['user_id']}' AND gift = '{$gift['id']}'");
	}
	else {
		$mysqli->query("INSERT INTO `boom_users_gift` (target, gift, gift_date) VALUES ('{$user['user_id']}','{$gift['id']}',".time().")");
	}
}

/* flood functions */

function checkFlood(){
	global $setting, $data;
	if(!isActProof()){
		if(!isset($_SESSION[BOOM_PREFIX . 'post_time'], $_SESSION[BOOM_PREFIX . 'post_count']) || $_SESSION[BOOM_PREFIX . 'post_time'] < (time() - 10)){
			$_SESSION[BOOM_PREFIX . 'post_time'] = time();
			$_SESSION[BOOM_PREFIX . 'post_count'] = 1;
			return false;
		}
		if($_SESSION[BOOM_PREFIX . 'post_count'] >= $setting['max_flood']){
			if($setting['flood_action'] == 1){
				systemFloodKick($data);
				return true;
			}
			else if($setting['flood_action'] == 2){
				systemFloodMute($data);
				return true;
			}
		}
		$_SESSION[BOOM_PREFIX . 'post_count']++;
	}
}

/* save db to cached file */

function boomSaveSettings(){
	global $mysqli;
	$mysqli->query("UPDATE boom_setting SET curset = curset + 1 WHERE id = 1");
	if(is_writable(BOOM_PATH . '/system/settings.php')){
		$q = $mysqli->query("SELECT * FROM boom_setting WHERE id = 1");
		$f = '';
		while($d = $q->fetch_assoc()){
			foreach($d as $key => $value){
				$f .= '$setting[\'' . $key . '\'] = \'' . addslashes($value) . '\';' . "\n";
			}
		}
		$g = "<?php\n$f?>";
		$f = fopen(BOOM_PATH . '/system/settings.php', "w+") or die();
		fwrite($f,$g);
		fclose($f);
	}
}
function reloadSettings($r = false){
	global $setting;
	if($r === true){
		$setting = settingDetails();
	}
	return [
		'avatarmax' => (int) $setting['max_avatar'],
		'covermax' => (int) $setting['max_cover'],
		'riconmax' => (int) $setting['max_ricon'],
		'filemax' => (int) $setting['file_weight'],
		'speed' => (int) $setting['speed'],
		'cancall'=> (int) minCall(),
		'usecall'=> (int) $setting['use_call'],
		'inout' => (int) $setting['act_delay'],
		'uquote' => (int) $setting['allow_quote'],
		'upquote' => (int) $setting['allow_pquote'],
		'primin' => (int) $setting['allow_private'],
		'canscontent' => (int) $setting['allow_scontent'],
		'cancontent' => (int) $setting['can_content'],
		'canrlogs' => (int) $setting['can_rlogs'],
		'canreport' => (int) $setting['allow_report'],
		'maxemo' => (int) $setting['max_emo'],
		'curset' => (int) $setting['curset'],
		'uselevel' => (int) $setting['use_level'],
		'usebadge' => (int) $setting['use_badge'],
	];
}

// element details functions

function userDetails($id){
	global $mysqli;
	$user = [];
	if(($cache = redisGetObject('user:' . $id))){
		return $cache;
	}
	$getuser = $mysqli->query("SELECT * FROM boom_users WHERE user_id = '$id'");
	if($getuser->num_rows > 0){
		$user = $getuser->fetch_assoc();
		redisSetObject('user:' . $id, $user);
	}
	return $user;
}
function userProfileDetails($id){
	global $mysqli;
	$user = userDetails($id);
	if(!empty($user)){
		$getuser = $mysqli->query("
			SELECT boom_users_data.*, boom_rooms.room_id, boom_rooms.room_name
			FROM boom_users_data
			LEFT JOIN boom_rooms ON boom_rooms.room_id = '{$user['user_roomid']}'
			WHERE uid = '{$user['user_id']}';
		");
		if($getuser->num_rows > 0){
			return array_merge($user, $getuser->fetch_assoc());
		}
	}
	return [];
}
function joinProfileDetails($user){
	global $mysqli;
	$getuser = $mysqli->query("SELECT boom_users_data.* FROM boom_users_data WHERE uid = '{$user['user_id']}'");
	if($getuser->num_rows > 0){
		return array_merge($user, $getuser->fetch_assoc());
	}
	return $user;
}
function userChatDetails($id){
	global $mysqli;
	$user = [];
	if(($cache = redisGetObject('cuser:' . $id))){
		return $cache;
	}
	$getuser = $mysqli->query("
	SELECT
	user_id, user_name, user_password, user_join, last_action, user_language, user_timezone, user_status, user_rank, user_level, user_roomid, session_id, pcount,
	pdel, pdeltime, user_news, user_ghost, user_mute, user_rmute, user_mmute, user_pmute, user_banned, user_kick, warn_msg, user_role, user_action, room_mute, naction, user_ruby, last_ruby, user_gold, last_gold, ucall, user_wall,
	(SELECT count(*) FROM boom_conversation WHERE target = '$id' AND unread > 0) as private_count
	FROM boom_users WHERE user_id = '$id'
	");
	if($getuser->num_rows > 0){
		$user = $getuser->fetch_assoc();
		redisSetObject('cuser:' . $id, $user);
	}
	return $user;
}
function userNameDetails($name){
	global $mysqli;
	$user = [];
	$getuser = $mysqli->query("SELECT * FROM boom_users WHERE user_name = '$name'");
	if($getuser->num_rows > 0){
		$user = $getuser->fetch_assoc();
	}
	return $user;
}
function userRoomDetails($id, $room = ''){
	global $mysqli, $data;
	if(empty($room)){
		$room = $data['user_roomid'];
	}
	$user = userDetails($id);
	if(!empty($user)){
		$getuser = $mysqli->query("
			SELECT
			IFNULL((SELECT action_muted FROM boom_room_action WHERE action_user = '$id' AND action_room = '$room'), 0) as room_muted,
			IFNULL((SELECT action_blocked FROM boom_room_action WHERE action_user = '$id' AND action_room = '$room'), 0) as room_blocked,
			IFNULL((SELECT room_rank FROM boom_room_staff WHERE room_staff = '$id' AND room_id = '$room'), 0) as room_ranking
		");
		if($getuser->num_rows > 0){
			return array_merge($user, $getuser->fetch_assoc());
		}
	}
	return [];
}
function userRelationDetails($id){
	global $mysqli, $data;
	$user = userDetails($id);
	if(!empty($user)){
		$getuser = $mysqli->query("
			SELECT
			IFNULL((SELECT fstatus FROM boom_friends WHERE hunter = '{$data['user_id']}' AND target = '$id'), 0) as friendship,
			(SELECT count(ignore_id) FROM boom_ignore WHERE ignorer = '$id' AND ignored = '{$data['user_id']}' ) as ignored,
			(SELECT count(ignore_id) FROM boom_ignore WHERE ignorer = '{$data['user_id']}' AND ignored = '$id') as ignoring
		");
		if($getuser->num_rows > 0){
			return array_merge($user, $getuser->fetch_assoc());
		}
	}
	return [];
}
function userFullDetails($id, $room = ''){
	global $mysqli, $data;
	if($room == ''){
		$room = $data['user_roomid'];
	}
	$user = userDetails($id);
	if(!empty($user)){
		$getuser = $mysqli->query("
			SELECT
			IFNULL((SELECT fstatus FROM boom_friends WHERE hunter = '{$data['user_id']}' AND target = '$id'), 0) as friendship,
			(SELECT count(ignore_id) FROM boom_ignore WHERE ignorer = '$id' AND ignored = '{$data['user_id']}' ) as ignored,
			(SELECT count(ignore_id) FROM boom_ignore WHERE ignorer = '{$data['user_id']}' AND ignored = '$id') as ignoring,
			IFNULL((SELECT action_muted FROM boom_room_action WHERE action_user = '$id' AND action_room = '$room'), 0) as room_muted,
			IFNULL((SELECT action_blocked FROM boom_room_action WHERE action_user = '$id' AND action_room = '$room'), 0) as room_blocked,
			IFNULL((SELECT room_rank FROM boom_room_staff WHERE room_staff = '$id' AND room_id = '$room'), 0) as room_ranking
		");
		if($getuser->num_rows > 0){
			return array_merge($user, $getuser->fetch_assoc());
		}
	}
	return [];
}
function userDataDetails($user, $key){
	global $mysqli;
	$d = '';
	if(redisCacheExist('data:' . $key . ':' . $user['user_id'])){
		return redisGetElement('data:' . $key . ':' . $user['user_id']);
	}
	else {
		$get_data = $mysqli->query("SELECT * FROM boom_data WHERE data_user = '{$user['user_id']}' AND data_key = '$key'");
		if($get_data->num_rows > 0){
			$dv = $get_data->fetch_assoc();
			$d = $dv['data_value'];
		}
		redisSetElement('data:' . $key . ':' . $user['user_id'], $d);
		return $d;
	}
}
function getUserData($user, $type){
	global $mysqli;
	$d = '';
	$get_data = $mysqli->query("SELECT $type FROM boom_users_data WHERE uid = '{$user['user_id']}'");
	if($get_data->num_rows > 0){
		$res = $get_data->fetch_assoc();
		$d = $res[$type];
	}
	return $d;
}
function myRoomDetails($r){
	global $mysqli, $data;
	$room = roomDetails($r);
	if(!empty($room)){
		$getroom = $mysqli->query("
			SELECT
			IFNULL((SELECT action_muted FROM boom_room_action WHERE action_user = '{$data['user_id']}' AND action_room = '$r'), 0) as room_muted,
			IFNULL((SELECT action_blocked FROM boom_room_action WHERE action_user = '{$data['user_id']}' AND action_room = '$r'), 0) as room_blocked,
			IFNULL((SELECT room_rank FROM boom_room_staff WHERE room_staff = '{$data['user_id']}' AND room_id = '$r'), 0) as room_ranking
		");
		if($getroom->num_rows > 0){
			return array_merge($room, $getroom->fetch_assoc());
		}
	}
	return [];
}
function roomDetails($id){
	global $mysqli;
	$room = [];
	if(($cache = redisGetObject('room:' . $id))){
		return $cache;
	}
	$get_room = $mysqli->query("SELECT * FROM boom_rooms WHERE room_id = '$id'");
	if($get_room->num_rows > 0){
		$room = $get_room->fetch_assoc();
		redisSetObject('room:' . $id, $room);
	}
	return $room;
}
function playerDetails($id){
	global $mysqli, $setting;
	$player['stream_url'] = '';
	$player['stream_title'] = '';
	if(usePlayer()){
		if($id == 0){
			$id = $setting['player_id'];
		}
		if(($cache = redisGetObject('player:' . $id))){
			return $cache;
		}
		$get_player = $mysqli->query("SELECT * FROM boom_radio_stream WHERE id = '$id'");
		if($get_player->num_rows > 0){
			$player = $get_player->fetch_assoc();
			redisSetObject('player:' . $id, $player);
		}
	}
	return $player;
}
function settingDetails(){
	global $mysqli;
	$get_setting = $mysqli->query("SELECT * FROM boom_setting WHERE id = 1");
	$set = $get_setting->fetch_assoc();
	return $set;
}
function addonsDetails($name){
	global $mysqli;
	$addons = [];
	if(($cache = redisGetObject('addons:' . $name))){
		return $cache;
	}
	$geta = $mysqli->query("SELECT * FROM boom_addons WHERE addons = '$name'");
	if($geta->num_rows > 0){
		$addons = $geta->fetch_assoc();
		redisSetObject('addons:' . $name, $addons);
	}
	return $addons;
}
function notifyDetails($id){
	global $mysqli;
	$notify = [];
	$get_notify = $mysqli->query("SELECT * FROM boom_notification WHERE id = '$id'");
	if($get_notify->num_rows > 0){
		$notify = $get_notify->fetch_assoc();
	}
	return $notify;
}
function logDetails($id){
	global $mysqli;
	$log = [];
	$get_log = $mysqli->query("SELECT * FROM boom_chat WHERE post_id = '$id'");
	if($get_log->num_rows > 0){
		$log = $get_log->fetch_assoc();
	}
	return $log;
}
function privateLogDetails($id){
	global $mysqli;
	$log = [];
	$get_log = $mysqli->query("SELECT * FROM boom_private WHERE id = '$id'");
	if($get_log->num_rows > 0){
		$log = $get_log->fetch_assoc();
	}
	return $log;
}
function reportDetails($id){
	global $mysqli;
	$rep = [];
	$get_report = $mysqli->query("SELECT * FROM boom_report WHERE report_id = '$id'");
	if($get_report->num_rows > 0){
		$rep = $get_report->fetch_assoc();
	}
	return $rep;
}

// base config files functions

function getUserSession($ident, $pass){
	global $mysqli;
	$user = userDetails($ident);
	if(empty($user) || $user['user_password'] != $pass){
		return [];
	}
	return $user;
}

function getUserChatSession($ident, $pass){
	global $mysqli;
	$user = userChatDetails($ident);
	if(empty($user) || $user['user_password'] != $pass){
		return [];
	}
	return $user;
}

// user notification

function getNotification(){
	global $mysqli, $data;
	$rep = '';
	if(canManageReport()){
		$rep = '(SELECT count(*) FROM boom_report) as report_count,';
	}
	$get_notify = $mysqli->query("SELECT
	(SELECT count(*) FROM boom_friends WHERE target = '{$data['user_id']}' AND fstatus = '2' AND viewed = '0') as friend_count,
	(SELECT count(*) FROM boom_notification WHERE notified = '{$data['user_id']}' AND notify_view = '0') as notify_count, $rep
	(SELECT count(*) FROM boom_news WHERE news_date > '{$data['user_news']}') as news_count
	");
	$fetch = $get_notify->fetch_assoc();
	if(!canManageReport()){
		$fetch['report_count'] = 0;
	}
	return [
		'friends'=> (int) $fetch['friend_count'],
		'notify'=> (int) $fetch['notify_count'],
		'news'=> (int) $fetch['news_count'],
		'report'=> (int) $fetch['report_count'],
		'nnotif'=> (int) $data['naction'],
	];
}

// room data

function createRoomData($room){
	global $data;
	if(empty($room)){
		return [];
	}
	return [
		'room_id'=> (int) $room['room_id'], 
		'room_name'=> $room['room_name'],
		'room_icon'=> myRoomIcon($room['room_icon']),
		'room_topic'=> getTopic($room),
		'room_action'=> (int) $room['rcaction'],
		'room_role'=> (int) $data['user_role'],
		'room_logs'=> getChatHistory($room['room_id']),
		'room_rm'=> checkMute($data),					   
	];

}

// save user data functions

function saveUserData($user, $key, $value){
	global $mysqli;
	$get_data = $mysqli->query("SELECT id FROM boom_data WHERE data_user = '{$user['user_id']}' AND data_key = '$key'");
	if($get_data->num_rows > 0){
		$mysqli->query("UPDATE boom_data SET data_value = '$value' WHERE data_user = '{$user['user_id']}' AND data_key = '$key'");
	}
	else {
		$mysqli->query("INSERT INTO boom_data (data_user, data_key, data_value) VALUES ('{$user['user_id']}', '$key', '$value')");
	}
	redisDel('data:' . $key . ':' . $user['user_id']);
}
function userAnimSetting($user, $key, $default = 1){
	if(empty($user) || !isset($user['user_id'])){
		return (int) $default;
	}
	$val = userDataDetails($user, $key);
	if($val === '' || $val === null){
		return (int) $default;
	}
	return ((int) $val > 0) ? 1 : 0;
}
function userAnimationConfig($user){
	return [
		'master' => userAnimSetting($user, 'anim_master', 1),
		'chatfx' => userAnimSetting($user, 'anim_chatfx', 1),
		'goofy' => userAnimSetting($user, 'anim_goofy', 1),
		'overlay' => userAnimSetting($user, 'anim_overlay', 1),
	];
}
function animationAllowed($type = 'chatfx'){
	global $data;
	if(empty($data) || !isset($data['user_id'])){
		return true;
	}
	$config = userAnimationConfig($data);
	if($config['master'] < 1){
		return false;
	}
	switch($type){
		case 'chatfx':
			return ($config['chatfx'] > 0);
		case 'goofy':
			return ($config['goofy'] > 0);
		case 'overlay':
			return ($config['overlay'] > 0);
		default:
			return true;
	}
}

// topic

function getTopic($room){
	global $lang;
	$topic = processUserData($room['topic']);
	if(!empty($topic)){
		return [
			'room'=> (int) $room['room_id'],
			'icon'=> specialLogIcon('topic.svg'), 
			'title'=> $lang['topic_title'],
			'content'=> $topic
		];
	}
	else {
		return [];
	}
}

// message reaction

function defaultReactionList(){
	return [
		['value' => 1, 'key' => 'default_images/reaction/like.svg', 'src' => 'default_images/reaction/like.svg'],
		['value' => 2, 'key' => 'default_images/reaction/dislike.svg', 'src' => 'default_images/reaction/dislike.svg'],
		['value' => 3, 'key' => 'default_images/reaction/love.svg', 'src' => 'default_images/reaction/love.svg'],
		['value' => 4, 'key' => 'default_images/reaction/funny.svg', 'src' => 'default_images/reaction/funny.svg'],
	];
}
function legacyReactionKey($value){
	$value = (int) $value;
	foreach(defaultReactionList() as $react){
		if((int) $react['value'] === $value){
			return $react['key'];
		}
	}
	return '';
}
function reactionValueFromKey($key){
	$key = trim((string) $key);
	if($key === ''){
		return 0;
	}
	foreach(defaultReactionList() as $react){
		if($react['key'] === $key){
			return (int) $react['value'];
		}
	}
	return 0;
}
function reactionKeyFromRow($row){
	$key = '';
	if(isset($row['react_key'])){
		$key = trim((string) $row['react_key']);
	}
	if($key === '' && isset($row['react_value'])){
		$key = legacyReactionKey($row['react_value']);
	}
	return $key;
}
function validReactionEmojiFile($file){
	$allowed = ['png', 'svg', 'gif', 'webp', 'jpg', 'jpeg'];
	$ext = strtolower(pathinfo((string) $file, PATHINFO_EXTENSION));
	if(in_array($ext, $allowed, true)){
		return true;
	}
	return false;
}
function reactionEmojiPool(){
	static $pool = null;
	if($pool !== null){
		return $pool;
	}
	$items = [];
	foreach(defaultReactionList() as $react){
		$items[$react['key']] = [
			'key' => $react['key'],
			'src' => $react['src'],
		];
	}
	$base = BOOM_PATH . '/emoticon';
	if(!is_dir($base)){
		$pool = array_values($items);
		return $pool;
	}
	$entries = scandir($base);
	foreach($entries as $entry){
		if($entry === '.' || $entry === '..'){
			continue;
		}
		$entry_path = $base . '/' . $entry;
		if(is_file($entry_path) && validReactionEmojiFile($entry)){
			$key = 'emoticon/' . $entry;
			$items[$key] = [
				'key' => $key,
				'src' => $key,
			];
			continue;
		}
		if(!is_dir($entry_path)){
			continue;
		}
		$sub_entries = scandir($entry_path);
		foreach($sub_entries as $file){
			if($file === '.' || $file === '..'){
				continue;
			}
			if(!validReactionEmojiFile($file)){
				continue;
			}
			$key = 'emoticon/' . $entry . '/' . $file;
			$items[$key] = [
				'key' => $key,
				'src' => $key,
			];
		}
	}
	$pool = array_values($items);
	return $pool;
}
function reactionEmojiMap(){
	static $map = null;
	if($map !== null){
		return $map;
	}
	$map = [];
	foreach(reactionEmojiPool() as $item){
		$map[$item['key']] = $item['src'];
	}
	return $map;
}
function validReactionKey($key){
	$key = trim((string) $key);
	if($key === ''){
		return false;
	}
	$map = reactionEmojiMap();
	if(isset($map[$key])){
		return true;
	}
	return false;
}
function ensureMessageReactionTable(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_message_react` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`react_scope` tinyint(1) NOT NULL DEFAULT '1',
		`react_target` int(11) NOT NULL DEFAULT '0',
		`react_user` int(11) NOT NULL DEFAULT '0',
		`react_value` tinyint(1) NOT NULL DEFAULT '1',
		`react_key` varchar(190) NOT NULL DEFAULT '',
		`react_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		UNIQUE KEY `react_unique` (`react_scope`, `react_target`, `react_user`),
		KEY `react_target_index` (`react_scope`, `react_target`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$has_key = $mysqli->query("SHOW COLUMNS FROM boom_message_react LIKE 'react_key'");
	if($has_key && $has_key->num_rows < 1){
		$mysqli->query("ALTER TABLE boom_message_react ADD react_key varchar(190) NOT NULL DEFAULT '' AFTER react_value");
	}
	$ready = true;
	return true;
}
function validMessageReaction($type){
	if(in_array((int) $type, array(1,2,3,4), true)){
		return true;
	}
	return false;
}
function messageReactionData($scope, $target){
	global $mysqli, $data;
	$react = [
		'mine'=> '',
		'like'=> 0,
		'dislike'=> 0,
		'love'=> 0,
		'funny'=> 0,
		'total'=> 0,
		'items'=> [],
	];
	$scope = (int) $scope;
	$target = (int) $target;
	if($target < 1 || !in_array($scope, array(1,2), true)){
		return $react;
	}
	try {
		ensureMessageReactionTable();
		$counts = $mysqli->query("SELECT react_value, react_key, COUNT(*) AS total FROM boom_message_react WHERE react_scope = '$scope' AND react_target = '$target' GROUP BY react_value, react_key");
		$count_list = [];
		if($counts){
			while($count = $counts->fetch_assoc()){
				$key = reactionKeyFromRow($count);
				if($key === ''){
					continue;
				}
				$amount = (int) $count['total'];
				if(!isset($count_list[$key])){
					$count_list[$key] = 0;
				}
				$count_list[$key] += $amount;
				switch(reactionValueFromKey($key)){
					case 1:
						$react['like'] += $amount;
						break;
					case 2:
						$react['dislike'] += $amount;
						break;
					case 3:
						$react['love'] += $amount;
						break;
					case 4:
						$react['funny'] += $amount;
						break;
				}
			}
		}
		if(!empty($count_list)){
			arsort($count_list, SORT_NUMERIC);
			foreach($count_list as $key => $amount){
				$react['items'][] = [
					'key' => $key,
					'src' => $key,
					'count' => (int) $amount,
				];
				$react['total'] += (int) $amount;
			}
		}
		if(isset($data['user_id'])){
			$user_id = (int) $data['user_id'];
			if($user_id > 0){
				$mine = $mysqli->query("SELECT react_value, react_key FROM boom_message_react WHERE react_scope = '$scope' AND react_target = '$target' AND react_user = '$user_id' LIMIT 1");
				if($mine && $mine->num_rows > 0){
					$user_react = $mine->fetch_assoc();
					$react['mine'] = reactionKeyFromRow($user_react);
				}
			}
		}
	}
	catch(Throwable $e) {
		return $react;
	}
	return $react;
}

// goofy global events

function canGoofyAdmin(){
	if(boomAllow(99)){
		return true;
	}
	return false;
}
function ensureGoofyEventTable(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_goofy_event` (
		`event_id` int(11) NOT NULL AUTO_INCREMENT,
		`event_type` varchar(20) NOT NULL DEFAULT '',
		`event_room` int(11) NOT NULL DEFAULT '0',
		`event_targets` text,
		`event_data` mediumtext,
		`event_drag` tinyint(1) NOT NULL DEFAULT '0',
		`event_created` int(11) NOT NULL DEFAULT '0',
		`event_expire` int(11) NOT NULL DEFAULT '0',
		`event_user` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`event_id`),
		KEY `event_room_created` (`event_room`, `event_created`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$ready = true;
	return true;
}
function validGoofyEventType($type){
	if(in_array($type, ['announce', 'jumpscare', 'audio', 'goofy'], true)){
		return true;
	}
	return false;
}
function goofyTargetCsvFromNames($raw_names, $room_id = 0){
	global $mysqli;
	$room_id = (int) $room_id;
	$names = explode(',', (string) $raw_names);
	$ids = [];
	foreach($names as $name){
		$name = trim($name);
		if($name === ''){
			continue;
		}
		$name = boomSanitize($name);
		if($name === ''){
			continue;
		}
		$q_room = '';
		if($room_id > 0){
			$q_room = "AND user_roomid = '$room_id'";
		}
		$get = $mysqli->query("SELECT user_id FROM boom_users WHERE user_name = '$name' $q_room LIMIT 1");
		if($get && $get->num_rows > 0){
			$r = $get->fetch_assoc();
			$ids[(int) $r['user_id']] = 1;
		}
	}
	if(empty($ids)){
		return '';
	}
	return implode(',', array_keys($ids));
}
function createGoofyEvent($type, $payload = [], $room_id = 0, $targets = 'all', $draggable = 0, $duration = 30){
	global $mysqli, $data;
	$type = boomSanitize($type);
	if(!validGoofyEventType($type)){
		return false;
	}
	ensureGoofyEventTable();
	$room_id = (int) $room_id;
	$targets = trim((string) $targets);
	if($targets === ''){
		$targets = 'all';
	}
	$draggable = ((int) $draggable > 0) ? 1 : 0;
	$duration = (int) $duration;
	if($duration < 5){
		$duration = 5;
	}
	if($duration > 120){
		$duration = 120;
	}
	$expire = time() + $duration;
	$event_data = escape(json_encode($payload, JSON_UNESCAPED_UNICODE));
	$event_user = isset($data['user_id']) ? (int) $data['user_id'] : 0;
	$targets = escape($targets);
	$mysqli->query("INSERT INTO boom_goofy_event (event_type, event_room, event_targets, event_data, event_drag, event_created, event_expire, event_user)
		VALUES ('$type', '$room_id', '$targets', '$event_data', '$draggable', '" . time() . "', '$expire', '$event_user')");
	return true;
}
function getPendingGoofyEvents($user, $room_id){
	global $mysqli;
	$events = [];
	if(empty($user) || !isset($user['user_id'])){
		return $events;
	}
	ensureGoofyEventTable();
	$room_id = (int) $room_id;
	$uid = (int) $user['user_id'];
	$last_seen = (int) userDataDetails($user, 'goofy_event_seen');
	$get = $mysqli->query("SELECT * FROM boom_goofy_event
		WHERE event_id > '$last_seen' AND (event_room = '0' OR event_room = '$room_id') AND event_expire >= '" . time() . "'
		ORDER BY event_id ASC LIMIT 25");
	$max_seen = $last_seen;
	if($get){
		while($row = $get->fetch_assoc()){
			$eid = (int) $row['event_id'];
			if($eid > $max_seen){
				$max_seen = $eid;
			}
			$targets = trim((string) $row['event_targets']);
			if($targets !== '' && $targets !== 'all'){
				$target_list = array_map('intval', explode(',', $targets));
				if(!in_array($uid, $target_list, true)){
					continue;
				}
			}
			$payload = [];
			if(!empty($row['event_data'])){
				$decoded = json_decode($row['event_data'], true);
				if(is_array($decoded)){
					$payload = $decoded;
				}
			}
			$events[] = [
				'id' => $eid,
				'type' => $row['event_type'],
				'drag' => (int) $row['event_drag'],
				'data' => $payload,
			];
		}
	}
	if($max_seen > $last_seen){
		saveUserData($user, 'goofy_event_seen', $max_seen);
	}
	return $events;
}

// public themes

function ensurePublicThemeTable(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_public_theme` (
		`theme_id` int(11) NOT NULL AUTO_INCREMENT,
		`theme_user` int(11) NOT NULL DEFAULT '0',
		`theme_name` varchar(80) NOT NULL DEFAULT '',
		`theme_slug` varchar(120) NOT NULL DEFAULT '',
		`theme_status` tinyint(1) NOT NULL DEFAULT '0',
		`theme_locked` tinyint(1) NOT NULL DEFAULT '0',
		`theme_config` mediumtext,
		`theme_custom_css` mediumtext,
		`theme_background` varchar(190) NOT NULL DEFAULT '',
		`theme_folder` varchar(80) NOT NULL DEFAULT '',
		`theme_note` varchar(255) NOT NULL DEFAULT '',
		`theme_created` int(11) NOT NULL DEFAULT '0',
		`theme_updated` int(11) NOT NULL DEFAULT '0',
		`theme_submitted` int(11) NOT NULL DEFAULT '0',
		`theme_reviewed` int(11) NOT NULL DEFAULT '0',
		`theme_reviewer` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`theme_id`),
		KEY `theme_user` (`theme_user`),
		KEY `theme_status` (`theme_status`),
		KEY `theme_folder` (`theme_folder`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$has_unique = $mysqli->query("SHOW INDEX FROM boom_public_theme WHERE Key_name = 'theme_user_unique'");
	if($has_unique && $has_unique->num_rows > 0){
		$mysqli->query("ALTER TABLE boom_public_theme DROP INDEX theme_user_unique");
	}
	$has_user_key = $mysqli->query("SHOW INDEX FROM boom_public_theme WHERE Key_name = 'theme_user'");
	if(!$has_user_key || $has_user_key->num_rows < 1){
		$mysqli->query("ALTER TABLE boom_public_theme ADD KEY theme_user (theme_user)");
	}
	ensurePublicThemeInstallTable();
	ensurePublicThemeRateTable();
	$ready = true;
	return true;
}
function ensurePublicThemeInstallTable(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_public_theme_install` (
		`install_id` int(11) NOT NULL AUTO_INCREMENT,
		`theme_id` int(11) NOT NULL DEFAULT '0',
		`user_id` int(11) NOT NULL DEFAULT '0',
		`install_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`install_id`),
		UNIQUE KEY `theme_user` (`theme_id`,`user_id`),
		KEY `install_theme` (`theme_id`),
		KEY `install_user` (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$ready = true;
	return true;
}
function ensurePublicThemeRateTable(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_public_theme_rate` (
		`rate_id` int(11) NOT NULL AUTO_INCREMENT,
		`theme_id` int(11) NOT NULL DEFAULT '0',
		`user_id` int(11) NOT NULL DEFAULT '0',
		`rate_value` tinyint(2) NOT NULL DEFAULT '0',
		`rate_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`rate_id`),
		UNIQUE KEY `theme_user` (`theme_id`,`user_id`),
		KEY `rate_theme` (`theme_id`),
		KEY `rate_user` (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$ready = true;
	return true;
}
function publicThemeTrackInstall($theme_id, $user_id){
	global $mysqli;
	$theme_id = (int) $theme_id;
	$user_id = (int) $user_id;
	if($theme_id < 1 || $user_id < 1){
		return false;
	}
	ensurePublicThemeInstallTable();
	$now = time();
	$mysqli->query("INSERT INTO boom_public_theme_install (theme_id, user_id, install_time)
		VALUES ('$theme_id', '$user_id', '$now')
		ON DUPLICATE KEY UPDATE install_time = '$now'");
	return true;
}
function publicThemeInstallCount($theme_id){
	global $mysqli;
	$theme_id = (int) $theme_id;
	if($theme_id < 1){
		return 0;
	}
	ensurePublicThemeInstallTable();
	$get = $mysqli->query("SELECT COUNT(*) AS total FROM boom_public_theme_install WHERE theme_id = '$theme_id'");
	if($get && $get->num_rows > 0){
		$res = $get->fetch_assoc();
		return (int) $res['total'];
	}
	return 0;
}
function publicThemeSaveRating($theme_id, $user_id, $rate_value){
	global $mysqli;
	$theme_id = (int) $theme_id;
	$user_id = (int) $user_id;
	$rate_value = (int) $rate_value;
	if($theme_id < 1 || $user_id < 1 || $rate_value < 1 || $rate_value > 5){
		return false;
	}
	ensurePublicThemeRateTable();
	$now = time();
	$mysqli->query("INSERT INTO boom_public_theme_rate (theme_id, user_id, rate_value, rate_time)
		VALUES ('$theme_id', '$user_id', '$rate_value', '$now')
		ON DUPLICATE KEY UPDATE rate_value = '$rate_value', rate_time = '$now'");
	return true;
}
function publicThemeUserRating($theme_id, $user_id){
	global $mysqli;
	$theme_id = (int) $theme_id;
	$user_id = (int) $user_id;
	if($theme_id < 1 || $user_id < 1){
		return 0;
	}
	ensurePublicThemeRateTable();
	$get = $mysqli->query("SELECT rate_value FROM boom_public_theme_rate WHERE theme_id = '$theme_id' AND user_id = '$user_id' LIMIT 1");
	if($get && $get->num_rows > 0){
		$res = $get->fetch_assoc();
		return (int) $res['rate_value'];
	}
	return 0;
}
function publicThemeRatingStats($theme_id){
	global $mysqli;
	$theme_id = (int) $theme_id;
	$stats = [
		'avg' => 0,
		'count' => 0,
	];
	if($theme_id < 1){
		return $stats;
	}
	ensurePublicThemeRateTable();
	$get = $mysqli->query("SELECT COUNT(*) AS total, IFNULL(ROUND(AVG(rate_value), 2), 0) AS avg_rate FROM boom_public_theme_rate WHERE theme_id = '$theme_id'");
	if($get && $get->num_rows > 0){
		$res = $get->fetch_assoc();
		$stats['count'] = (int) $res['total'];
		$stats['avg'] = (float) $res['avg_rate'];
	}
	return $stats;
}
function publicThemeCanPublish($user = []){
	if(!empty($user) && isset($user['user_rank'])){
		if((int) $user['user_rank'] >= 50){
			return true;
		}
		return false;
	}
	if(boomAllow(50)){
		return true;
	}
	return false;
}
function publicThemeCanModerate($user = []){
	if(!empty($user) && isset($user['user_rank'])){
		if((int) $user['user_rank'] >= 70){
			return true;
		}
		return false;
	}
	if(boomAllow(70)){
		return true;
	}
	return false;
}
function publicThemeDefaultConfig(){
	return [
		'header_bg' => '#111827',
		'header_text' => '#FFFFFF',
		'chat_bg' => '#0F172A',
		'chat_text' => '#E2E8F0',
		'bubble_bg' => '#1E293B',
		'accent' => '#38BDF8',
		'default_btn' => '#334155',
		'panel_opacity' => 0.85,
		'panel_blur' => 8,
	];
}
function publicThemeSanitizeName($name){
	$name = trim((string) $name);
	$name = preg_replace('/[^a-zA-Z0-9 _-]/', '', $name);
	$name = preg_replace('/\s+/', ' ', $name);
	if(strlen($name) > 32){
		$name = substr($name, 0, 32);
	}
	if(strlen($name) < 3){
		return '';
	}
	return $name;
}
function publicThemeSanitizeHex($color, $fallback){
	$color = trim((string) $color);
	if(validColor($color)){
		return strtoupper($color);
	}
	return $fallback;
}
function publicThemeSanitizeRange($value, $min, $max, $default, $precision = 2){
	if(!is_numeric($value)){
		return $default;
	}
	$number = (float) $value;
	if($number < $min){
		$number = $min;
	}
	if($number > $max){
		$number = $max;
	}
	if($precision <= 0){
		return (int) round($number);
	}
	$factor = pow(10, $precision);
	return floor(($number * $factor) + 0.5) / $factor;
}
function publicThemeSanitizeCss($css){
	$css = trim((string) $css);
	if($css === ''){
		return '';
	}
	$css = str_ireplace(['<?php', '<?', '?>', '<script', '</script', '<style', '</style'], '', $css);
	$css = str_replace(['<', '>'], '', $css);
	if(strlen($css) > 6000){
		$css = substr($css, 0, 6000);
	}
	return $css;
}
function publicThemeSanitizeConfig($raw){
	$default = publicThemeDefaultConfig();
	if(!is_array($raw)){
		$raw = [];
	}
	$config = $default;
	$config['header_bg'] = publicThemeSanitizeHex($raw['header_bg'] ?? $default['header_bg'], $default['header_bg']);
	$config['header_text'] = publicThemeSanitizeHex($raw['header_text'] ?? $default['header_text'], $default['header_text']);
	$config['chat_bg'] = publicThemeSanitizeHex($raw['chat_bg'] ?? $default['chat_bg'], $default['chat_bg']);
	$config['chat_text'] = publicThemeSanitizeHex($raw['chat_text'] ?? $default['chat_text'], $default['chat_text']);
	$config['bubble_bg'] = publicThemeSanitizeHex($raw['bubble_bg'] ?? $default['bubble_bg'], $default['bubble_bg']);
	$config['accent'] = publicThemeSanitizeHex($raw['accent'] ?? $default['accent'], $default['accent']);
	$config['default_btn'] = publicThemeSanitizeHex($raw['default_btn'] ?? $default['default_btn'], $default['default_btn']);
	$config['panel_opacity'] = publicThemeSanitizeRange($raw['panel_opacity'] ?? $default['panel_opacity'], 0.30, 1.00, $default['panel_opacity'], 2);
	$config['panel_blur'] = publicThemeSanitizeRange($raw['panel_blur'] ?? $default['panel_blur'], 0, 24, $default['panel_blur'], 0);
	return $config;
}
function publicThemeNormalizeBackground($background){
	$background = trim((string) $background);
	if($background === ''){
		return '';
	}
	$background = str_replace('\\', '/', $background);
	if(strpos($background, BOOM_DOMAIN) === 0){
		$background = substr($background, strlen(BOOM_DOMAIN));
	}
	$background = ltrim($background, '/');
	$marker = '';
	$marker_pos = false;
	$markers = ['upload/theme_public/', 'theme_public/'];
	foreach($markers as $m){
		$pos = strpos($background, $m);
		if($pos !== false){
			$marker = $m;
			$marker_pos = $pos;
			break;
		}
	}
	if($marker_pos === false){
		return '';
	}
	$background = substr($background, $marker_pos);
	if(strpos($background, '..') !== false){
		return '';
	}
	if($marker !== '' && strpos($background, $marker) !== 0){
		return '';
	}
	if(strpos($background, 'theme_public/') === 0 && strpos($background, 'upload/theme_public/') !== 0){
		$upload_candidate = 'upload/' . $background;
		if(file_exists(BOOM_PATH . '/' . $upload_candidate)){
			$background = $upload_candidate;
		}
	}
	if(strpos($background, 'upload/theme_public/') === 0 && !file_exists(BOOM_PATH . '/' . $background)){
		$legacy_candidate = substr($background, 7);
		if($legacy_candidate !== false && file_exists(BOOM_PATH . '/' . $legacy_candidate)){
			$background = $legacy_candidate;
		}
	}
	if(!file_exists(BOOM_PATH . '/' . $background)){
		return '';
	}
	return $background;
}
function publicThemeBackgroundUrl($background){
	$background = publicThemeNormalizeBackground($background);
	if($background === ''){
		return '';
	}
	return publicThemeWebBaseUrl() . ltrim($background, '/');
}
function publicThemeWebBaseUrl(){
	if(BOOM_DOMAIN !== ''){
		return rtrim(BOOM_DOMAIN, '/') . '/';
	}
	$script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';
	if($script !== ''){
		$system_pos = strpos($script, '/system/');
		if($system_pos !== false){
			$root = substr($script, 0, $system_pos);
			if($root === ''){
				return '/';
			}
			return rtrim($root, '/') . '/';
		}
		$dir = str_replace('\\', '/', dirname($script));
		if($dir === '' || $dir === '.' || $dir === '\\'){
			return '/';
		}
		return rtrim($dir, '/') . '/';
	}
	return '/';
}
function publicThemeBackgroundCssUrl($background){
	$background = publicThemeNormalizeBackground($background);
	if($background === ''){
		return '';
	}
	if(BOOM_DOMAIN !== ''){
		return BOOM_DOMAIN . $background;
	}
	return '../../../' . $background;
}
function publicThemeConfigFromRow($row){
	if(!is_array($row) || empty($row)){
		return publicThemeDefaultConfig();
	}
	$raw = [];
	if(isset($row['theme_config']) && $row['theme_config'] !== ''){
		$decoded = json_decode($row['theme_config'], true);
		if(is_array($decoded)){
			$raw = $decoded;
		}
	}
	return publicThemeSanitizeConfig($raw);
}
function publicThemeFolderName($theme_id){
	$theme_id = (int) $theme_id;
	if($theme_id < 1){
		return '';
	}
	return 'pt_' . $theme_id;
}
function publicThemeGetByUser($user_id){
	global $mysqli;
	$user_id = (int) $user_id;
	if($user_id < 1){
		return [];
	}
	ensurePublicThemeTable();
	$get = $mysqli->query("SELECT * FROM boom_public_theme WHERE theme_user = '$user_id' ORDER BY theme_updated DESC, theme_id DESC LIMIT 1");
	if($get && $get->num_rows > 0){
		return $get->fetch_assoc();
	}
	return [];
}
function publicThemeGetUserThemeById($user_id, $theme_id){
	global $mysqli;
	$user_id = (int) $user_id;
	$theme_id = (int) $theme_id;
	if($user_id < 1 || $theme_id < 1){
		return [];
	}
	ensurePublicThemeTable();
	$get = $mysqli->query("SELECT * FROM boom_public_theme WHERE theme_id = '$theme_id' AND theme_user = '$user_id' LIMIT 1");
	if($get && $get->num_rows > 0){
		return $get->fetch_assoc();
	}
	return [];
}
function publicThemeGetUserThemes($user_id, $limit = 50){
	global $mysqli;
	$user_id = (int) $user_id;
	$limit = (int) $limit;
	if($user_id < 1){
		return [];
	}
	if($limit < 1){
		$limit = 1;
	}
	if($limit > 100){
		$limit = 100;
	}
	ensurePublicThemeTable();
	$list = [];
	$get = $mysqli->query("SELECT * FROM boom_public_theme WHERE theme_user = '$user_id' ORDER BY theme_updated DESC, theme_id DESC LIMIT $limit");
	if($get){
		$list = $get->fetch_all(MYSQLI_ASSOC);
	}
	return $list;
}
function publicThemeGetById($theme_id){
	global $mysqli;
	$theme_id = (int) $theme_id;
	if($theme_id < 1){
		return [];
	}
	ensurePublicThemeTable();
	$get = $mysqli->query("SELECT * FROM boom_public_theme WHERE theme_id = '$theme_id' LIMIT 1");
	if($get && $get->num_rows > 0){
		return $get->fetch_assoc();
	}
	return [];
}
function publicThemeHexToRgb($hex){
	$hex = ltrim((string) $hex, '#');
	if(strlen($hex) !== 6){
		return [255, 255, 255];
	}
	return [
		hexdec(substr($hex, 0, 2)),
		hexdec(substr($hex, 2, 2)),
		hexdec(substr($hex, 4, 2)),
	];
}
function publicThemeRgba($hex, $alpha){
	$rgb = publicThemeHexToRgb($hex);
	$alpha = publicThemeSanitizeRange($alpha, 0, 1, 1, 2);
	return 'rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',' . $alpha . ')';
}
function publicThemeBuildCss($config, $background = '', $custom_css = ''){
	$config = publicThemeSanitizeConfig($config);
	$background = publicThemeBackgroundCssUrl($background);
	$custom_css = publicThemeSanitizeCss($custom_css);

	$header_bg = $config['header_bg'];
	$header_text = $config['header_text'];
	$chat_bg = $config['chat_bg'];
	$chat_text = $config['chat_text'];
	$bubble_bg = $config['bubble_bg'];
	$accent = $config['accent'];
	$default_btn = $config['default_btn'];
	$panel_opacity = $config['panel_opacity'];
	$panel_blur = (int) $config['panel_blur'];

	$panel_bg = publicThemeRgba($chat_bg, $panel_opacity);
	$bubble_soft = publicThemeRgba($bubble_bg, min(1, $panel_opacity + 0.1));
	$line_soft = publicThemeRgba($header_text, 0.14);
	$hover_soft = publicThemeRgba($header_text, 0.10);
	$input_bg = publicThemeRgba($header_text, 0.06);

	$sheet = '';
	$sheet .= "@import url('../Lite/Lite.css" . boomFileVersion() . "');\n";
	$sheet .= "a { color: {$accent}; }\n";
	$sheet .= "body { background: {$chat_bg}; color: {$chat_text};";
	if($background !== ''){
		$sheet .= " background-image: url('{$background}'); background-size: cover; background-position: center center; background-attachment: fixed;";
	}
	$sheet .= " }\n";
	$sheet .= "input, textarea, .post_input_container { background: {$input_bg}; border: 1px solid {$line_soft} !important; color: {$chat_text}; }\n";
	$sheet .= ".setdef, .default_color, .user { color: {$chat_text}; }\n";
	$sheet .= ".bhead, .bsidebar, .modal_top, .pro_top, .bfoot, .foot, .back_pmenu, .back_ptop { background: {$header_bg}; color: {$header_text}; fill: {$header_text}; }\n";
	$sheet .= ".theme_color, .menui, .subi { color: {$accent}; }\n";
	$sheet .= ".theme_btn, .back_theme, .my_notice { background: {$accent}; color: {$header_text}; }\n";
	$sheet .= ".default_btn, .back_default, .defaultd_btn, .send_btn { background: {$default_btn}; color: {$header_text}; }\n";
	$sheet .= ".backglob, .back_chat, .back_priv, .back_panel, .back_menu, .back_box, .back_input, .back_modal, .page_element, .back_quote { background: {$panel_bg}; color: {$chat_text}; }\n";
	$sheet .= ".mbubble, .hunter_private, .targ_quote, .reply_item, .cquote { background: {$bubble_soft}; color: {$chat_text}; }\n";
	$sheet .= ".my_log, .target_private, .hunt_quote { background: {$bubble_bg}; color: {$header_text}; }\n";
	$sheet .= ".chat_system, .sub_text, .sub_date, .input_item { color: {$line_soft}; }\n";
	$sheet .= ".bback, .bbackhover, .modal_mback { background: {$input_bg}; }\n";
	$sheet .= ".bhover:hover, .bhoverr:hover, .bbackhover:hover, .blisting:hover, .submenu:hover, .bmenu:hover, .bpmenu:hover, .bsub:hover { background: {$hover_soft}; }\n";
	$sheet .= ".bborder, .tborder, .lborder, .rborder, .fborder, .blisting, .blist, .float_top, .float_ctop, .modal_mborder { border-color: {$line_soft}; }\n";
	$sheet .= ".bshadow, .page_element, .float_menu, .btnshadow, .pboxed, .tab_menu { box-shadow: 0 8px 24px rgba(0,0,0,0.35); }\n";
	$sheet .= ".modal_back { background-color: rgba(0,0,0,0.55); }\n";
	if($panel_blur > 0){
		$sheet .= ".backglob, .back_chat, .back_priv, .back_panel, .back_menu, .back_box, .back_input, .back_modal, .page_element, .back_quote { backdrop-filter: blur({$panel_blur}px); -webkit-backdrop-filter: blur({$panel_blur}px); }\n";
	}
	if($custom_css !== ''){
		$sheet .= "\n/* custom css */\n" . $custom_css . "\n";
	}
	return $sheet;
}
function publicThemeWriteCssFile($theme){
	if(empty($theme) || !isset($theme['theme_id'])){
		return '';
	}
	$folder = publicThemeFolderName($theme['theme_id']);
	if($folder === ''){
		return '';
	}
	$path = BOOM_PATH . '/css/themes/' . $folder;
	if(!is_dir($path)){
		@mkdir($path, 0755, true);
	}
	if(!is_dir($path)){
		return '';
	}
	$config = publicThemeConfigFromRow($theme);
	$custom_css = isset($theme['theme_custom_css']) ? (string) $theme['theme_custom_css'] : '';
	$background = isset($theme['theme_background']) ? (string) $theme['theme_background'] : '';
	$sheet = publicThemeBuildCss($config, $background, $custom_css);
	$target = $path . '/' . $folder . '.css';
	if(@file_put_contents($target, $sheet) === false){
		return '';
	}
	return $folder;
}
function isApprovedPublicThemeFolder($folder){
	global $mysqli;
	static $cache = [];
	$folder = trim((string) $folder);
	if($folder === ''){
		return false;
	}
	if(isset($cache[$folder])){
		return $cache[$folder];
	}
	ensurePublicThemeTable();
	$safe = escape($folder);
	$check = $mysqli->query("SELECT theme_id FROM boom_public_theme WHERE theme_folder = '$safe' AND theme_status = '2' LIMIT 1");
	if($check && $check->num_rows > 0){
		$cache[$folder] = true;
		return true;
	}
	$cache[$folder] = false;
	return false;
}
function publicThemeStatusText($status){
	switch((int) $status){
		case 1:
			return 'Pending review';
		case 2:
			return 'Approved';
		case 3:
			return 'Rejected';
		default:
			return 'Draft';
	}
}
function publicThemeStatusClass($status){
	switch((int) $status){
		case 1:
			return 'pending';
		case 2:
			return 'approved';
		case 3:
			return 'rejected';
		default:
			return 'draft';
	}
}

// chat effects

function chatEffectList(){
	return [
		1 => ['title' => 'Flash Pop', 'price' => 100, 'class' => 'cefx_1', 'desc' => 'Fast scale burst'],
		2 => ['title' => 'Vortex Lift', 'price' => 200, 'class' => 'cefx_2', 'desc' => 'Pull-up with spin'],
		3 => ['title' => 'Shock Swing', 'price' => 300, 'class' => 'cefx_3', 'desc' => 'Hard swing entry'],
		4 => ['title' => 'Pulse Burst', 'price' => 400, 'class' => 'cefx_4', 'desc' => 'Punchy pulse wave'],
		5 => ['title' => 'Blade Tilt', 'price' => 500, 'class' => 'cefx_5', 'desc' => 'Sharp side slash'],
		6 => ['title' => 'Jelly Quake', 'price' => 600, 'class' => 'cefx_6', 'desc' => 'Elastic bounce quake'],
		7 => ['title' => 'Ignite Bloom', 'price' => 700, 'class' => 'cefx_7', 'desc' => 'Bright bloom snap'],
		8 => ['title' => 'Rocket Break', 'price' => 800, 'class' => 'cefx_8', 'desc' => 'Launch and settle'],
		9 => ['title' => 'Wave Crush', 'price' => 900, 'class' => 'cefx_9', 'desc' => 'Crashing side wave'],
		10 => ['title' => 'Ground Slam', 'price' => 1000, 'class' => 'cefx_10', 'desc' => 'Heavy 2D ground burst'],
		11 => ['title' => 'Volt Snap', 'price' => 1200, 'class' => 'cefx_11', 'desc' => 'Quick electric snap'],
		12 => ['title' => 'Rift Shift', 'price' => 1500, 'class' => 'cefx_12', 'desc' => 'Phase-in glitch shift'],
		13 => ['title' => 'Hammer Drop', 'price' => 2000, 'class' => 'cefx_13', 'desc' => 'Weighted drop impact'],
		14 => ['title' => 'Comet Rush', 'price' => 2600, 'class' => 'cefx_14', 'desc' => 'Comet trail streak'],
		15 => ['title' => 'Echo Prism', 'price' => 3200, 'class' => 'cefx_15', 'desc' => 'Layered prism echo'],
		16 => ['title' => 'Duel Breaker', 'price' => 5000, 'class' => 'cefx_16 cefx_link', 'desc' => '2D strike that hits the previous message'],
		17 => ['title' => 'Phantom Blink', 'price' => 5600, 'class' => 'cefx_17', 'desc' => 'Ghost blur blink-in'],
		18 => ['title' => 'Orbit Spin', 'price' => 6400, 'class' => 'cefx_18', 'desc' => '3D orbit spin landing'],
		19 => ['title' => 'Glitch Cut', 'price' => 7300, 'class' => 'cefx_19', 'desc' => 'RGB glitch strike'],
		20 => ['title' => 'Nova Pulse', 'price' => 8200, 'class' => 'cefx_20', 'desc' => 'Nova flash shock ring'],
		21 => ['title' => 'Meteor Drop', 'price' => 9200, 'class' => 'cefx_21', 'desc' => 'Meteor dive rebound'],
		22 => ['title' => 'Time Warp', 'price' => 10300, 'class' => 'cefx_22', 'desc' => 'Time warp snap-back'],
		23 => ['title' => 'Hyper Flip', 'price' => 11600, 'class' => 'cefx_23', 'desc' => 'Hyper flip impact'],
		24 => ['title' => 'Chain Impact', 'price' => 13000, 'class' => 'cefx_24 cefx_link', 'desc' => 'Impact that hits the previous message'],
	];
}
function validChatEffect($effect){
	if(isset(chatEffectList()[(int) $effect])){
		return true;
	}
}
function ensureChatEffectTables(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_chat_effect` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` int(11) NOT NULL DEFAULT '0',
		`effect_id` tinyint(2) NOT NULL DEFAULT '0',
		`effect_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		UNIQUE KEY `user_effect` (`user_id`,`effect_id`),
		KEY `effect_user` (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_chat_effect_selected` (
		`user_id` int(11) NOT NULL DEFAULT '0',
		`effect_id` tinyint(2) NOT NULL DEFAULT '0',
		`set_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$ready = true;
	return true;
}
function userChatEffectOwned($user_id){
	global $mysqli;
	$owned = [];
	$user_id = (int) $user_id;
	if($user_id < 1){
		return $owned;
	}
	ensureChatEffectTables();
	$get_owned = $mysqli->query("SELECT effect_id FROM boom_chat_effect WHERE user_id = '$user_id'");
	if($get_owned){
		while($item = $get_owned->fetch_assoc()){
			$owned[(int) $item['effect_id']] = 1;
		}
	}
	return $owned;
}
function userChatEffectSelected($user_id){
	global $mysqli;
	static $selected_cache = [];
	$user_id = (int) $user_id;
	if($user_id < 1){
		return 0;
	}
	if(isset($selected_cache[$user_id])){
		return $selected_cache[$user_id];
	}
	$selected_cache[$user_id] = 0;
	ensureChatEffectTables();
	$get_selected = $mysqli->query("SELECT sel.effect_id FROM boom_chat_effect_selected sel
		LEFT JOIN boom_chat_effect own ON own.user_id = sel.user_id AND own.effect_id = sel.effect_id
		WHERE sel.user_id = '$user_id' AND own.effect_id IS NOT NULL LIMIT 1");
	if($get_selected && $get_selected->num_rows > 0){
		$selected = $get_selected->fetch_assoc();
		if(validChatEffect($selected['effect_id'])){
			$selected_cache[$user_id] = (int) $selected['effect_id'];
		}
	}
	return $selected_cache[$user_id];
}
function setUserChatEffect($user_id, $effect_id){
	global $mysqli;
	$user_id = (int) $user_id;
	$effect_id = (int) $effect_id;
	if($user_id < 1){
		return false;
	}
	ensureChatEffectTables();
	if($effect_id < 1){
		$mysqli->query("DELETE FROM boom_chat_effect_selected WHERE user_id = '$user_id'");
		return true;
	}
	if(!validChatEffect($effect_id)){
		return false;
	}
	$mysqli->query("REPLACE INTO boom_chat_effect_selected (user_id, effect_id, set_time) VALUES ('$user_id', '$effect_id', '" . time() . "')");
	return true;
}
function chatEffectClass($effect_id){
	$effects = chatEffectList();
	$effect_id = (int) $effect_id;
	if(isset($effects[$effect_id])){
		return $effects[$effect_id]['class'];
	}
	return '';
}
function messageEffectClass($user_id, $log_time){
	$user_id = (int) $user_id;
	$log_time = (int) $log_time;
	if($user_id < 1 || $log_time < 1){
		return '';
	}
	if(!animationAllowed('chatfx')){
		return '';
	}
	if($log_time < (time() - 30)){
		return '';
	}
	$effect = userChatEffectSelected($user_id);
	if($effect < 1){
		return '';
	}
	return chatEffectClass($effect);
}

// profile customization effects

function profileEffectCatalog(){
	return [
		'avatar_frame' => [
			'title' => 'Avatar Frames',
			'desc' => 'Premium avatar rings and edge accents',
			'effects' => [
				1 => ['title' => 'Neon Ring', 'price' => 300, 'class' => 'pfx_af_1', 'desc' => 'Hot pink neon frame'],
				2 => ['title' => 'Royal Crest', 'price' => 420, 'class' => 'pfx_af_2', 'desc' => 'Gold crown-inspired border'],
				3 => ['title' => 'Glitch Halo', 'price' => 560, 'class' => 'pfx_af_3', 'desc' => 'Cyber split light ring'],
				4 => ['title' => 'Candy Pop', 'price' => 700, 'class' => 'pfx_af_4', 'desc' => 'Playful bright color frame'],
				5 => ['title' => 'Ocean Pulse', 'price' => 860, 'class' => 'pfx_af_5', 'desc' => 'Cool blue ripple edge'],
				6 => ['title' => 'Ember Crown', 'price' => 1040, 'class' => 'pfx_af_6', 'desc' => 'Warm fire-edge glow'],
				7 => ['title' => 'Lunar Silver', 'price' => 1220, 'class' => 'pfx_af_7', 'desc' => 'Clean moonlight silver'],
				8 => ['title' => 'Emerald Arc', 'price' => 1440, 'class' => 'pfx_af_8', 'desc' => 'Gem green arc border'],
				9 => ['title' => 'Rose Quartz', 'price' => 1700, 'class' => 'pfx_af_9', 'desc' => 'Soft crystal pink frame'],
				10 => ['title' => 'Cyber Hex', 'price' => 1980, 'class' => 'pfx_af_10', 'desc' => 'Hex-tech polished ring'],
			],
		],
		'name_style' => [
			'title' => 'Name Styles',
			'desc' => 'Custom premium username rendering for profiles',
			'effects' => [
				1 => ['title' => 'Solar Glow', 'price' => 260, 'class' => 'pfx_ns_1', 'desc' => 'Bright warm glow name'],
				2 => ['title' => 'Arctic Shine', 'price' => 380, 'class' => 'pfx_ns_2', 'desc' => 'Cool icy highlight'],
				3 => ['title' => 'Violet Beam', 'price' => 520, 'class' => 'pfx_ns_3', 'desc' => 'Bold violet sheen'],
				4 => ['title' => 'Mint Luster', 'price' => 680, 'class' => 'pfx_ns_4', 'desc' => 'Fresh mint edge glow'],
				5 => ['title' => 'Amber Script', 'price' => 820, 'class' => 'pfx_ns_5', 'desc' => 'Warm amber emphasis'],
				6 => ['title' => 'Ruby Flash', 'price' => 980, 'class' => 'pfx_ns_6', 'desc' => 'Red jewel highlight'],
				7 => ['title' => 'Skyline Bold', 'price' => 1160, 'class' => 'pfx_ns_7', 'desc' => 'City-neon text weight'],
				8 => ['title' => 'Pearl Tone', 'price' => 1380, 'class' => 'pfx_ns_8', 'desc' => 'Smooth pearl sheen'],
				9 => ['title' => 'Volt Type', 'price' => 1660, 'class' => 'pfx_ns_9', 'desc' => 'Electric accent name'],
				10 => ['title' => 'Diamond Voice', 'price' => 1960, 'class' => 'pfx_ns_10', 'desc' => 'Premium crystal text'],
			],
		],
		'profile_skin' => [
			'title' => 'Profile Skins',
			'desc' => 'Colorway themes for your profile card',
			'effects' => [
				1 => ['title' => 'Midnight Drift', 'price' => 420, 'class' => 'pfx_ps_1', 'desc' => 'Dark blue gradient shell'],
				2 => ['title' => 'Peach Storm', 'price' => 560, 'class' => 'pfx_ps_2', 'desc' => 'Orange-pink glow blend'],
				3 => ['title' => 'Emerald Night', 'price' => 720, 'class' => 'pfx_ps_3', 'desc' => 'Green noir blend'],
				4 => ['title' => 'Polar Glass', 'price' => 900, 'class' => 'pfx_ps_4', 'desc' => 'Frosted white-blue skin'],
				5 => ['title' => 'Sunset Core', 'price' => 1080, 'class' => 'pfx_ps_5', 'desc' => 'Sunset orange core'],
				6 => ['title' => 'Lavender Haze', 'price' => 1280, 'class' => 'pfx_ps_6', 'desc' => 'Smooth violet skin'],
				7 => ['title' => 'Marine Flux', 'price' => 1520, 'class' => 'pfx_ps_7', 'desc' => 'Deep marine blend'],
				8 => ['title' => 'Ruby Shift', 'price' => 1760, 'class' => 'pfx_ps_8', 'desc' => 'Rich red gradient'],
				9 => ['title' => 'Aurora Fade', 'price' => 2020, 'class' => 'pfx_ps_9', 'desc' => 'Northern light sweep'],
				10 => ['title' => 'Obsidian Luxe', 'price' => 2360, 'class' => 'pfx_ps_10', 'desc' => 'Premium dark luxe shell'],
			],
		],
		'mood_badge' => [
			'title' => 'Mood Badges',
			'desc' => 'Premium badge styling for rank and mood',
			'effects' => [
				1 => ['title' => 'Cherry Pulse', 'price' => 220, 'class' => 'pfx_mb_1', 'desc' => 'Crisp cherry badge'],
				2 => ['title' => 'Ocean Dot', 'price' => 320, 'class' => 'pfx_mb_2', 'desc' => 'Blue modern badge'],
				3 => ['title' => 'Mint Chip', 'price' => 440, 'class' => 'pfx_mb_3', 'desc' => 'Mint rounded badge'],
				4 => ['title' => 'Citrus Pop', 'price' => 580, 'class' => 'pfx_mb_4', 'desc' => 'Lime neon badge'],
				5 => ['title' => 'Rose Mark', 'price' => 740, 'class' => 'pfx_mb_5', 'desc' => 'Rose signal badge'],
				6 => ['title' => 'Plasma Dot', 'price' => 920, 'class' => 'pfx_mb_6', 'desc' => 'Electric purple badge'],
				7 => ['title' => 'Blaze Patch', 'price' => 1120, 'class' => 'pfx_mb_7', 'desc' => 'Orange fire badge'],
				8 => ['title' => 'Sky Stamp', 'price' => 1360, 'class' => 'pfx_mb_8', 'desc' => 'Clear sky pill badge'],
				9 => ['title' => 'Pearl Tag', 'price' => 1640, 'class' => 'pfx_mb_9', 'desc' => 'Pearly clean badge'],
				10 => ['title' => 'Royal Plate', 'price' => 1980, 'class' => 'pfx_mb_10', 'desc' => 'Luxury deep badge'],
			],
		],
		'profile_aura' => [
			'title' => 'Profile Aura',
			'desc' => 'Ambient aura overlays around your profile top',
			'effects' => [
				1 => ['title' => 'Spark Orbit', 'price' => 480, 'class' => 'pfx_pa_1', 'desc' => 'Fast sparkle ring'],
				2 => ['title' => 'Mist Ring', 'price' => 620, 'class' => 'pfx_pa_2', 'desc' => 'Soft mist swirl'],
				3 => ['title' => 'Pulse Halo', 'price' => 780, 'class' => 'pfx_pa_3', 'desc' => 'Heartbeat halo aura'],
				4 => ['title' => 'Nova Flow', 'price' => 960, 'class' => 'pfx_pa_4', 'desc' => 'Bright nova flow'],
				5 => ['title' => 'Crystal Wind', 'price' => 1180, 'class' => 'pfx_pa_5', 'desc' => 'Glassy wind shimmer'],
				6 => ['title' => 'Ion Drift', 'price' => 1420, 'class' => 'pfx_pa_6', 'desc' => 'Ionized drift aura'],
				7 => ['title' => 'Solar Veil', 'price' => 1680, 'class' => 'pfx_pa_7', 'desc' => 'Warm sun veil'],
				8 => ['title' => 'Moon Dust', 'price' => 1960, 'class' => 'pfx_pa_8', 'desc' => 'Silvery moon particles'],
				9 => ['title' => 'Prism Arc', 'price' => 2280, 'class' => 'pfx_pa_9', 'desc' => 'Prismatic moving arc'],
				10 => ['title' => 'Eclipse Crown', 'price' => 2600, 'class' => 'pfx_pa_10', 'desc' => 'Premium eclipse aura'],
			],
		],
	];
}
function profileEffectCategories(){
	$catalog = profileEffectCatalog();
	$list = [];
	foreach($catalog as $key => $cat){
		$list[$key] = [
			'title' => $cat['title'],
			'desc' => isset($cat['desc']) ? $cat['desc'] : '',
		];
	}
	return $list;
}
function validProfileEffectCategory($category){
	$catalog = profileEffectCatalog();
	if(isset($catalog[$category])){
		return true;
	}
	return false;
}
function profileEffectList($category){
	$catalog = profileEffectCatalog();
	if(isset($catalog[$category]['effects']) && is_array($catalog[$category]['effects'])){
		return $catalog[$category]['effects'];
	}
	return [];
}
function validProfileEffect($category, $effect_id){
	$effects = profileEffectList($category);
	if(isset($effects[(int) $effect_id])){
		return true;
	}
	return false;
}
function profileEffectPrice($category, $effect_id){
	$effects = profileEffectList($category);
	$effect_id = (int) $effect_id;
	if(isset($effects[$effect_id])){
		return (int) $effects[$effect_id]['price'];
	}
	return 0;
}
function profileEffectClassById($category, $effect_id){
	$effects = profileEffectList($category);
	$effect_id = (int) $effect_id;
	if(isset($effects[$effect_id]) && !empty($effects[$effect_id]['class'])){
		return $effects[$effect_id]['class'];
	}
	return '';
}
function ensureProfileEffectTables(){
	global $mysqli;
	static $ready = false;
	if($ready){
		return true;
	}
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_profile_effect` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user_id` int(11) NOT NULL DEFAULT '0',
		`effect_category` varchar(40) NOT NULL DEFAULT '',
		`effect_id` smallint(4) NOT NULL DEFAULT '0',
		`effect_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		UNIQUE KEY `user_effect_cat` (`user_id`,`effect_category`,`effect_id`),
		KEY `user_cat` (`user_id`,`effect_category`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_profile_effect_selected` (
		`user_id` int(11) NOT NULL DEFAULT '0',
		`effect_category` varchar(40) NOT NULL DEFAULT '',
		`effect_id` smallint(4) NOT NULL DEFAULT '0',
		`set_time` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`user_id`,`effect_category`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	$ready = true;
	return true;
}
function userProfileEffectOwned($user_id, $category){
	global $mysqli;
	$owned = [];
	$user_id = (int) $user_id;
	$category = trim((string) $category);
	if($user_id < 1 || !validProfileEffectCategory($category)){
		return $owned;
	}
	ensureProfileEffectTables();
	$category = escape($category);
	$get_owned = $mysqli->query("SELECT effect_id FROM boom_profile_effect WHERE user_id = '$user_id' AND effect_category = '$category'");
	if($get_owned){
		while($item = $get_owned->fetch_assoc()){
			$owned[(int) $item['effect_id']] = 1;
		}
	}
	return $owned;
}
function userProfileEffectSelected($user_id, $category){
	global $mysqli;
	static $selected_cache = [];
	$user_id = (int) $user_id;
	$category = trim((string) $category);
	if($user_id < 1 || !validProfileEffectCategory($category)){
		return 0;
	}
	$cache_key = $user_id . '_' . $category;
	if(isset($selected_cache[$cache_key])){
		return $selected_cache[$cache_key];
	}
	$selected_cache[$cache_key] = 0;
	ensureProfileEffectTables();
	$esc_cat = escape($category);
	$get_selected = $mysqli->query("SELECT sel.effect_id FROM boom_profile_effect_selected sel
		LEFT JOIN boom_profile_effect own ON own.user_id = sel.user_id AND own.effect_category = sel.effect_category AND own.effect_id = sel.effect_id
		WHERE sel.user_id = '$user_id' AND sel.effect_category = '$esc_cat' AND own.effect_id IS NOT NULL LIMIT 1");
	if($get_selected && $get_selected->num_rows > 0){
		$selected = $get_selected->fetch_assoc();
		if(validProfileEffect($category, $selected['effect_id'])){
			$selected_cache[$cache_key] = (int) $selected['effect_id'];
		}
	}
	return $selected_cache[$cache_key];
}
function setUserProfileEffect($user_id, $category, $effect_id){
	global $mysqli;
	$user_id = (int) $user_id;
	$effect_id = (int) $effect_id;
	$category = trim((string) $category);
	if($user_id < 1 || !validProfileEffectCategory($category)){
		return false;
	}
	ensureProfileEffectTables();
	$esc_cat = escape($category);
	if($effect_id < 1){
		$mysqli->query("DELETE FROM boom_profile_effect_selected WHERE user_id = '$user_id' AND effect_category = '$esc_cat'");
		return true;
	}
	if(!validProfileEffect($category, $effect_id)){
		return false;
	}
	$mysqli->query("REPLACE INTO boom_profile_effect_selected (user_id, effect_category, effect_id, set_time) VALUES ('$user_id', '$esc_cat', '$effect_id', '" . time() . "')");
	return true;
}
function userProfileEffectClasses($user_id){
	$user_id = (int) $user_id;
	if($user_id < 1){
		return '';
	}
	$classes = [];
	foreach(profileEffectCategories() as $cat => $meta){
		$selected = userProfileEffectSelected($user_id, $cat);
		if($selected > 0){
			$class = profileEffectClassById($cat, $selected);
			if($class !== ''){
				$classes[] = $class;
			}
		}
	}
	return implode(' ', $classes);
}

// create logs

function createLog($log, $quote = []){
	global $data;
	if(isGhosted($data)){
		$log['pghost'] = 0;
	}
	if($log['qpost'] > 0 && empty($quote)){
		$quote = quoteDetails($log['qpost']);
	}
	if(empty($quote)){
		$quote_data = null;
	}
	else {
		$quote_data = [
			'qid'=> (int) $quote['post_id'],
			'quser'=> (int) $quote['user_id'],
			'qname'=> $quote['user_name'],
			'qtumb'=> avatarFile($quote['user_tumb']),
			'qcontent'=> processQuoteMessage($quote['post_message']),
		];
	}
	return [
		'user_id'=> (int) $log['user_id'],
		'user_name'=> $log['user_name'],
		'user_rank'=> (int) $log['user_rank'],
		'user_level'=> (int) $log['user_level'],
		'user_tumb'=> avatarFile($log['user_tumb']),
		'user_cover'=> $log['user_cover'],
		'user_color'=> myColorFont($log),
		'user_tcolor'=> myBubbleColor($log),
		'user_bot'=> (int) $log['user_bot'],
		'user_gender'=> uGender($log),
		'gborder'=> genderBorder($log['user_sex']),
		'user_country'=> uCountry($log),
		'user_age'=> uAge($log),
		'user_roomid'=> (int) $log['post_roomid'],
		'log_id'=> (int) $log['post_id'],
		'log_type'=> $log['type'],
		'log_content'=> processChatMessage($log),
		'log_date'=> chatDate($log['post_date']),
		'log_time'=> $log['post_date'],
		'log_rank'=> (int) $log['log_rank'],
		'log_sys'=> (int) $log['syslog'],
		'log_uid'=> (int) $log['log_uid'],
		'quote'=> $quote_data,
		'gpost'=> (int) $log['pghost'],
		'reaction'=> ((int) $log['syslog'] === 0) ? messageReactionData(1, $log['post_id']) : null,
		'effect'=> messageEffectClass($log['user_id'], $log['post_date']),
		'tid'=> (int) $log['tid'],
		'tname'=> $log['tname'],
		'custom'=> $log['custom'],
	];
}
function exportLogs($logs){
	$result = [];
	foreach($logs as $log){
		$result[] = createLog($log);
	}
	return $result;
}
function getChatLog($id){
	global $mysqli;
	$log = [];
	$get_log = $mysqli->query("
		SELECT boom_chat.*,
		boom_users.user_name, boom_users.user_color, boom_users.user_font, boom_users.user_rank, boom_users.user_level, boom_users.bccolor, boom_users.bcbold, boom_users.bcfont, boom_users.user_sex, boom_users.user_age, boom_users.user_tumb,
		boom_users.user_cover, boom_users.country, boom_users.user_bot, boom_users.ashare, boom_users.sshare, boom_users.lshare
		FROM boom_chat
		LEFT JOIN boom_users ON boom_users.user_id = boom_chat.user_id
		WHERE boom_chat.post_id = '$id'
	");
	if($get_log->num_rows == 1){
		$log = $get_log->fetch_assoc();
	}
	return $log;
}
function getChatLogs($room, $last){
	global $mysqli, $data;
	$history = 24;
	$add = '';
	if(!isGhosted($data) && !canViewGhost()){
		$add = 'AND pghost = 0';
	}
	$log = $mysqli->query("
	SELECT log.*, 
	boom_users.user_name, boom_users.user_color, boom_users.user_font, boom_users.user_rank, boom_users.user_level, boom_users.bccolor, boom_users.bcbold, boom_users.bcfont, boom_users.user_sex, boom_users.user_age, boom_users.user_tumb,
	boom_users.user_cover, boom_users.country, boom_users.user_bot, boom_users.ashare, boom_users.sshare, boom_users.lshare
	FROM ( SELECT * FROM `boom_chat` WHERE `post_roomid` = '$room' AND post_id > '$last' $add ORDER BY `post_id` DESC LIMIT $history) AS log
	LEFT JOIN boom_users ON log.user_id = boom_users.user_id
	ORDER BY `post_id` ASC
	");
	$logs = $log->fetch_all(MYSQLI_ASSOC);
	return exportLogs($logs);
}
function getChatHistory($room){
	global $mysqli, $data;
	$history = 20;
	$add = '';
	if(!isGhosted($data) && !canViewGhost()){
		$add = 'AND pghost = 0';
	}
	$log = $mysqli->query("
	SELECT log.*,
	boom_users.user_name, boom_users.user_color, boom_users.user_font, boom_users.user_rank, boom_users.user_level, boom_users.bccolor, boom_users.bcbold, boom_users.bcfont, boom_users.user_sex, boom_users.user_age, boom_users.user_tumb,
	boom_users.user_cover, boom_users.country, boom_users.user_bot, boom_users.ashare, boom_users.sshare, boom_users.lshare
	FROM ( SELECT * FROM `boom_chat` WHERE `post_roomid` = '$room' $add ORDER BY `post_id` DESC LIMIT $history) AS log
	LEFT JOIN boom_users ON log.user_id = boom_users.user_id
	ORDER BY `post_id` ASC
	");
	$logs = $log->fetch_all(MYSQLI_ASSOC);
	return exportLogs($logs);
}
function getMoreChatHistory($last){
	global $mysqli, $setting, $data;
	$history = 60;
	$add = '';
	if(!isGhosted($data) && !canViewGhost()){
		$add = 'AND pghost = 0';
	}
	$log = $mysqli->query("
	SELECT log.*, 
	boom_users.user_name, boom_users.user_color, boom_users.user_font, boom_users.user_rank, boom_users.user_level, boom_users.bccolor, boom_users.bcbold, boom_users.bcfont, boom_users.user_sex, boom_users.user_age, boom_users.user_tumb,
	boom_users.user_cover, boom_users.country, boom_users.user_bot, boom_users.ashare, boom_users.sshare, boom_users.lshare
	FROM ( SELECT * FROM `boom_chat` WHERE `post_roomid` = '{$data['user_roomid']}' AND post_id < '$last' AND user_id != '{$setting['system_id']}' $add ORDER BY `post_id` DESC LIMIT $history) AS log
	LEFT JOIN boom_users ON log.user_id = boom_users.user_id
	ORDER BY `post_id` ASC
	");
	$logs = $log->fetch_all(MYSQLI_ASSOC);
	return exportLogs($logs);
}

// private chat

function createPrivateLog($log, $quote = []){
	if($log['qpost'] > 0 && empty($quote)){
		$quote = privateQuoteDetails($log['qpost']);
	}
	if(empty($quote)){
		$quote_data = null;
	}
	else {
		$quote_data = [
			'qpost'=> (int) $quote['id'],
			'qcontent'=> processQuoteMessage($quote['message']),
		];
	}
	return [
		'user_id'=> (int) $log['user_id'],
		'target'=> (int) $log['target'],
		'user_name'=> $log['user_name'],
		'user_tumb'=> avatarFile($log['user_tumb']),
		'user_tcolor'=> myPrivateBubbleColor($log),
		'log_id'=> (int) $log['id'],
		'log_content'=> processPrivateMessage($log),
		'log_date'=> chatDate($log['time']),
		'log_time'=> $log['time'],
		'quote'=> $quote_data,
		'reaction'=> messageReactionData(2, $log['id']),
		'effect'=> messageEffectClass($log['user_id'], $log['time']),
		'view'=> (int) $log['view'],
	];
}
function exportPrivateLogs($logs){
	$result = [];
	foreach($logs as $log){
		$result[] = createPrivateLog($log);
	}
	return $result;
}
function getPrivateLog($id){
	global $mysqli;
	$log = [];
	$get_log = $mysqli->query("
		SELECT boom_private.*, 
		boom_users.user_id, boom_users.user_name, boom_users.user_color, boom_users.bccolor, boom_users.user_tumb, boom_users.user_bot
		FROM boom_private
		LEFT JOIN boom_users ON boom_private.hunter = boom_users.user_id
		WHERE boom_private.id = '$id'
		
	");
	if($get_log->num_rows == 1){
		$log = $get_log->fetch_assoc();
	}
	return $log;
}
function getPrivateLogs($p, $l){
	global $mysqli, $data;
	$log = $mysqli->query("
	SELECT log.*, 
	boom_users.user_id, boom_users.user_name, boom_users.user_color, boom_users.bccolor, boom_users.user_tumb, boom_users.user_bot
	FROM ( SELECT * FROM `boom_private` WHERE  `hunter` = '$p' AND `target` = '{$data['user_id']}' AND id > '$l' OR hunter = '{$data['user_id']}' AND target = '$p' AND id > '$l' ORDER BY `id` DESC LIMIT 10) AS log 
	LEFT JOIN boom_users ON log.hunter = boom_users.user_id ORDER BY `id` ASC
	");
	if($log->num_rows > 0){
		readConv($p, $data['user_id']);
	}
	$logs = $log->fetch_all(MYSQLI_ASSOC);
	return exportPrivateLogs($logs);
}
function getPrivateHistory($p){
	global $mysqli, $data;
	$log = $mysqli->query("
	SELECT log.*, 	
	boom_users.user_id, boom_users.user_name, boom_users.user_color, boom_users.bccolor, boom_users.user_tumb, boom_users.user_bot
	FROM ( SELECT * FROM `boom_private` WHERE  `hunter` = '{$data['user_id']}' AND `target` = '$p'  OR `hunter` = '$p' AND `target` = '{$data['user_id']}' ORDER BY `id` DESC LIMIT 14) AS log 
	LEFT JOIN boom_users ON log.hunter = boom_users.user_id ORDER BY `id` ASC
	");
	$logs = $log->fetch_all(MYSQLI_ASSOC);
	readConv($p, $data['user_id']);
	return exportPrivateLogs($logs);
}
function getMorePrivateHistory($p, $l){
	global $mysqli, $data;
	$log = $mysqli->query("
	SELECT log.*, 	
	boom_users.user_id, boom_users.user_name, boom_users.user_color, boom_users.bccolor, boom_users.user_tumb, boom_users.user_bot
	FROM ( SELECT * FROM `boom_private` WHERE  `hunter` = '{$data['user_id']}' AND `target` = '$p' AND id < '$l' OR `hunter` = '$p' AND `target` = '{$data['user_id']}'  AND id < '$l' ORDER BY `id` DESC LIMIT 30) AS log 
	LEFT JOIN boom_users ON log.hunter = boom_users.user_id ORDER BY `id` ASC
	");
	$logs = $log->fetch_all(MYSQLI_ASSOC);
	return exportPrivateLogs($logs);
}
function countryFlag($country){
	switch($country){
		case '':
		case 'ZZ':
			return '';
		default:
			return 'system/location/flag/' . $country . '.png';
	}
}
function checkRateLimit(){
	global $setting;
	if($setting['use_rate'] == 1){
		if(isset($_SESSION[BOOM_PREFIX . 'fignore']) && $_SESSION[BOOM_PREFIX . 'fignore'] > time()){
			return true;
		}
		if(!isset($_SESSION[BOOM_PREFIX .'ftime']) || $_SESSION[BOOM_PREFIX . 'ftime'] < (time() - 20)){
			$_SESSION[BOOM_PREFIX . 'ftime'] = time();
			$_SESSION[BOOM_PREFIX . 'fcount'] = 1;
			return false;
		}
		else if($_SESSION[BOOM_PREFIX . 'fcount'] >= $setting['rate_limit']){
			$_SESSION[BOOM_PREFIX . 'fignore'] = (time() + 300);
			return true;
		}
		$_SESSION[BOOM_PREFIX . 'fcount']++;
	}
}

function userProfileMusic($user){
        if(empty($user['user_pmusic'])){
                return '';
        }
        return BOOM_DOMAIN . 'music/' . $user['user_pmusic'];
}
function autoProfileMusic(){
        global $data;
        if($data['pmusic'] > 0){
                return true;
        }
}
function playIcon(){
        return 'default_images/icons/play.svg';
}
function pauseIcon(){
        return 'default_images/icons/pause.svg';
}
?>
