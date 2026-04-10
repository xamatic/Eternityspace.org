<?php
$load_addons = 'voice_record';
require('../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}

?>
<?php echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div class="page_element">
		<div class="config_section">
			<div class="setting_element ">
				<p class="label"><?php echo $lang['voice_access']; ?></p>
					<select id="set_voice_access">
						<?php echo listRank($addons['addons_access']); ?>
					</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['voice_main']; ?></p>
				<select id="set_voice_main">
					<?php echo onOff($addons['custom2']); ?>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['voice_main_time']; ?></p>
				<select id="set_voice_main_time">
					<?php echo optionCount($addons['custom4'], 5, 120, 5); ?>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['voice_private']; ?></p>
				<select id="set_voice_private">
					<?php echo onOff($addons['custom3']); ?>
				</select>
			</div>
			<div class="setting_element ">
				<p class="label"><?php echo $lang['voice_private_time']; ?></p>
				<select id="set_voice_private_time">
					<?php echo optionCount($addons['custom5'], 5, 120, 5); ?>
				</select>
			</div>
			<button id="save_voice_recorder" onclick="saveVoiceRecord();" type="button" class="tmargin10 reg_button theme_btn"><?php echo $lang['save']; ?></button>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
				saveVoiceRecord = function(){
					$.post('addons/voice_record/system/action.php', {
						save: 1,
						set_voice_access: $('#set_voice_access').val(),
						set_voice_main: $('#set_voice_main').val(),
						set_voice_main_time: $('#set_voice_main_time').val(),
						set_voice_private: $('#set_voice_private').val(),
						set_voice_private_time: $('#set_voice_private_time').val(),
						}, function(response) {
					}, 'json');	
				}
			</script>
		</div>
	</div>
</div>
