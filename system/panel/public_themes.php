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

$my_themes = publicThemeGetUserThemes($data['user_id'], 60);
$requested_theme_id = (int) ($_POST['edit_theme'] ?? 0);
$force_new_theme = (int) ($_POST['new_theme'] ?? 0);
$allow_edit_theme = (int) ($_POST['allow_edit'] ?? 0);

$my_theme = [];
$theme_id = 0;
$builder_title = 'New Theme';
$builder_sub = 'Build and submit your public theme.';

if($requested_theme_id > 0){
	$my_theme = publicThemeGetUserThemeById($data['user_id'], $requested_theme_id);
}
if(empty($my_theme) && $force_new_theme < 1){
	foreach($my_themes as $candidate){
		if((int) $candidate['theme_status'] === 0 && (int) $candidate['theme_locked'] === 0){
			$my_theme = $candidate;
			break;
		}
	}
}

$theme_name = 'My Public Theme';
$theme_status = 0;
$theme_locked = 0;
$theme_note = '';
$theme_background = '';
$theme_css = '';
$theme_config = publicThemeDefaultConfig();

if(!empty($my_theme)){
	$theme_id = (int) $my_theme['theme_id'];
	if(!empty($my_theme['theme_name'])){
		$theme_name = $my_theme['theme_name'];
	}
	$theme_status = (int) $my_theme['theme_status'];
	$theme_locked = (int) $my_theme['theme_locked'];
	$theme_note = (string) $my_theme['theme_note'];
	$theme_background = publicThemeNormalizeBackground((string) $my_theme['theme_background']);
	$theme_css = (string) $my_theme['theme_custom_css'];
	$theme_config = publicThemeConfigFromRow($my_theme);
	$builder_title = 'Edit Theme';
	$builder_sub = 'Submitted themes are immutable. Start a new theme to keep creating.';
}

$overwrite_theme = 0;
if($allow_edit_theme === 1 && $requested_theme_id > 0 && !empty($my_theme) && (int) $my_theme['theme_id'] === $requested_theme_id){
	$overwrite_theme = 1;
	$builder_title = 'Edit Theme';
	$builder_sub = 'Editing here overwrites this same theme. Submit sends the update back to moderation.';
}

$theme_ui_locked = ($theme_locked > 0 && $overwrite_theme < 1) ? 1 : 0;
$lock_attr = ($theme_ui_locked > 0) ? 'disabled="disabled"' : '';

$approved = [];

$viewer_id = (int) $data['user_id'];
$has_theme_install = false;
$has_theme_rate = false;
$check_install = $mysqli->query("SHOW TABLES LIKE 'boom_public_theme_install'");
if($check_install && $check_install->num_rows > 0){
	$has_theme_install = true;
}
$check_rate = $mysqli->query("SHOW TABLES LIKE 'boom_public_theme_rate'");
if($check_rate && $check_rate->num_rows > 0){
	$has_theme_rate = true;
}

