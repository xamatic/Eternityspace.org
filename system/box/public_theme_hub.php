<?php
require('../config_session.php');

ensurePublicThemeTable();

if(!function_exists('ptEsc')){
	function ptEsc($value){
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}
}
if(!function_exists('publicThemePreviewVars')){
	function publicThemePreviewVars($config, $background = ''){
		$config = publicThemeSanitizeConfig($config);
		$bg_url = publicThemeBackgroundUrl($background);
		$style = '--pt-header-bg:' . $config['header_bg'] . ';';
		$style .= '--pt-header-text:' . $config['header_text'] . ';';
		$style .= '--pt-chat-bg:' . $config['chat_bg'] . ';';
		$style .= '--pt-chat-text:' . $config['chat_text'] . ';';
		$style .= '--pt-bubble-bg:' . $config['bubble_bg'] . ';';
		$style .= '--pt-accent:' . $config['accent'] . ';';
		$style .= '--pt-default:' . $config['default_btn'] . ';';
		$style .= '--pt-opacity:' . $config['panel_opacity'] . ';';
		$style .= '--pt-blur:' . (int) $config['panel_blur'] . 'px;';
		if($bg_url != ''){
			$style .= "--pt-bg-url:url('" . $bg_url . "');";
		}
		else {
			$style .= '--pt-bg-url:none;';
		}
		return $style;
	}
}

$can_publish = publicThemeCanPublish($data);
$can_moderate = publicThemeCanModerate($data);

$my_theme = publicThemeGetByUser($data['user_id']);
$theme_name = 'My Public Theme';
$theme_status = 0;
$theme_locked = 0;
$theme_note = '';
$theme_background = '';
$theme_css = '';
$theme_config = publicThemeDefaultConfig();

if(!empty($my_theme)){
	if(!empty($my_theme['theme_name'])){
		$theme_name = $my_theme['theme_name'];
	}
	$theme_status = (int) $my_theme['theme_status'];
	$theme_locked = (int) $my_theme['theme_locked'];
	$theme_note = (string) $my_theme['theme_note'];
	$theme_background = publicThemeNormalizeBackground((string) $my_theme['theme_background']);
	$theme_css = (string) $my_theme['theme_custom_css'];
	$theme_config = publicThemeConfigFromRow($my_theme);
}
$lock_attr = ($theme_locked > 0) ? 'disabled="disabled"' : '';

