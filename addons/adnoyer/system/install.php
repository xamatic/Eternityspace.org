<?php
if(!defined('BOOM')){
	die();
}
$ad = array(
	'name' => 'adnoyer',
	'bot_name'=> 'Adnoyer',
	'bot_type'=> 2,
	'custom1'=> 0,
	'custom2'=> 300,
	'custom3'=> 99,
	'custom4'=> 3,
	'custom5'=> 1,
);

$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_adnoyer` (
				`adnoyer_id` int(10) NOT NULL AUTO_INCREMENT,
				`adnoyer_title` varchar(200) NOT NULL DEFAULT '',
				`adnoyer_content` varchar(2000) NOT NULL DEFAULT '',
				`adnoyer_type` varchar(30) NOT NULL DEFAULT 'adnoyer_log',
				`adnoyer_date` int(11) NOT NULL DEFAULT '0',
				PRIMARY KEY (`adnoyer_id`)
				) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1");

$mysqli->query("ALTER TABLE `boom_rooms` ADD adnoyer_time int(11) NOT NULL DEFAULT '0'");
?>