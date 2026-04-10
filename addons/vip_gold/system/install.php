<?php
if(!defined('BOOM')){
	die();
}
function vipInstallation(){
	global $mysqli, $data;
	$mysqli->query("CREATE TABLE IF NOT EXISTS `boom_vip` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`userid` int(11) NOT NULL DEFAULT '11',
		`userp` varchar(50) NOT NULL DEFAULT '',
		`plan` varchar(20) NOT NULL DEFAULT '',
		`price` varchar(20) NOT NULL DEFAULT '',
		`vdate` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `userid` (`userid`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci AUTO_INCREMENT=1");
}
$ad = array(
	'name' => 'vip_gold',
	'access'=> 0,
	'max'=> 1,
	'custom1'=> '100000',
	'custom2'=> '200000',
	'custom3'=> '500000',
	'custom4'=> '750000',
	'custom5'=> '1000000',
	'custom6'=> 0,
	);
	
$install_vip = vipInstallation();	

?>