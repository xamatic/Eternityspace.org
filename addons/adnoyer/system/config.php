<?php
$load_addons = 'adnoyer';
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
				<li class="tab_menu_item tab_selected" data="adnoy" data-z="adnoyer_setting"><?php echo $lang['settings']; ?></li>
				<li class="tab_menu_item" data="adnoy" data-z="adnoyer_add"><?php echo $lang['data']; ?></li>
				<li class="tab_menu_item" data="adnoy" data-z="adnoyer_help"><?php echo $lang['help']; ?></li>
			</ul>
		</div>
	</div>
	<div class="page_element">
		<div class="tpad15">
			<div id="adnoy">
				<div id="adnoyer_add" class="tab_zone hide_zone">
					<div class="bmargin15">
						<button onclick="openAdnoyer();" class="reg_button theme_btn">+ <?php echo $lang['add']; ?></button>
					</div>
					<div id="adnoyer_list" class="tmargin15">
						<?php echo adnoyerData(); ?>
					</div>
				</div>
				<div id="adnoyer_help" class="hide_zone tab_zone no_rtl">
					<div class="docu_box">
						<div class="docu_head bback">
							Starting adnoyer
						</div>
						<div class="docu_content">
							<div class="docu_description sub_text">
								<p>
								As adnoyer is a cron addons it would require you to start a cron job on your server side to make it talk.
								Bellow is a example of cron job command based on a cpanel interface. Note that those command may differ from
								panel to panel and would require you to adjust them according to your server type and panel type.
								</p>
								<br>
								<p>php -q /home/something_here/public_html/user_here/addons/adnoyer/system/cron/adnoyer.php</p>
							</div>
						</div>
					</div>
					<div class="docu_box">
						<div class="docu_head bback">
							Adding to the bot
						</div>
						<div class="docu_content">
							<div class="docu_description sub_text">
							<p>
							You can add many things to Adnoyer bot and with a little knowledge of html you can do some magic with it. Adnoyer support many
							html tag that allow you to customise your messages you can see bellow a list of tag that are currently supported by adnoyer.
							</p>
							<div class="vpad10 bold">
								<p><?php echo htmlspecialchars('<a><p><h1><h2><h3><h4><ul><li><b><strong><br><i><span><u><strike><small>'); ?></p>
								<p><?php echo htmlspecialchars('<font><center><blink><img><iframe><del><hr><sub><ol><blockquote>'); ?><p>
							</div>
							</div>
						</div>
					</div>
					<div class="docu_box">
						<div class="docu_head bback">
							How it work
						</div>
						<div class="docu_content">
							<div class="docu_description sub_text">
								<p>
								Adnoyer is a very powerfull auto post bot that allow you to send random message to all rooms. Adnoyer base his work on 
								some predefined algorithm that make him send message to rooms if they meet requirements. Example in the setting you can choose
								how many people must be present in the room and how many line people have to type between the bot messages. Those settings greatly
								help of keeping the chat clean and prevent the bot of flooding rooms where there is nobody talking or empty room.
								Adnoyer auto delete his post constantly to keep your database as low as possible and prevent slow down in your system.
								</p>		
							</div>
						</div>
					</div>
				</div>
				<div id="adnoyer_setting" class="tab_zone">
					<div class="setting_element ">
						<p class="label"><?php echo $lang['status']; ?></p>
						<select id="set_adnoyer_status">
							<?php echo onOff($addons['custom1']); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['adnoyer_delay']; ?></p>
						<select id="set_adnoyer_delay">
							<?php echo optionSeconds($addons['custom2'], array(15,20,30,45,60,120,180,240,300,600,1200,1800,3600)); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['adnoyer_mlogs']; ?></p>
						<select id="set_adnoyer_mlogs">
							<?php echo optionCount($addons['custom4'], 3, 15, 1); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['adnoyer_users']; ?></p>
						<select id="set_adnoyer_users">
							<?php echo optionCount($addons['custom5'], 1, 10, 1); ?>
						</select>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['adnoyer_hide']; ?></p>
						<select id="set_adnoyer_hide">
							<?php echo listRank($addons['custom3']); ?>
						</select>
					</div>
					<button id="save_adnoyer" onclick="saveAdnoyer();" type="button" class="tmargin10 reg_button theme_btn"><?php echo $lang['save']; ?></button>
				</div>
			</div>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
				saveAdnoyer = function(){
					$.post('addons/adnoyer/system/action.php', {
						adnoyer_status: $('#set_adnoyer_status').val(),
						adnoyer_delay: $('#set_adnoyer_delay').val(),
						adnoyer_hide: $('#set_adnoyer_hide').val(),
						adnoyer_users: $('#set_adnoyer_users').val(),
						adnoyer_mlogs: $('#set_adnoyer_mlogs').val(),
						}, function(response) {
							if(response == 5){
								callSuccess(system.saved);
							}
							else{
								callError(system.error);
							}
					});	
				}
				openAdnoyer = function(){
					$.post('addons/adnoyer/system/box/add_adnoyer.php', {
						}, function(response) {
							if(response == 0){
								return false;
							}
							else{
								showModal(response, 500);
							}
					});	
				}
				deleteAdnoyer = function(id){
					$.post('addons/adnoyer/system/action.php', {
						delete_adnoyer:id,
						}, function(response) {
							if(response == 1){
								$('.adnoyer'+id).replaceWith("");
							}
							else{
								callError(system.error);
							}
					});	
				}
				editAdnoyer = function(id){
					$.post('addons/adnoyer/system/box/edit_adnoyer.php', {
						edit_adnoyer: id,
						}, function(response) {
							if(response == 0){
								callError(system.error);
							}
							else{
								showModal(response, 500);
							}
					});	
				}
				var waitAdnoyer = 0;
				addAdnoyerData = function(){
					if(waitAdnoyer == 0){
						waitAdnoyer = 1;
						$.ajax({
							url: "addons/adnoyer/system/action.php",
							type: "post",
							cache: false,
							dataType: 'json',
							data: { 
								adnoyer_new: 1,
								adnoyer_title: $('#adnoyer_title').val(),
								adnoyer_content: $('#adnoyer_content').val(),
							},
							success: function(response) {
								if(response.code == 1){
									$('#adnoyer_list').prepend(response.data);
									hideModal();
									waitAdnoyer = 0;
								}
								else {
									callError(system.error);
									waitAdnoyer = 0;
								}
							},
							error: function(){
								callError(system.error);
								waitAdnoyer = 0;
							}
						});
					}
					else {
						return false;
					}
				}
				changeAdnoyer = function(id){
					$.ajax({
						url: "addons/adnoyer/system/action.php",
						type: "post",
						cache: false,
						dataType: 'json',
						data: { 
							adnoyer_save: id,
							adnoyer_title: $('#adnoyer_title').val(),
							adnoyer_content: $('#adnoyer_content').val(),
						},
						success: function(response) {
							if(response.code == 1){
								$('.adnoyer'+id).replaceWith(response.data);
								hideModal();
							}
							else {
								callError(system.error);
							}
						},
						error: function(){
							callError(system.error);
						}
					});
				}
			</script>
		</div>
	</div>
</div>