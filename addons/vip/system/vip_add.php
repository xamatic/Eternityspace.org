<?php
$load_addons = 'vip';
require_once('../../../system/config_addons.php');
if(!canManageAddons()){
	die();
}
?>
<div class="pad15">
	<div class="setting_element">
		<p class="label"><?php echo $lang['username']; ?></p>
		<input id="set_vip_name" class="full_input" type="text"/>
	</div>
	<div class="setting_element">
		<p class="label"><?php echo $lang['vip_plan']; ?></p>
		<select id="set_vip_plan">
			<option  value="1"><?php echo $lang['vip_plan_name1']; ?></option>
			<option  value="2"><?php echo $lang['vip_plan_name2']; ?></option>
			<option  value="3"><?php echo $lang['vip_plan_name3']; ?></option>
			<option  value="4"><?php echo $lang['vip_plan_name4']; ?></option>
			<option  value="5"><?php echo $lang['vip_plan_name5']; ?></option>
			<option  value="6"><?php echo $lang['vip_plan_name6']; ?></option>
			<option  value="7"><?php echo $lang['vip_plan_name7']; ?></option>
			<option  value="8"><?php echo $lang['vip_plan_name8']; ?></option>
			<option  value="9"><?php echo $lang['vip_plan_name9']; ?></option>
		</select>
	</div>
	<div class="tpad10">
		<button onclick="addVipData();" class="reg_button default_btn"><?php echo $lang['vip_add']; ?></button>
	</div>
</div>