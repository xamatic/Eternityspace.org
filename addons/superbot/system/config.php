<?php
$load_addons = 'superbot';
require('../../../system/config_addons.php');

if(!canManageAddons()){
	die();
}

?>
<?php echo elementTitle($addons['addons'], 'loadLob(\'admin/setting_addons.php\');'); ?>
<div class="page_full">
	<div>
		<div class="tab_menu">
			<ul>
				<li class="tab_menu_item tab_selected" data="spbot" data-z="superbot_add"><?php echo $lang['add']; ?></li>
				<li class="tab_menu_item" data="spbot" data-z="superbot_search"><?php echo $lang['search']; ?></li>
				<li class="tab_menu_item" data="spbot" data-z="superbot_setting"><?php echo $lang['settings']; ?></li>
				<li class="tab_menu_item" data="spbot" data-z="superbot_help"><?php echo $lang['help']; ?></li>
			</ul>
		</div>
	</div>
	<div class="page_element">
		<div class="tpad15">
			<div id="spbot">
				<div id="superbot_add" class="tab_zone">
					<div class="setting_element ">
						<p class="label"><?php echo $lang['question']; ?></p>
						<input id="set_question" class="full_input" type="text"/>
					</div>
					<div class="setting_element ">
						<p class="label"><?php echo $lang['answers']; ?></p>
						<input id="set_answer1" class="full_input" type="text"/>
					</div>
					<div class="setting_element ">
						<input id="set_answer2" class="full_input" type="text"/>
					</div>
					<div class="setting_element ">
						<input id="set_answer3" class="full_input" type="text"/>
					</div>
					<div class="setting_element ">
						<input id="set_answer4" class="full_input" type="text"/>
					</div>
					<div class="setting_element ">
						<input id="set_answer5" class="full_input" type="text"/>
					</div>
					<button id="add_superbot" onclick="addSuperbot();" type="button" class="reg_button theme_btn"><i class="fa fa-plus-circle"></i> <?php echo $lang['add']; ?></button>
				</div>
				<div id="superbot_help" class="hide_zone tab_zone no_rtl">
					<div class="docu_box">
						<div class="docu_head bback">
							Edit bot name
						</div>
						<div class="docu_content">
							<div class="docu_description sub_text">
								<p>
								You can edit bot name as you edit other member name. You can achieve it by viewing the bot profile then edit his name.
								</p>
							</div>
						</div>
					</div>
					<div class="docu_box">
						<div class="docu_head bback">
							Adding to bot
						</div>
						<div class="docu_content">
							<div class="docu_description sub_text">
								<p>
								For each question you can add up to 5 answer at same time the bot will choose a random answer for the question. To add more than 5 answer simplyrepeat the question and add up to 5 more answers.
								Do not add the bot name in the question and do not add question mark ( ? ) at end of question.
								If you leave the question empty this will make the bot responding to his name only.
								</p>
							</div>
						</div>
					</div>
					<div class="docu_box">
						<div class="docu_head bback">
							Predefined word completion
						</div>
						<div class="docu_content">
							<div class="docu_description sub_text">
								<p>followed by text will return a youtube video in main chat according to search</p><br/>
								<p><span class="theme_color">%user%</span> - Current username</p>
								<p><span class="theme_color">%time%</span> - Current time</p>
								<p><span class="theme_color">%female%</span> - Registered female count</p>
								<p><span class="theme_color">%male%</span> - Registered male count</p>
								<p><span class="theme_color">%members%</span> - Total members count</p>
								<p><span class="theme_color">%bot%</span> - Current bot name</p>		
							</div>
						</div>
					</div>
				</div>
				<div id="superbot_setting" class="tab_zone hide_zone">
					<div class="setting_element ">
						<p class="label"><?php echo $lang['limit_feature']; ?></p>
						<select id="set_superbot_access">
							<?php echo listRank($addons['addons_access']); ?>
						</select>
					</div>
					<button id="save_superbot" onclick="saveSuperbot();" type="button" class="tmargin10 reg_button theme_btn"><i class="fa fa-floppy-o"></i> <?php echo $lang['save']; ?></button>
				</div>
				<div id="superbot_search" class="tab_zone hide_zone">
					<div class="admin_search">
						<div class="admin_input bcell">
							<input class="full_input" id="superbot_data" type="text"/>
						</div>
						<div id="superbot_find" onclick="findSuperbot();" class="admin_search_btn default_btn">
							<i class="fa fa-search" aria-hidden="true"></i>
						</div>
					</div>
					<div id="superbot_result" class="tmargin15">
					</div>
				</div>
			</div>
		</div>
		<div class="config_section">
			<script data-cfasync="false">
				addSuperbot = function(){
						var question = $('#set_question').val();
						var answer1 = $('#set_answer1').val();
						var answer2 = $('#set_answer2').val();
						var answer3 = $('#set_answer3').val();
						var answer4 = $('#set_answer4').val();
						var answer5 = $('#set_answer5').val();
						$.post('addons/superbot/system/action.php', {
							question: question,
							answer1: answer1,
							answer2: answer2,
							answer3: answer3,
							answer4: answer4,
							answer5: answer5,
							}, function(response) {
								if(response == 1){
									callSuccess(system.saved);
									$('#set_question').val('');
									$('#set_answer1').val('');
									$('#set_answer2').val('');
									$('#set_answer3').val('');
									$('#set_answer4').val('');
									$('#set_answer5').val('');
									
								}
								else{
									callError(system.emptyField);
								}
						});	
					}
				findSuperbot = function(){
					var search = $('#superbot_data').val();
					if(search == '' || search.length < 2 ){
						callError(system.tooShort);
					}
					else {
						$.post('addons/superbot/system/action.php', {
							find_in_bot: search,
							}, function(response) {
								$('#superbot_data').val('');
								$('#superbot_result').html(response);
						});	
					}
				}
				superbotDelete = function(elem, id){
					$.post('addons/superbot/system/action.php', {
						sbdelete: id,
						}, function(response) {
							$(elem).parent().remove();
					});	
				}
				saveSuperbot = function(){
					$.post('addons/superbot/system/action.php', {
						save: 1,
						set_superbot_access: $('#set_superbot_access').val(),
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
