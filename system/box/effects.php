<?php
require('../config_session.php');

$effects = chatEffectList();
$owned_effects = userChatEffectOwned($data['user_id']);
$selected_effect = userChatEffectSelected($data['user_id']);
$profile_catalog = profileEffectCatalog();
$profile_owned = [];
$profile_selected = [];
foreach($profile_catalog as $cat_key => $cat_data){
	$profile_owned[$cat_key] = userProfileEffectOwned($data['user_id'], $cat_key);
	$profile_selected[$cat_key] = userProfileEffectSelected($data['user_id'], $cat_key);
}
$wallet_enabled = useWallet();
?>
<div class="modal_title">
	Effects
</div>
<div class="modal_content effects_modal_inner">
	<?php if($wallet_enabled){ ?>
	<p class="text_small sub_text bpad10">Balance: <?php echo (int) $data['user_gold']; ?> <?php echo $lang['gold']; ?> | All listed options are paid unlocks.</p>
	<?php } ?>
	<div class="effects_tab_bar bpad10">
		<div class="effects_tab effects_tab_active" data-target="effects_tab_chat">Chat FX</div>
		<?php foreach($profile_catalog as $cat_key => $cat_data){ ?>
		<div class="effects_tab" data-target="effects_tab_<?php echo $cat_key; ?>"><?php echo $cat_data['title']; ?></div>
		<?php } ?>
	</div>

	<div id="effects_tab_chat" class="effects_tab_content effects_tab_show">
		<p class="label">Bubble send effects</p>
		<p class="text_small sub_text bpad10">One-shot animations that trigger when you send a message.</p>
		<div class="chat_effect_menu">
			<div class="chat_effect_item <?php echo $selected_effect < 1 ? 'chat_effect_selected' : ''; ?>">
				<div class="chat_effect_head">
					<div class="chat_effect_name">No effect</div>
					<div class="chat_effect_cost">Free</div>
				</div>
				<div class="chat_effect_preview">
					<div class="chat_effect_preview_row">
						<div class="chat_effect_demo bubble" data-effect-class="">Plain bubble</div>
					</div>
				</div>
				<div class="chat_effect_action">
					<?php if($selected_effect < 1){ ?>
					<button class="small_button default_btn" disabled>Selected</button>
					<?php } else { ?>
					<button class="small_button theme_btn" onclick="selectChatEffect(0);">Use</button>
					<?php } ?>
				</div>
			</div>
			<?php foreach($effects as $effect_id => $effect){ ?>
			<?php $is_owned = isset($owned_effects[$effect_id]); ?>
			<?php $is_selected = ((int) $selected_effect === (int) $effect_id); ?>
			<?php $is_linked = (strpos($effect['class'], 'cefx_link') !== false); ?>
			<div class="chat_effect_item <?php echo $is_selected ? 'chat_effect_selected' : ''; ?>">
				<div class="chat_effect_head">
					<div class="chat_effect_name"><?php echo $effect['title']; ?></div>
					<div class="chat_effect_cost"><?php echo (int) $effect['price']; ?> <?php echo $lang['gold']; ?></div>
				</div>
				<div class="chat_effect_preview">
					<div class="chat_effect_preview_row">
						<?php if($is_linked){ ?>
						<div class="chat_effect_demo_target bubble">Prev message</div>
						<?php } ?>
						<div class="chat_effect_demo bubble" data-effect-class="<?php echo $effect['class']; ?>">Preview message</div>
						<button class="tiny_button theme_btn play_effect_preview" data-effect-class="<?php echo $effect['class']; ?>" onclick="playEffectPreview(this);">Play</button>
					</div>
					<?php if(!empty($effect['desc'])){ ?>
					<div class="chat_effect_note text_xsmall sub_text"><?php echo $effect['desc']; ?></div>
					<?php } ?>
				</div>
				<div class="chat_effect_action">
					<?php if($is_selected){ ?>
					<button class="small_button default_btn" disabled>Selected</button>
					<?php } else if($is_owned){ ?>
					<button class="small_button theme_btn" onclick="selectChatEffect(<?php echo $effect_id; ?>);">Use</button>
					<?php } else if($wallet_enabled){ ?>
					<button class="small_button theme_btn" onclick="buyChatEffect(<?php echo $effect_id; ?>);">Buy</button>
					<?php } else { ?>
					<button class="small_button default_btn" disabled>Locked</button>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>

	<?php foreach($profile_catalog as $cat_key => $cat_data){ ?>
	<?php $cat_owned = $profile_owned[$cat_key]; ?>
	<?php $cat_selected = (int) $profile_selected[$cat_key]; ?>
	<div id="effects_tab_<?php echo $cat_key; ?>" class="effects_tab_content">
		<p class="label"><?php echo $cat_data['title']; ?></p>
		<p class="text_small sub_text bpad10"><?php echo $cat_data['desc']; ?></p>
		<div class="chat_effect_menu">
			<div class="chat_effect_item <?php echo $cat_selected < 1 ? 'chat_effect_selected' : ''; ?>">
				<div class="chat_effect_head">
					<div class="chat_effect_name">No effect</div>
					<div class="chat_effect_cost">Free</div>
				</div>
				<div class="chat_effect_preview">
					<div class="profile_effect_demo">
						<div class="profile_effect_avatar_demo"></div>
						<div class="profile_effect_name_demo">Profile Name</div>
						<div class="profile_effect_badge_demo">Mood Badge</div>
					</div>
				</div>
				<div class="chat_effect_action">
					<?php if($cat_selected < 1){ ?>
					<button class="small_button default_btn" disabled>Selected</button>
					<?php } else { ?>
					<button class="small_button theme_btn" onclick="selectProfileEffect('<?php echo $cat_key; ?>', 0);">Use</button>
					<?php } ?>
				</div>
			</div>
			<?php foreach($cat_data['effects'] as $effect_id => $effect){ ?>
			<?php $is_owned = isset($cat_owned[$effect_id]); ?>
			<?php $is_selected = ($cat_selected === (int) $effect_id); ?>
			<div class="chat_effect_item <?php echo $is_selected ? 'chat_effect_selected' : ''; ?>">
				<div class="chat_effect_head">
					<div class="chat_effect_name"><?php echo $effect['title']; ?></div>
					<div class="chat_effect_cost"><?php echo (int) $effect['price']; ?> <?php echo $lang['gold']; ?></div>
				</div>
				<div class="chat_effect_preview">
					<div class="profile_effect_demo <?php echo $effect['class']; ?>">
						<div class="profile_effect_avatar_demo"></div>
						<div class="profile_effect_name_demo">Profile Name</div>
						<div class="profile_effect_badge_demo">Mood Badge</div>
					</div>
					<?php if(!empty($effect['desc'])){ ?>
					<div class="chat_effect_note text_xsmall sub_text"><?php echo $effect['desc']; ?></div>
					<?php } ?>
				</div>
				<div class="chat_effect_action">
					<?php if($is_selected){ ?>
					<button class="small_button default_btn" disabled>Selected</button>
					<?php } else if($is_owned){ ?>
					<button class="small_button theme_btn" onclick="selectProfileEffect('<?php echo $cat_key; ?>', <?php echo $effect_id; ?>);">Use</button>
					<?php } else if($wallet_enabled){ ?>
					<button class="small_button theme_btn" onclick="buyProfileEffect('<?php echo $cat_key; ?>', <?php echo $effect_id; ?>);">Buy</button>
					<?php } else { ?>
					<button class="small_button default_btn" disabled>Locked</button>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>

	<div class="text_small sub_text tpad10">Profile effects apply to your profile card and are one-time paid unlocks.</div>
	</div>
</div>