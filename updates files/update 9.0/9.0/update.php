<?php
// boom_call

@$mysqli->query("ALTER TABLE `boom_call` DROP COLUMN call_token");
@$mysqli->query("ALTER TABLE `boom_call` DROP COLUMN call_token2");

// boom_chat

@$mysqli->query("ALTER TABLE `boom_chat` DROP COLUMN snum");
@$mysqli->query("ALTER TABLE `boom_chat` DROP COLUMN tcolor");
$mysqli->query("ALTER TABLE `boom_chat` ADD tid int(11) NOT NULL DEFAULT '0'");
$mysqli->query("ALTER TABLE `boom_chat` ADD tname varchar(60) NOT NULL DEFAULT ''");
$mysqli->query("ALTER TABLE `boom_chat` ADD custom varchar(2000) NOT NULL DEFAULT ''");

// boom_clean

@$mysqli->query("ALTER TABLE `boom_clean` DROP COLUMN last_vip");

// boom_exp 

@$mysqli->query("ALTER TABLE `boom_exp` DROP COLUMN exp_chat");
@$mysqli->query("ALTER TABLE `boom_exp` DROP COLUMN exp_priv");
@$mysqli->query("ALTER TABLE `boom_exp` DROP COLUMN exp_gift");
@$mysqli->query("ALTER TABLE `boom_exp` DROP COLUMN exp_post");

// boom_news 

@$mysqli->query("ALTER TABLE `boom_news` DROP COLUMN news_poll");

// boom_setting

$mysqli->query("ALTER TABLE `boom_setting` ADD can_agcall int(3) NOT NULL DEFAULT '100'");
$mysqli->query("ALTER TABLE `boom_setting` ADD can_vgcall int(3) NOT NULL DEFAULT '100'");
$mysqli->query("ALTER TABLE boom_setting MODIFY openai_key varchar(200) NOT NULL DEFAULT ''");
$mysqli->query("ALTER TABLE boom_setting MODIFY mod_cat varchar(200) NOT NULL DEFAULT ''");
$mysqli->query("ALTER TABLE boom_setting MODIFY level_mode int(4) NOT NULL DEFAULT '10'");
@$mysqli->query("ALTER TABLE `boom_setting` DROP COLUMN max_ugcall");
@$mysqli->query("ALTER TABLE `boom_setting` DROP COLUMN privload");
@$mysqli->query("ALTER TABLE `boom_setting` DROP COLUMN can_cgcall");

// boom_warn

$mysqli->query("DROP TABLE IF EXISTS boom_warn");

// boom_users

$mysqli->query("ALTER TABLE `boom_users` ADD user_wall int(11) NOT NULL DEFAULT '0'");
$mysqli->query("ALTER TABLE `boom_users` ADD user_bubble int(1) NOT NULL DEFAULT '1'");
$mysqli->query("UPDATE boom_users SET user_theme = 'system', bccolor = '' WHERE user_id > 0");
$mysqli->query("ALTER TABLE boom_users MODIFY warn_msg varchar(500) NOT NULL DEFAULT ''");
@$mysqli->query("ALTER TABLE `boom_users` DROP COLUMN pstyle");
@$mysqli->query("ALTER TABLE `boom_users` DROP COLUMN exp_post");
@$mysqli->query("ALTER TABLE `boom_users` DROP COLUMN join_msg");

$mysqli->query("UPDATE boom_setting SET version = '9.0' WHERE id > 0");

redisFlushAll();
boomCacheUpdate();
boomSaveSettings();
?>