<?php
if(!defined('BOOM')){
	die();
}
$ad = array(
	'name' => 'superbot',
	'bot_name'=> 'Superbot',
	'bot_type'=> 1,
	);

$mysqli->query("CREATE TABLE IF NOT EXISTS `superbot_data` (
				`id` int(10) NOT NULL AUTO_INCREMENT,
				`superbot_question` varchar(2000) NOT NULL DEFAULT '',
				`superbot_answer` varchar(2000) NOT NULL DEFAULT '',
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1");
?>