$approved_fields = [
	"pt.*",
	"u.user_name",
	"u.user_tumb",
];
if($has_theme_install){
	$approved_fields[] = "(SELECT COUNT(*) FROM boom_public_theme_install pi WHERE pi.theme_id = pt.theme_id) AS theme_installs";
	$approved_fields[] = "IFNULL((SELECT pix.theme_id FROM boom_public_theme_install pix WHERE pix.theme_id = pt.theme_id AND pix.user_id = '$viewer_id' LIMIT 1), 0) AS my_install";
}
else {
	$approved_fields[] = "0 AS theme_installs";
	$approved_fields[] = "0 AS my_install";
}
if($has_theme_rate){
	$approved_fields[] = "(SELECT IFNULL(ROUND(AVG(pr.rate_value), 2), 0) FROM boom_public_theme_rate pr WHERE pr.theme_id = pt.theme_id) AS theme_rate_avg";
	$approved_fields[] = "(SELECT COUNT(*) FROM boom_public_theme_rate prc WHERE prc.theme_id = pt.theme_id) AS theme_rate_count";
	$approved_fields[] = "IFNULL((SELECT prx.rate_value FROM boom_public_theme_rate prx WHERE prx.theme_id = pt.theme_id AND prx.user_id = '$viewer_id' LIMIT 1), 0) AS my_rate";
}
else {
	$approved_fields[] = "0 AS theme_rate_avg";
	$approved_fields[] = "0 AS theme_rate_count";
	$approved_fields[] = "0 AS my_rate";
}
$approved_select = implode(",\n\t", $approved_fields);
$get_approved = $mysqli->query("SELECT $approved_select
	FROM boom_public_theme pt
	LEFT JOIN boom_users u ON pt.theme_user = u.user_id
	WHERE pt.theme_status = '2'
	ORDER BY theme_installs DESC, pt.theme_reviewed DESC, pt.theme_id DESC LIMIT 60");
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

ob_start();
?>
<div id="public_theme_left_root" class="left_keep public_theme_left">
	<div class="pad10">
		<div class="public_theme_left_nav">
			<button id="public_theme_nav_market" class="small_button theme_btn public_theme_nav_btn active" onclick="showPublicThemeMarket();">Published</button>
			<?php if($can_publish){ ?>
			<button id="public_theme_nav_builder" class="small_button default_btn public_theme_nav_btn" onclick="showPublicThemeBuilder();">New Theme</button>
			<?php } ?>
			<?php if($can_moderate){ ?>
			<button id="public_theme_nav_moderator" class="small_button default_btn public_theme_nav_btn" onclick="showPublicThemeModeration();">Moderation <?php if(empty($pending)) { echo ""; } else { echo " (" . count($pending) . ")"; } ?></button>
			<?php } ?>
			<?php foreach($pending as $queue){
				$qcfg = publicThemeConfigFromRow($queue);
				$qstyle = publicThemePreviewVars($qcfg, $queue['theme_background']);
			?>
			<?php } ?>
		</div>

		<div id="public_theme_view_market" class="public_theme_left_view">
			<?php if(!$can_publish){ ?>
			<div class="public_theme_gate fborder bmargin10">
				<div class="public_theme_gate_title">Theme Publisher Locked</div>
				<div class="public_theme_gate_text">Publishing is available to VIP and higher ranks.</div>
			</div>
			<?php } ?>
			<div class="public_theme_market fborder">
				<div class="public_theme_market_head">
					<div class="public_theme_market_title">Published Community Themes</div>
					<div class="public_theme_market_tools">
						<label class="public_theme_sort_label" for="public_theme_sort_mode">Sort</label>
						<select id="public_theme_sort_mode" class="public_theme_sort_select" onchange="sortPublicThemeCards();">
							<option value="installs">Most installs</option>
							<option value="rating">Top rated</option>
							<option value="recent">Newest</option>
							<option value="name">Name A-Z</option>
						</select>
					</div>
				</div>
				<?php if(empty($approved)){ ?>
				<div class="public_theme_empty">No approved public themes yet.</div>
				<?php } else { ?>
				<div class="public_theme_card_grid">
					<?php foreach($approved as $theme){
						$config = publicThemeConfigFromRow($theme);
						$style = publicThemePreviewVars($config, $theme['theme_background']);
						$is_active = ($data['user_theme'] == $theme['theme_folder']) ? ' public_theme_card_active' : '';
						$installs = (int) ($theme['theme_installs'] ?? 0);
						$rate_count = (int) ($theme['theme_rate_count'] ?? 0);
						$rate_avg_val = (float) ($theme['theme_rate_avg'] ?? 0);
						$rate_avg = number_format($rate_avg_val, 1, '.', '');
						$my_rate = (int) ($theme['my_rate'] ?? 0);
						$my_install = ((int) ($theme['my_install'] ?? 0) > 0);
						$is_owner_theme = ((int) $theme['theme_user'] === (int) $data['user_id']);
						$can_delete_theme = ($can_moderate || (int) $theme['theme_user'] === (int) $data['user_id']);
					?>
					<div class="public_theme_card<?php echo $is_active; ?>" data-theme-id="<?php echo (int) $theme['theme_id']; ?>" data-installs="<?php echo $installs; ?>" data-rating="<?php echo ptEsc($rate_avg); ?>" data-rate-count="<?php echo $rate_count; ?>" data-reviewed="<?php echo (int) $theme['theme_reviewed']; ?>" data-name="<?php echo ptEsc(strtolower((string) $theme['theme_name'])); ?>" style="<?php echo ptEsc($style); ?>">
						<div class="public_theme_card_top">
							<div class="public_theme_card_name"><?php echo ptEsc($theme['theme_name']); ?></div>
							<div class="public_theme_card_author">by <?php echo ptEsc($theme['user_name']); ?></div>
						</div>
						<div class="public_theme_card_meta">
							<div class="public_theme_card_stat"><i class="fa fa-download"></i> <span class="public_theme_install_count"><?php echo $installs; ?></span></div>
							<div class="public_theme_card_stat"><i class="fa fa-star"></i> <span class="public_theme_rating_avg"><?php echo ptEsc($rate_avg); ?></span> <span class="public_theme_rating_count">(<?php echo $rate_count; ?>)</span></div>
							<?php if($my_install){ ?><div class="public_theme_installed_tag">Installed</div><?php } ?>
						</div>
						<div class="public_theme_card_preview">
							<div class="public_theme_card_mini_head">
								<div class="public_theme_card_mini_dot"></div>
								<div class="public_theme_card_mini_title"># Main Room</div>
								<div class="public_theme_card_mini_icons"><i class="fa fa-bell"></i><i class="fa fa-cog"></i></div>
							</div>
							<div class="public_theme_card_mini_chat">
								<div class="public_theme_card_mini_msg">
									<div class="public_theme_card_mini_user">Alex</div>
									<div class="public_theme_card_bubble">This one feels clean and readable.</div>
								</div>
								<div class="public_theme_card_mini_msg mine">
									<div class="public_theme_card_mini_user">You</div>
									<div class="public_theme_card_bubble">Background plus bubbles look balanced.</div>
								</div>
							</div>
							<div class="public_theme_card_mini_input">
								<span>Type message...</span>
								<i class="fa fa-paper-plane"></i>
							</div>
						</div>
						<div class="public_theme_rate_row" data-theme-id="<?php echo (int) $theme['theme_id']; ?>" data-my-rate="<?php echo $my_rate; ?>">
							<button class="public_theme_rate_star<?php echo ($my_rate >= 1) ? ' active' : ''; ?>" type="button" onclick="ratePublicTheme(<?php echo (int) $theme['theme_id']; ?>, 1);">&#9733;</button>
							<button class="public_theme_rate_star<?php echo ($my_rate >= 2) ? ' active' : ''; ?>" type="button" onclick="ratePublicTheme(<?php echo (int) $theme['theme_id']; ?>, 2);">&#9733;</button>
							<button class="public_theme_rate_star<?php echo ($my_rate >= 3) ? ' active' : ''; ?>" type="button" onclick="ratePublicTheme(<?php echo (int) $theme['theme_id']; ?>, 3);">&#9733;</button>
							<button class="public_theme_rate_star<?php echo ($my_rate >= 4) ? ' active' : ''; ?>" type="button" onclick="ratePublicTheme(<?php echo (int) $theme['theme_id']; ?>, 4);">&#9733;</button>
							<button class="public_theme_rate_star<?php echo ($my_rate >= 5) ? ' active' : ''; ?>" type="button" onclick="ratePublicTheme(<?php echo (int) $theme['theme_id']; ?>, 5);">&#9733;</button>
						</div>
						<div class="public_theme_card_actions">
							<button class="small_button theme_btn" onclick="applyPublicTheme(<?php echo (int) $theme['theme_id']; ?>);">Apply</button>
							<?php if($is_owner_theme && $can_publish){ ?>
							<button class="small_button default_btn" onclick="editOwnedPublicTheme(<?php echo (int) $theme['theme_id']; ?>);">Edit</button>
							<?php } ?>
							<?php if($can_delete_theme){ ?>
							<button class="small_button delete_btn" onclick="deletePublicTheme(<?php echo (int) $theme['theme_id']; ?>);">Delete</button>
							<?php } ?>
							<?php if($is_active != ''){ ?><span class="public_theme_current">Current</span><?php } ?>
						</div>
					</div>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
		</div>

		<?php if($can_publish){ ?>
		<div id="public_theme_view_builder" class="public_theme_left_view fhide">
			<div class="public_theme_editor fborder">
				<div class="public_theme_head">
					<div>
						<div class="public_theme_head_title"><?php echo $builder_title; ?></div>
						<div class="public_theme_head_sub"><?php echo $builder_sub; ?></div>
					</div>
					<div class="public_theme_status public_theme_status_<?php echo publicThemeStatusClass($theme_status); ?>">
						<?php echo publicThemeStatusText($theme_status); ?>
					</div>
				</div>
				<div class="public_theme_mythemes">
					<button type="button" class="small_button default_btn" onclick="openNewPublicThemeBuilder();">Start New Theme</button>
					<?php if(empty($my_themes)){ ?>
					<span class="public_theme_empty">No saved themes yet.</span>
					<?php } else { ?>
					<?php foreach($my_themes as $owned_theme){
						$owned_id = (int) $owned_theme['theme_id'];
						$owned_active = ($theme_id === $owned_id) ? ' public_theme_mytheme_active' : '';
						$owned_locked = ((int) $owned_theme['theme_locked'] > 0) ? ' Locked' : '';
					?>
					<button type="button" class="small_button default_btn public_theme_mytheme_btn<?php echo $owned_active; ?>" onclick="openPublicThemeBuilderTheme(<?php echo $owned_id; ?>);">#<?php echo $owned_id; ?> <?php echo ptEsc($owned_theme['theme_name']); ?> (<?php echo publicThemeStatusText((int) $owned_theme['theme_status']); ?><?php echo $owned_locked; ?>)</button>
					<?php } ?>
					<?php } ?>
				</div>
				<?php if($overwrite_theme > 0){ ?>
				<div class="public_theme_warn_note">You are editing this existing published theme directly. Submit sends your update for moderation.</div>
				<?php } else if($theme_ui_locked > 0){ ?>
				<div class="public_theme_lock_note">This theme was submitted and is immutable. Start a new theme to make changes.</div>
				<?php } else { ?>
				<div class="public_theme_warn_note">Warning: once submitted, this theme cannot be edited again.</div>
				<?php } ?>
				<?php if($theme_note != '' && $theme_status == 3){ ?>
				<div class="public_theme_reject_note">Moderator note: <?php echo ptEsc($theme_note); ?></div>
				<?php } ?>

				<div id="public_theme_builder" data-locked="<?php echo $theme_ui_locked; ?>" data-theme-id="<?php echo $theme_id; ?>">
					<input id="public_theme_id" type="hidden" value="<?php echo (int) $theme_id; ?>"/>
					<input id="public_theme_overwrite" type="hidden" value="<?php echo (int) $overwrite_theme; ?>"/>
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
						<input id="public_theme_bg_file" class="hidden" type="file" accept="image/png,image/jpeg,image/gif,image/webp" <?php echo $lock_attr; ?>/>
						<div class="public_theme_bg_controls">
							<button type="button" class="small_button default_btn" onclick="choosePublicThemeBackground();" <?php echo $lock_attr; ?>>Choose background image</button>
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
						<button type="button" class="small_button default_btn" onclick="showPublicThemeMarket();">Back</button>
						<?php if($theme_id > 0){ ?>
						<button type="button" class="small_button delete_btn" onclick="deletePublicTheme(<?php echo (int) $theme_id; ?>);">Delete</button>
						<?php } ?>
						<button type="button" class="small_button default_btn" onclick="savePublicThemeDraft();" <?php echo $lock_attr; ?>>Save draft</button>
						<button type="button" class="small_button theme_btn" onclick="submitPublicTheme();" <?php echo $lock_attr; ?>>Submit</button>
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
								<div class="public_theme_live_user">Mia</div>
								<div class="public_theme_live_bubble">This theme preview mirrors the chat vibe.</div>
							</div>
						</div>
						<div class="public_theme_live_row mine">
							<img src="<?php echo myAvatar($data['user_tumb']); ?>"/>
							<div>
								<div class="public_theme_live_user"><?php echo ptEsc($data['user_name']); ?></div>
								<div class="public_theme_live_bubble">Background, bubbles, buttons, and text all update live.</div>
							</div>
						</div>
						<div class="public_theme_live_input"><span>Type message...</span><i class="fa fa-paper-plane"></i></div>
					</div>
				</div>
			</div>
		</div>
		<div id="public_theme_view_moderator" class="public_theme_left_view  <?php if(empty($music)){ echo 'fhide'; } ?>">
			<?php if($can_moderate){ ?>
			<div class="public_theme_moderation fborder tmargin10">
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
							<div class="public_theme_card_mini_head">
								<div class="public_theme_card_mini_dot"></div>
								<div class="public_theme_card_mini_title"># Moderation Preview</div>
							</div>
							<div class="public_theme_card_mini_chat">
								<div class="public_theme_card_mini_msg">
									<div class="public_theme_card_mini_user">Preview</div>
									<div class="public_theme_card_bubble">Reviewing readability and vibe.</div>
								</div>
							</div>
						</div>
						<textarea id="public_theme_mod_note_<?php echo (int) $queue['theme_id']; ?>" class="small_textarea full_textarea" maxlength="255" placeholder="Reason required for rejection"></textarea>
						<div class="public_theme_action_row">
							<button class="small_button theme_btn" onclick="moderatePublicTheme(<?php echo (int) $queue['theme_id']; ?>, 'approve');">Approve</button>
							<button class="small_button delete_btn" onclick="moderatePublicTheme(<?php echo (int) $queue['theme_id']; ?>, 'reject');">Reject</button>
							<button class="small_button default_btn" onclick="deletePublicTheme(<?php echo (int) $queue['theme_id']; ?>);">Delete</button>
						</div>
					</div>
					<?php } ?>
				</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>
<?php
$res['content'] = ob_get_clean();
$res['title'] = 'Public Themes';
echo boomCode(1, $res);
?>