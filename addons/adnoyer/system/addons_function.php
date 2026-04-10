<?php
function adnoyerData(){
	global $mysqli, $data, $lang;
	$list = '';
	$get_data = $mysqli->query("SELECT * FROM boom_adnoyer WHERE adnoyer_id > 0 LIMIT 500");
	if($get_data->num_rows > 0){
		while($adnoyer = $get_data->fetch_assoc()){
			$list .= boomAddonsTemplate('../addons/adnoyer/system/template/adnoyer_data', $adnoyer);
		}
	}
	return $list;
}
function adnoyerEscape($t){
	global $mysqli;
	$atags = '<a><p><h1><h2><h3><h4><ul><li><b><strong><br><i><span><u><strike><small><font><center><blink><img><iframe><del><hr><sub><ol><blockquote>';
	$t = strip_tags($t, $atags);
	$t = addslashes($t);
	return $mysqli->real_escape_string(trim($t));
}
?>