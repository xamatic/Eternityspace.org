<?php
$load_addons = 'paintit';
require('../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}

?>
<style>
</style>
<?php echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div>
		<div class="tab_menu">
			<ul>
				<li class="tab_menu_item tab_selected" data="paint" data-z="paint_setting"><?php echo $lang['settings']; ?></li>
			</ul>
		</div>
	</div>
	<div class="page_element">
		<div class="tpad15">
			<div id="paint">
				<div id="paint_setting" class="tab_zone">
					<div class="setting_element ">
						<p class="label"><?php echo $lang['limit_feature']; ?></p>
						<select id="set_paint_access">
							<?php echo listRank($addons['addons_access']); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['paint_main']; ?></p>
						<select id="set_paint_main">
							<?php echo onOff($addons['custom1']); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['paint_private']; ?></p>
						<select id="set_paint_private">
							<?php echo onOff($addons['custom2']); ?>
						</select>
					</div>
					<button onclick="savePaintIt();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
				</div>
			</div>
		</div>
		<div class="config_section">
			<script data-cfasync="false" type="text/javascript">
				savePaintIt = function(){
					$.post('addons/paintit/system/action.php', {
						set_paint_access: $('#set_paint_access').val(),
						set_paint_main: $('#set_paint_main').val(),
						set_paint_private: $('#set_paint_private').val(),
						}, function(response) {
							if(response == 5){
								callSuccess(system.saved);
							}
							else{
								callError(system.error);
							}
					});	
				}
			</script>
		</div>
	</div>
</div>
