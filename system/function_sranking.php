<?php
// main system ranking functions

function rankLang($key, $fallback){
	global $lang;
	if(isset($lang[$key]) && $lang[$key] !== ''){
		return $lang[$key];
	}
	return $fallback;
}

function botRank(){
	return 69;
}
function rankList(){
	return array(0,1,50,69,70,71,80,90,99,100,999);
}
function rankIcon($rank){
	switch($rank){
		case 0:
			return 'guest.svg';
		case 1:
			return 'user.svg';
		case 50:
			return 'vip.svg';
		case 69:
			return 'bot.svg';
		case 70:
		    return 'moderator.png';
		case 71:
			return 'mod.svg';
		case 80:
			return 'admin.svg';
		case 90:
			return 'super.svg';
		case 99:
		    return 'coowner.png';
		case 100:
			return 'owner.gif';
		default:
			return 'user.svg';
	}
}
function rankTitle($rank){
	switch($rank){
		case 0:
			return rankLang('guest', 'Guest');
		case 1:
			return rankLang('user', 'User');
		case 50:
			return rankLang('vip', 'VIP');
		case 69:
			return rankLang('user_bot', 'Bot');
		case 70:
		    return rankLang('jr_mod', 'Mod in Training');
		case 71:
			return rankLang('mod', 'Moderator');
		case 80:
			return rankLang('admin', 'Admin');
		case 90:
			return rankLang('super_admin', 'Super Admin');
		case 99:
		    return rankLang('co_owner', 'Co Owner');
		case 100:
			return rankLang('owner', 'Owner');
		case 999:
			return rankLang('nobody', 'Nobody');
		default:
			return rankLang('user', 'User');
	}
}

// room system ranking functions

function roomRankList(){
	return array(0,4,5,6,9);
}

function roomRankTitle($rank){
	switch($rank){
		case 0:
			return rankLang('user', 'User');
		case 4:
			return rankLang('r_mod', 'Room Mod');
		case 5:
			return rankLang('r_admin', 'Room Admin');
		case 6:
			return rankLang('r_owner', 'Room Owner');
		case 9:
			return rankLang('nobody', 'Nobody');
		default:
			return rankLang('user', 'User');
	}
}
function roomRankIcon($rank){
	switch($rank){
		case 4:
			return 'room_mod.svg';
		case 5:
			return 'room_admin.svg';
		case 6:
			return 'room_owner.svg';
		default:
			return 'user.svg';
	}
}

// status system functions 

function statusList(){
	return array(1,2,3,99);
}
function statusTitle($status){
	switch($status){
		case 1:  
			return rankLang('online', 'Online');
		case 2:  
			return rankLang('away', 'Away');
		case 3:  
			return rankLang('busy', 'Busy');
		case 99:  
			return rankLang('invisible', 'Invisible');
		default: 
			return rankLang('online', 'Online');
	}
}
function statusIcon($status){
	switch($status){
		case 1:
			return 'online.svg';
		case 2:
			return 'away.svg';
		case 3:
			return 'busy.svg';
		case 99:
			return 'invisible.svg';
		default:
			return 'online.svg';
	}	
}
?>