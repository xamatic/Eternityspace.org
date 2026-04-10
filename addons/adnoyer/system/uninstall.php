<?php
if(!defined('BOOM')){
	die();
}
$mysqli->query("ALTER TABLE `boom_rooms` DROP `adnoyer_time`");
?>