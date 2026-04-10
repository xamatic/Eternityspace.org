<?php
require('../config_session.php');

if(!isset($_POST['buy_effect']) && !isset($_POST['set_effect']) && !isset($_POST['buy_profile_effect']) && !isset($_POST['set_profile_effect'])){
	echo boomCode(0);
	die();
}

if(isset($_POST['buy_effect']) || isset($_POST['set_effect'])){
	try {
		ensureChatEffectTables();
	}
	catch(Throwable $e){
		echo boomCode(0);
		die();
	}
}
if(isset($_POST['buy_profile_effect']) || isset($_POST['set_profile_effect'])){
	try {
		ensureProfileEffectTables();
	}
	catch(Throwable $e){
		echo boomCode(0);
		die();
	}
}

if(isset($_POST['buy_effect'])){
	if(!useWallet()){
		echo boomCode(0);
		die();
	}
	$effect = (int) escape($_POST['buy_effect'], true);
	if(!validChatEffect($effect)){
		echo boomCode(0);
		die();
	}
	$owned = userChatEffectOwned($data['user_id']);
	if(isset($owned[$effect])){
		echo boomCode(3);
		die();
	}
	$effects = chatEffectList();
	$price = (int) $effects[$effect]['price'];
	if($price < 1 || $price > 5000){
		echo boomCode(0);
		die();
	}
	if(!walletBalance(1, $price)){
		echo boomCode(2);
		die();
	}
	try {
		removeWallet($data, 1, $price);
		$mysqli->query("INSERT IGNORE INTO boom_chat_effect (user_id, effect_id, effect_time) VALUES ('{$data['user_id']}', '$effect', '" . time() . "')");
		setUserChatEffect($data['user_id'], $effect);
	}
	catch(Throwable $e){
		echo boomCode(0);
		die();
	}
	echo boomCode(1, [
		'effect' => (int) $effect,
		'gold' => max(0, ((int) $data['user_gold'] - $price)),
	]);
	die();
}

if(isset($_POST['set_effect'])){
	$effect = (int) escape($_POST['set_effect'], true);
	if($effect === 0){
		setUserChatEffect($data['user_id'], 0);
		echo boomCode(1, ['effect' => 0]);
		die();
	}
	if(!validChatEffect($effect)){
		echo boomCode(0);
		die();
	}
	$owned = userChatEffectOwned($data['user_id']);
	if(!isset($owned[$effect])){
		echo boomCode(3);
		die();
	}
	setUserChatEffect($data['user_id'], $effect);
	echo boomCode(1, ['effect' => (int) $effect]);
	die();
}

if(isset($_POST['buy_profile_effect'])){
	if(!useWallet()){
		echo boomCode(0);
		die();
	}
	$category = isset($_POST['effect_category']) ? escape($_POST['effect_category']) : '';
	$effect = (int) escape($_POST['effect_id'], true);
	if(!validProfileEffectCategory($category) || !validProfileEffect($category, $effect)){
		echo boomCode(0);
		die();
	}
	$owned = userProfileEffectOwned($data['user_id'], $category);
	if(isset($owned[$effect])){
		echo boomCode(3);
		die();
	}
	$price = profileEffectPrice($category, $effect);
	if($price < 1 || $price > 5000){
		echo boomCode(0);
		die();
	}
	if(!walletBalance(1, $price)){
		echo boomCode(2);
		die();
	}
	try {
		removeWallet($data, 1, $price);
		$cat = escape($category);
		$mysqli->query("INSERT IGNORE INTO boom_profile_effect (user_id, effect_category, effect_id, effect_time) VALUES ('{$data['user_id']}', '$cat', '$effect', '" . time() . "')");
		setUserProfileEffect($data['user_id'], $category, $effect);
	}
	catch(Throwable $e){
		echo boomCode(0);
		die();
	}
	echo boomCode(1, [
		'category' => $category,
		'effect' => (int) $effect,
		'gold' => max(0, ((int) $data['user_gold'] - $price)),
	]);
	die();
}

if(isset($_POST['set_profile_effect'])){
	$category = isset($_POST['effect_category']) ? escape($_POST['effect_category']) : '';
	$effect = (int) escape($_POST['effect_id'], true);
	if(!validProfileEffectCategory($category)){
		echo boomCode(0);
		die();
	}
	if($effect === 0){
		setUserProfileEffect($data['user_id'], $category, 0);
		echo boomCode(1, ['category' => $category, 'effect' => 0]);
		die();
	}
	if(!validProfileEffect($category, $effect)){
		echo boomCode(0);
		die();
	}
	$owned = userProfileEffectOwned($data['user_id'], $category);
	if(!isset($owned[$effect])){
		echo boomCode(3);
		die();
	}
	setUserProfileEffect($data['user_id'], $category, $effect);
	echo boomCode(1, ['category' => $category, 'effect' => (int) $effect]);
	die();
}

echo boomCode(0);
die();
?>