$approved = [];
$get_approved = $mysqli->query("SELECT pt.*, u.user_name, u.user_tumb FROM boom_public_theme pt
	LEFT JOIN boom_users u ON pt.theme_user = u.user_id
	WHERE pt.theme_status = '2'
	ORDER BY pt.theme_reviewed DESC, pt.theme_id DESC LIMIT 36");
if($get_approved){
	$approved = $get_approved->fetch_all(MYSQLI_ASSOC);
}

$pending = [];
if($can_moderate){
	$get_pending = $mysqli->query("SELECT pt.*, u.user_name, u.user_tumb FROM boom_public_theme pt
		LEFT JOIN boom_users u ON pt.theme_user = u.user_id
		WHERE pt.theme_status = '1'
		ORDER BY pt.theme_submitted ASC, pt.theme_id ASC LIMIT 30");
	if($get_pending){
		$pending = $get_pending->fetch_all(MYSQLI_ASSOC);
	}
}
?>
<div class="modal_title">Public Themes</div>
<div class="modal_content">
	<div class="public_theme_shell">
		<?php if($can_publish){ ?>
		<div class="public_theme_editor fborder">
			<div class="public_theme_head">
				<div>
					<div class="public_theme_head_title">Theme Publisher</div>
					<div class="public_theme_head_sub">VIP and above can submit one public theme for approval.</div>
				</div>
				<div class="public_theme_status public_theme_status_<?php echo publicThemeStatusClass($theme_status); ?>">
					<?php echo publicThemeStatusText($theme_status); ?>
				</div>
			</div>
			<?php if($theme_locked > 0){ ?>
			<div class="public_theme_lock_note">This theme is approved and immutable. Editing is disabled.</div>
			<?php } else { ?>
			<div class="public_theme_warn_note">Warning: once approved, this theme cannot be edited again.</div>
			<?php } ?>
			<?php if($theme_note != '' && $theme_status == 3){ ?>
			<div class="public_theme_reject_note">Moderator note: <?php echo ptEsc($theme_note); ?></div>
			<?php } ?>

			<div id="public_theme_builder" data-locked="<?php echo $theme_locked; ?>">
				<div class="public_theme_grid">
					<div class="public_theme_group">
						<p class="label">Theme name</p>
						<input id="public_theme_name" class="full_input" type="text" maxlength="32" value="<?php echo ptEsc($theme_name); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Header background</p>
						<input id="public_theme_header_bg" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['header_bg']); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Header text</p>
						<input id="public_theme_header_text" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['header_text']); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Chat background</p>
						<input id="public_theme_chat_bg" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['chat_bg']); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Chat text</p>
						<input id="public_theme_chat_text" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['chat_text']); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Bubble background</p>
						<input id="public_theme_bubble_bg" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['bubble_bg']); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Accent color</p>
						<input id="public_theme_accent" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['accent']); ?>" <?php echo $lock_attr; ?>/>
					</div>
					<div class="public_theme_group">
						<p class="label">Default button color</p>
						<input id="public_theme_default_btn" class="public_theme_color" type="color" value="<?php echo ptEsc($theme_config['default_btn']); ?>" <?php echo $lock_attr; ?>/>
					</div>
				</div>

				<div class="public_theme_slider_group">
					<div class="public_theme_group">
						<p class="label">Panel transparency</p>
						<div class="public_theme_slider_wrap">
							<input id="public_theme_panel_opacity" type="range" min="0.30" max="1" step="0.05" value="<?php echo ptEsc($theme_config['panel_opacity']); ?>" <?php echo $lock_attr; ?>/>
							<span id="public_theme_panel_opacity_value" class="public_theme_slider_value"></span>
						</div>
					</div>
					<div class="public_theme_group">
						<p class="label">Panel blur</p>
						<div class="public_theme_slider_wrap">
							<input id="public_theme_panel_blur" type="range" min="0" max="24" step="1" value="<?php echo ptEsc($theme_config['panel_blur']); ?>" <?php echo $lock_attr; ?>/>
							<span id="public_theme_panel_blur_value" class="public_theme_slider_value"></span>
						</div>
					</div>
				</div>

				<div class="public_theme_group">
					<p class="label">Background image</p>
					<input id="public_theme_bg" type="hidden" value="<?php echo ptEsc($theme_background); ?>"/>
					<input id="public_theme_bg_file" type="file" accept="image/png,image/jpeg,image/gif,image/webp" <?php echo $lock_attr; ?>/>
					<div class="public_theme_bg_controls">
						<button type="button" class="small_button theme_btn" onclick="uploadPublicThemeBackground();" <?php echo $lock_attr; ?>>Upload</button>
						<button type="button" class="small_button default_btn" onclick="removePublicThemeBackground();" <?php echo $lock_attr; ?>>Remove</button>
						<span id="public_theme_bg_state" class="sub_text"></span>
					</div>
				</div>

				<div class="public_theme_group">
					<p class="label">Custom CSS (CSS only)</p>
					<textarea id="public_theme_custom_css" class="small_textarea full_textarea" maxlength="6000" placeholder="Add optional CSS selectors here" <?php echo $lock_attr; ?>><?php echo ptEsc($theme_css); ?></textarea>
				</div>

				<div class="public_theme_action_row">
					<button type="button" class="small_button default_btn" onclick="savePublicThemeDraft();" <?php echo $lock_attr; ?>>Save draft</button>
					<button type="button" class="small_button theme_btn" onclick="submitPublicTheme();" <?php echo $lock_attr; ?>>Submit for review</button>
				</div>
			</div>

			<div class="public_theme_live_box" id="public_theme_live_preview" style="<?php echo ptEsc(publicThemePreviewVars($theme_config, $theme_background)); ?>">
				<div class="public_theme_live_header">
					<div class="public_theme_live_dot"></div>
					<div id="public_theme_live_name" class="public_theme_live_name"><?php echo ptEsc($theme_name); ?></div>
					<div class="public_theme_live_icons"><i class="fa fa-bell"></i><i class="fa fa-cog"></i></div>
				</div>
				<div class="public_theme_live_chat">
					<div class="public_theme_live_row">
						<img src="<?php echo myAvatar($data['user_tumb']); ?>"/>
						<div>
							<div class="public_theme_live_user"><?php echo ptEsc($data['user_name']); ?></div>
							<div class="public_theme_live_bubble">Looks good. Live preview updates while you edit.</div>
						</div>
					</div>
					<div class="public_theme_live_row">
						<img src="<?php echo myAvatar($data['user_tumb']); ?>"/>
						<div>
							<div class="public_theme_live_user">System</div>
							<div class="public_theme_live_bubble">Approved themes are immutable after moderation.</div>
						</div>
					</div>
				</div>
			</div>
			<p class="text_small sub_text tpad10">Live preview applies to this card and temporarily to your page while this menu is open.</p>
		</div>
		<?php } else { ?>
		<div class="public_theme_gate fborder">
			<div class="public_theme_gate_title">Theme Publisher Locked</div>
			<div class="public_theme_gate_text">Publishing is available to VIP and higher ranks.</div>
		</div>
		<?php } ?>

		<div class="public_theme_market fborder">
			<div class="public_theme_market_title">Published Community Themes</div>
			<?php if(empty($approved)){ ?>
			<div class="public_theme_empty">No approved public themes yet.</div>
			<?php } else { ?>
			<div class="public_theme_card_grid">
				<?php foreach($approved as $theme){
					$config = publicThemeConfigFromRow($theme);
					$style = publicThemePreviewVars($config, $theme['theme_background']);
					$is_active = ($data['user_theme'] == $theme['theme_folder']) ? ' public_theme_card_active' : '';
				?>
				<div class="public_theme_card<?php echo $is_active; ?>" style="<?php echo ptEsc($style); ?>">
					<div class="public_theme_card_top">
						<div class="public_theme_card_name"><?php echo ptEsc($theme['theme_name']); ?></div>
						<div class="public_theme_card_author">by <?php echo ptEsc($theme['user_name']); ?></div>
					</div>
					<div class="public_theme_card_preview">
						<div class="public_theme_card_bubble">Preview bubble</div>
					</div>
					<div class="public_theme_card_actions">
						<button class="small_button theme_btn" onclick="applyPublicTheme(<?php echo (int) $theme['theme_id']; ?>);">Apply</button>
						<?php if($is_active != ''){ ?><span class="public_theme_current">Current</span><?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>

		<?php if($can_moderate){ ?>
		<div class="public_theme_moderation fborder">
			<div class="public_theme_market_title">Moderation Queue</div>
			<?php if(empty($pending)){ ?>
			<div class="public_theme_empty">No pending submissions.</div>
			<?php } else { ?>
			<div class="public_theme_queue">
				<?php foreach($pending as $queue){
					$qcfg = publicThemeConfigFromRow($queue);
					$qstyle = publicThemePreviewVars($qcfg, $queue['theme_background']);
				?>
				<div class="public_theme_queue_item" style="<?php echo ptEsc($qstyle); ?>">
					<div class="public_theme_queue_head">
						<div>
							<div class="public_theme_card_name"><?php echo ptEsc($queue['theme_name']); ?></div>
							<div class="public_theme_card_author">by <?php echo ptEsc($queue['user_name']); ?></div>
						</div>
						<div class="public_theme_status public_theme_status_pending">Pending</div>
					</div>
					<div class="public_theme_card_preview">
						<div class="public_theme_card_bubble">Queue preview</div>
					</div>
					<textarea id="public_theme_mod_note_<?php echo (int) $queue['theme_id']; ?>" class="small_textarea full_textarea" maxlength="255" placeholder="Reason required for rejection"></textarea>
					<div class="public_theme_action_row">
						<button class="small_button theme_btn" onclick="moderatePublicTheme(<?php echo (int) $queue['theme_id']; ?>, 'approve');">Approve & publish</button>
						<button class="small_button delete_btn" onclick="moderatePublicTheme(<?php echo (int) $queue['theme_id']; ?>, 'reject');">Reject</button>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>
<div class="modal_control">
	<button onclick="hideOver();" class="reg_button default_btn"><i class="fa fa-times"></i> Close</button>
</div>