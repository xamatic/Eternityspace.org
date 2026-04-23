<?php
require('../config_session.php');

if(!canGoofyAdmin()){
	echo 0;
	die();
}
?>
<div class="modal_title">
	Goofy Event Panel
</div>
<div class="modal_content">
	<div class="chat_effect_item">
		<div class="chat_effect_head">
			<div class="chat_effect_name">Announcement</div>
		</div>
		<div class="setting_element tpad10">
			<textarea id="goofy_announce_text" class="small_textarea full_textarea" maxlength="300" placeholder="Announcement text"></textarea>
		</div>
		<div class="btable tpad10">
			<div class="bcell_mid">
				<p class="label">Duration (sec)</p>
				<input id="goofy_announce_duration" class="full_input" type="number" min="5" max="120" value="20"/>
			</div>
			<div class="bcell_mid hpad10"></div>
			<div class="bcell_mid">
				<p class="label">Draggable</p>
				<select id="goofy_announce_drag"><?php echo onOff(0); ?></select>
			</div>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Targets</p>
			<select id="goofy_announce_target_mode">
				<option value="all">All users (global)</option>
				<option value="some">Some users (comma usernames)</option>
			</select>
			<input id="goofy_announce_targets" class="full_input tmargin5" type="text" maxlength="300" placeholder="name1, name2"/>
		</div>
		<div class="chat_effect_action">
			<button class="small_button theme_btn" onclick="sendGoofyAnnouncement();">Send Announcement</button>
		</div>
	</div>

	<div class="chat_effect_item">
		<div class="chat_effect_head">
			<div class="chat_effect_name">Jump Scare Event</div>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Image</p>
			<input id="goofy_jump_image" type="file" accept="image/png,image/jpeg,image/gif,image/webp"/>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Optional MP3</p>
			<input id="goofy_jump_audio" type="file" accept="audio/mpeg,.mp3"/>
		</div>
		<div class="setting_element tpad10">
			<input id="goofy_jump_text" class="full_input" type="text" maxlength="160" placeholder="Optional text overlay"/>
		</div>
		<div class="btable tpad10">
			<div class="bcell_mid">
				<p class="label">Duration (sec)</p>
				<input id="goofy_jump_duration" class="full_input" type="number" min="5" max="60" value="12"/>
			</div>
			<div class="bcell_mid hpad10"></div>
			<div class="bcell_mid">
				<p class="label">Draggable</p>
				<select id="goofy_jump_drag"><?php echo onOff(0); ?></select>
			</div>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Targets</p>
			<select id="goofy_jump_target_mode">
				<option value="all">All users (global)</option>
				<option value="some">Some users (comma usernames)</option>
			</select>
			<input id="goofy_jump_targets" class="full_input tmargin5" type="text" maxlength="300" placeholder="name1, name2"/>
		</div>
		<div class="chat_effect_action">
			<button class="small_button theme_btn" onclick="sendGoofyJump();">Launch Jump Event</button>
		</div>
	</div>

	<div class="chat_effect_item">
		<div class="chat_effect_head">
			<div class="chat_effect_name">Global Audio Event (MP3)</div>
		</div>
		<div class="setting_element tpad10">
			<input id="goofy_audio_file" type="file" accept="audio/mpeg,.mp3"/>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Targets</p>
			<select id="goofy_audio_target_mode">
				<option value="all">All users (global)</option>
				<option value="some">Some users (comma usernames)</option>
			</select>
			<input id="goofy_audio_targets" class="full_input tmargin5" type="text" maxlength="300" placeholder="name1, name2"/>
		</div>
		<div class="chat_effect_action">
			<button class="small_button theme_btn" onclick="sendGoofyAudio();">Broadcast Audio</button>
		</div>
	</div>

	<div class="chat_effect_item">
		<div class="chat_effect_head">
			<div class="chat_effect_name">Random Goofy Burst</div>
		</div>
		<div class="setting_element tpad10">
			<label><input id="goofy_random_effect" type="checkbox" checked/> Random chat effects</label><br/>
			<label><input id="goofy_random_shake" type="checkbox" checked/> Screen shake</label><br/>
			<label><input id="goofy_random_spin" type="checkbox" checked/> Spin some avatars</label>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Duration (sec)</p>
			<input id="goofy_random_duration" class="full_input" type="number" min="5" max="40" value="10"/>
		</div>
		<div class="setting_element tpad10">
			<p class="label">Targets</p>
			<select id="goofy_random_target_mode">
				<option value="all">All users (global)</option>
				<option value="some">Some users (comma usernames)</option>
			</select>
			<input id="goofy_random_targets" class="full_input tmargin5" type="text" maxlength="300" placeholder="name1, name2"/>
		</div>
		<div class="chat_effect_action">
			<button class="small_button theme_btn" onclick="sendGoofyRandom();">Trigger Goofy Burst</button>
		</div>
	</div>
</div>