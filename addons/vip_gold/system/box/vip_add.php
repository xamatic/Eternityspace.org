<?php
$load_addons = 'vip_gold';
require_once('../../../../system/config_addons.php');
if(!canManageAddons()){
	die();
}
?>
<div class="modal_content">
	<div class="setting_element">
		<p class="label"><?php echo $lang['username']; ?></p>
		<input id="set_vip_name" class="full_input" type="text"/>
	</div>
	<div class="setting_element">
		<p class="label"><?php echo $lang['vip_plan']; ?></p>
		<select id="set_vip_plan">
			<option  value="1"><?php echo $lang['vplan1']; ?></option>
			<option  value="2"><?php echo $lang['vplan2']; ?></option>
			<option  value="3"><?php echo $lang['vplan3']; ?></option>
			<option  value="4"><?php echo $lang['vplan4']; ?></option>
			<option  value="5"><?php echo $lang['vplan5']; ?></option>
		</select>
	</div>
</div>
<div class="modal_control">
	<button onclick="addVipData();" class="reg_button default_btn"><i class="fa fa-plus"></i> <?php echo $lang['vip_add']; ?></button>
</div>