<?php
$load_addons = 'vip_gold';
require_once('../../../system/config_addons.php');

if(!boomAllow(100)){
	die();
}
?>
<?php echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div>
		<div class="tab_menu">
			<ul>
				<li class="tab_menu_item tab_selected" data="vip" data-z="vip_setting"><?php echo $lang['settings']; ?></li>
				<li class="tab_menu_item" data="vip" data-z="vip_transaction" onclick="vipLoadTransaction();"><?php echo $lang['vip_transaction']; ?></li>
			</ul>
		</div>
	</div>
	<div id="vip">
		<div id="vip_setting" class="tab_zone">
			<div class="page_element">
				<div class="form_content">
					<div class="setting_element ">
						<p class="label"><?php echo $lang['vip_mode_status']; ?></p>
						<select id="set_vip_status">
							<?php echo onOff($addons['custom6']); ?>
						</select>
					</div>
					<div class="setting_element">
						<p class="label"><?php echo $lang['vset1']; ?></p>
						<input id="set_plan1" class="full_input" value="<?php echo $addons['custom1']; ?>" type="text"/>
					</div>
					<div class="setting_element">
						<p class="label"><?php echo $lang['vset2']; ?></p>
						<input id="set_plan2" class="full_input" value="<?php echo $addons['custom2']; ?>" type="text"/>
					</div>
					<div class="setting_element">
						<p class="label"><?php echo $lang['vset3']; ?></p>
						<input id="set_plan3" class="full_input" value="<?php echo $addons['custom3']; ?>" type="text"/>
					</div>
					<div class="setting_element">
						<p class="label"><?php echo $lang['vset4']; ?></p>
						<input id="set_plan4" class="full_input" value="<?php echo $addons['custom4']; ?>" type="text"/>
					</div>
					<div class="setting_element">
						<p class="label"><?php echo $lang['vset5']; ?></p>
						<input id="set_plan5" class="full_input" value="<?php echo $addons['custom5']; ?>" type="text"/>
					</div>
				</div>
				<div class="form_control">
					<button onclick="saveVipGold();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
				</div>
			</div>
		</div>
		<div id="vip_transaction" class="tab_zone hide_zone">
			<div class="page_element">
				<div class="vpad10">
					<button onclick="vipAddBox();" class="reg_button theme_btn"><i class="fa fa-plus-circle"></i> <?php echo $lang['vip_add']; ?></button>
				</div>
				<div id="vip_search_transaction">
					<div class="admin_search">
						<div class="admin_input bcell">
							<input class="full_input" placeholder="<?php echo $lang['search']; ?>" id="vip_find" type="text"/>
						</div>
						<div onclick="searchVip();" class="admin_search_btn default_btn">
							<i class="fa fa-search" aria-hidden="true"></i>
						</div>
					</div>
				</div>
			</div>
			<div class="page_element">
				<div id="vip_transaction_list">
				</div>
			</div>
		</div>
	</div>
	<div class="config_section">
		<script data-cfasync="false">
			searchVip = function(){
				$.post('addons/vip_gold/system/action.php', {
					search_vip: $('#vip_find').val(),
					}, function(response) {
						$('#vip_transaction_list').html(response);
				});	
			}
			vipAddBox = function(){
				$.post('addons/vip_gold/system/box/vip_add.php', {
					}, function(response) {
						showModal(response);
				});	
			}
			vipLoadTransaction = function(){
				$.post('addons/vip_gold/system/action.php', {
					load_transaction: 1,
					}, function(response) {
						$('#vip_find').val('');
						$('#vip_transaction_list').html(response);
				});
			}
			saveVipGold = function(){
				$.post('addons/vip_gold/system/action.php', {
					plan1: $('#set_plan1').val(),
					plan2: $('#set_plan2').val(),
					plan3: $('#set_plan3').val(),
					plan4: $('#set_plan4').val(),
					plan5: $('#set_plan5').val(),
					status: $('#set_vip_status').val(),
					}, function(response) {
						if(response == 1){
							callSaved(system.saved, 1);
						}
						else {
							callSaved(system.error, 3);
						}
				});	
			}
			vipDetails = function(id){
				$.post('addons/vip_gold/system/box/vip_details.php', {
					tdetails: id,
					}, function(response) {
						if(response == 0){
							callSaved(system.error, 3);
						}
						else {
							showModal(response, 500);
						}
				});	
			}
			vipWait = 0;
			addVipData = function(){
				if(vipWait == 0){
					vipWait = 1;
					$.post('addons/vip_gold/system/action.php', {
						vip_add: 1,
						vip_user: $('#set_vip_name').val(),
						vip_plan: $('#set_vip_plan').val(),
						}, function(response) {
							vipWait = 0;
							if(response == 2){
								callSaved(system.noUser, 3);
							}
							else if(response == 3){
								callSaved(system.cannotUser, 3);
							}
							else if(response.indexOf("pvip") >= 1){
								hideModal();
								callSaved(system.actionComplete, 1);
								vipLoadTransaction();
							}
							else {
								callSaved(system.error, 3);
							}
					});	
				}
			}
		</script>
	</div>
</div>