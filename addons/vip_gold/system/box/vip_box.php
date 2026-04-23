<?php
$load_addons = 'vip_gold';
require_once('../../../../system/config_addons.php');
redisUpdateAddons('vip_gold');


$vip_tab = 'gold';
if(isset($_POST['vip_tab'])){
	$requested_tier = escape($_POST['vip_tab']);
	if($requested_tier == 'star'){
		$vip_tab = 'star';
	}
}
function vipText($key, $fallback = ''){
	global $lang;
	if(isset($lang[$key]) && $lang[$key] != ''){
		return $lang[$key];
	}
	return $fallback;
}

function listVipFeature($tier = 'gold'){
	global $feature, $star_feature;
	$list = '';
	$feature_list = $feature;
	if($tier == 'star' && isset($star_feature)){
		$feature_list = $star_feature;
	}
	foreach($feature_list as $f){
		if($f != ''){
			$list .= boomTemplate('../addons/vip_gold/system/template/vip_feature', $f);
		}
	}
	return $list;
}
function listVipPricing($tier = 'gold'){
	$list = '';
	$start = 1;
	$end = 5;
	if($tier == 'star'){
		$start = 6;
		$end = 9;
	}
	for($i = $start; $i <= $end; $i++){
		$list .= boomAddonsTemplate('../addons/vip_gold/system/template/vip_pricing', $i);
	}
	return $list;
}

$vip_gold_text = vipText('vip_gold_feature_text', vipText('vip_feature_text'));
$vip_star_text = vipText('vip_star_feature_text', 'Star VIP includes everything in Gold VIP plus premium custom features.');

if(!useVip()){
	die();
}
?>
<div class="pad15">
	<div id="vippart1" class="vippart">
		<div class="modal_content">
			<div class="hpad10">
				<div class="vip_text_top centered_element bold pad5">
					<p class="text_med"><?php echo $lang['vip_feature_title']; ?></p>
				</div>
				<div class="vip_tier_tabs">
					<button class="vip_tier_btn" data-tier="gold" onclick="vipTier('gold');"><?php echo vipText('vip_tier_gold', 'Gold VIP'); ?></button>
					<button class="vip_tier_btn" data-tier="star" onclick="vipTier('star');"><?php echo vipText('vip_tier_star', 'Star VIP'); ?></button>
				</div>
				<div class="vip_text_intro centered_element bpad15">
					<p id="vip_feature_note" class="text_small sub_text"><?php echo $vip_gold_text; ?></p>
				</div>
				<div id="vip_feature_gold" class="vip_feature_group vip_table_list text_small hpad10 bpad15">
					<?php echo listVipFeature('gold'); ?>
				</div>
				<div id="vip_feature_star" class="vip_feature_group vip_table_list text_small hpad10 bpad15 hidden">
					<?php echo listVipFeature('star'); ?>
				</div>
			</div>
		</div>
		<div class="modal_control centered_element">
			<button onclick="vipPart('vippart2');" class="large_button ok_btn"><?php echo $lang['vip_take']; ?></button>
		</div>
	</div>
	<div id="vippart2" class="vippart hidden">
		<?php if(isGuest($data)){ ?>
		<div class="modal_content">
			<div class="hpad10">
				<div class="vip_text_top centered_element bold pad5">
					<p class="text_med"><?php echo $lang['register']; ?></p>
				</div>
				<div class="vip_text_intro centered_element vpad10">
					<p class="text_small sub_text"><?php echo $lang['vip_guest']; ?></p>
				</div>
			</div>
		</div>
		<div class="modal_control centered_element">
			<button class="cancel_modal reg_button default_btn"><?php echo $lang['close']; ?></button>
		</div>
		<?php } ?>
		<?php if(!isGuest($data)){ ?>
		<div class="modal_content">
			<div class="bpad15">
				<div class="vip_text_top centered_element bold pad5">
					<p class="text_med"><?php echo $lang['vip_plan_title']; ?></p>
				</div>
				<div class="vip_tier_tabs">
					<button class="vip_tier_btn" data-tier="gold" onclick="vipTier('gold');"><?php echo vipText('vip_tier_gold', 'Gold VIP'); ?></button>
					<button class="vip_tier_btn" data-tier="star" onclick="vipTier('star');"><?php echo vipText('vip_tier_star', 'Star VIP'); ?></button>
				</div>
				<div class="vip_text_intro centered_element bpad15">
					<p id="vip_plan_note" class="text_small sub_text"><?php echo $vip_gold_text; ?></p>
				</div>
			</div>
			<div class="hidden"><input id="vip_active_tier" value="<?php echo $vip_tab; ?>"/></div>
			<div id="vip_plan_gold" class="vip_plan_group vip_table_list text_small bpad10">
				<?php echo listVipPricing('gold'); ?>
			</div>
			<div id="vip_plan_star" class="vip_plan_group vip_table_list text_small bpad10 hidden">
				<?php echo listVipPricing('star'); ?>
			</div>
		</div>
		<div id="vip_cart" class="modal_control centered_element hidden">
			<p class="bpad15"><?php echo $lang['vip_forwho']; ?></p>
			<?php if(!maxVip($data) && !boomAllow(51)){ ?>
			<button onclick="vipCheckout(1);"  class="large_button ok_btn bmargin5"><?php echo $lang['vip_myself']; ?></button>
			<?php } ?>
			<button onclick="vipCheckout(2);"  class="large_button default_btn"><?php echo $lang['vip_other']; ?></button>
		</div>
		<?php } ?>
	</div>
