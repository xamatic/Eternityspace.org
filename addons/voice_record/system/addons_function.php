<?php
function mainVoiceRecord(){
	global $addons;
	if(boomAllow($addons['addons_access']) && $addons['custom2'] == 1){
		return true;
	}
}
function privateVoiceRecord(){
	global $addons;
	if(boomAllow($addons['addons_access']) && $addons['custom3'] == 1){
		return true;
	}
}
?>