<?php
$load_addons = 'adnoyer';
require('../../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}
?>
<div class="modal_content">
	<div class="setting_element ">
		<p class="label"><?php echo $lang['adnoyer_title']; ?></p>
		<input id="adnoyer_title" class="full_input" type="text"/>
	</div>
	<div class="setting_element ">
		<p class="label"><?php echo $lang['adnoyer_content']; ?></p>
		<textarea id="adnoyer_content" class="full_textarea large_textarea" type="text"></textarea>
	</div>
</div>
<div class="modal_control">
	<button id="add_adnoyer" onclick="addAdnoyerData();" type="button" class="reg_button theme_btn"><?php echo $lang['add']; ?></button>
</div>