</div>
<script data-cfasync="false">
	var vipTierText = {
		gold: <?php echo json_encode($vip_gold_text); ?>,
		star: <?php echo json_encode($vip_star_text); ?>
	};
	vipResetSelection = function(){
		$('.vip_checkbox').children('i').removeClass('fa-check-circle').addClass('fa-circle').removeClass('success');
		$('#vip_cart').attr('value', 0);
		$('#vip_cart').hide();
	}
	vipTier = function(tier){
		$('#vip_active_tier').val(tier);
		$('.vip_tier_btn').removeClass('active');
		$('.vip_tier_btn[data-tier="' + tier + '"]').addClass('active');
		$('.vip_feature_group').hide();
		$('#vip_feature_' + tier).show();
		$('.vip_plan_group').hide();
		$('#vip_plan_' + tier).show();
		$('#vip_feature_note').text(vipTierText[tier] || '');
		$('#vip_plan_note').text(vipTierText[tier] || '');
		vipResetSelection();
	}
	vipPart = function(part){
		$('.vippart').hide();
		$('#'+part).show();
		selectIt();
	}
	vipPlan = function(item, plan){
		if($('#vip_active_tier').val() == 'gold' && plan > 5){
			return;
		}
		if($('#vip_active_tier').val() == 'star' && plan < 6){
			return;
		}
		if($('#vip_cart').attr('value') == plan){
			$(item).children('.vip_checkbox').children('i').removeClass('fa-check-circle').addClass('fa-circle').removeClass('success');
			$('#vip_cart').attr('value', 0);
			$('#vip_cart').hide();
		}
		else {
			$('#vip_cart').attr('value', plan);
			$('.vip_checkbox').children('i').removeClass('fa-check-circle').addClass('fa-circle').removeClass('success');
			$(item).children('.vip_checkbox').children('i').removeClass('fa-circle').addClass('fa-check-circle').addClass('success');
			$('#vip_cart').show();
		}
	}
	vipCheckout = function(t){
		$.post('addons/vip_gold/system/box/vip_checkout.php', {
			plan: $('#vip_cart').attr('value'),
			type: t,
			}, function(response) {
				if(response == 0){
					callSaved(system.error, 3);
					hideModal();
				}
				else {
					showModal(response, 420);
				}
		});
	}
		$(document).ready(function(){
			vipTier('<?php echo $vip_tab; ?>');
		});
</script>