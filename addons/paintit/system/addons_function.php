<?php
function paintSize($img){
	global $setting;
    try{
        $size = ((strlen($img) * 3 / 4) / 1024 ) / 1024;
		if($size > $setting['file_weight']){
			return false;
		}
		else {
			return true;
		}
    }
    catch(Exception $e){
        return false;
    }
}
?>