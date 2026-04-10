<?php
require('../config_session.php');

$anim = userAnimationConfig($data);
?>
<div class="modal_title">
	Animation Settings
</div>
<div class="modal_content">
	<div class="setting_element">
		<p class="label">Enable chat animations</p>
		<select id="anim_master">
			<?php echo onOff($anim['master']); ?>
		</select>
	</div>
	<div class="setting_element">
		<p class="label">Chat bubble effects</p>
		<select id="anim_chatfx">
			<?php echo onOff($anim['chatfx']); ?>
		</select>
	</div>
	<div class="setting_element">
		<p class="label">Message pop effects</p>
		<select id="anim_goofy">
			<?php echo onOff($anim['goofy']); ?>
		</select>
	</div>
	<div class="setting_element">
		<p class="label">Interface animations</p>
		<select id="anim_overlay">
			<?php echo onOff($anim['overlay']); ?>
		</select>
	</div>
	<p class="text_small sub_text tpad10">When disabled, normal chat animations are reduced or skipped for your account.</p>
</div>
<div class="modal_control">
	<button onclick="saveAnimationSettings();" class="reg_button theme_btn"><i class="fa fa-save"></i> <?php echo $lang['save']; ?></button>
</div>