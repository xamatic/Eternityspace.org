<?php
$load_addons = 'adnoyer';
require('../../../../system/config_addons.php');

if(!canManageAddons()){
	echo 0;
	die();
}
if(!isset($_POST['edit_adnoyer'])){
	echo 0;
	die();
}
$id = escape($_POST['edit_adnoyer'], true);
$get_adnoyer = $mysqli->query("SELECT * FROM boom_adnoyer WHERE adnoyer_id = '$id'");
if($get_adnoyer->num_rows > 0){
	$adnoyer = $get_adnoyer->fetch_assoc();
}
else {
	echo 0;
	die();
}
?>
<div class="modal_content">
	<div class="setting_element ">
		<p class="label"><?php echo $lang['adnoyer_title']; ?></p>
		<input id="adnoyer_title" class="full_input" type="text" value="<?php echo $adnoyer['adnoyer_title']; ?>"/>
	</div>
	<div class="setting_element ">
		<p class="label"><?php echo $lang['adnoyer_content']; ?></p>
		<textarea id="adnoyer_content" class="full_textarea large_textarea" type="text"><?php echo stripslashes($adnoyer['adnoyer_content']); ?></textarea>
	</div>
</div>
<div class="modal_control">
	<button id="add_adnoyer" onclick="changeAdnoyer(<?php echo $adnoyer['adnoyer_id']; ?>);" type="button" class="reg_button theme_btn"><i class="fa fa-save"></i> <?php echo $lang['save']; ?></button>
</div>