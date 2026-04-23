<?php
$load_addons = 'vip';
require_once('../../../system/config_addons.php');
if(boomAllow(50)){
	die();
}
if(vipOff()){
	die();
}
$vip_tab = 'gold';
if(isset($_POST['vip_tab'])){
	$requested_tier = escape($_POST['vip_tab']);
	if($requested_tier == 'star'){
		$vip_tab = 'star';
	}
}
function vipLangValue($key, $fallback = ''){
	global $lang;
	if(isset($lang[$key]) && $lang[$key] != ''){
		return $lang[$key];
	}
	return $fallback;
}
function listVipFeature($tier = 'gold'){
	$list = '';
	$prefix = 'vip_custom_feature';
	if($tier == 'star'){
		$prefix = 'vip_star_feature';
	}
	$i = 1;
	while($i <= 20){
		$feature = vipLangValue($prefix . $i, '');
		if($feature != ''){
			$list .= boomAddonsTemplate('../addons/vip/system/template/vip_feature', $feature);
		}
		$i++;
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
		$list .= boomAddonsTemplate('../addons/vip/system/template/vip_pricing', $i);
	}
	return $list;
}
$vip_gold_text = vipLangValue('vip_gold_feature_text', vipLangValue('vip_feature_text'));
$vip_star_text = vipLangValue('vip_star_feature_text', 'Star VIP includes everything in Gold VIP plus premium custom features.');
?>
<div id="vip_main_content" class="pad15">
	<div id="vippart1" class="vippart">
		<div class="hpad10">
			<div class="vip_text_top centered_element bold pad5">
				<p class="text_med"><?php echo $lang['vip_feature_title']; ?></p>
			</div>
			<div class="vip_tier_tabs">
				<button class="vip_tier_btn" data-tier="gold" onclick="vipTier('gold');"><?php echo vipLangValue('vip_tier_gold', 'Gold VIP'); ?></button>
				<button class="vip_tier_btn" data-tier="star" onclick="vipTier('star');"><?php echo vipLangValue('vip_tier_star', 'Star VIP'); ?></button>
			</div>
			<div class="vip_text_intro centered_element bpad15">
				<p id="vip_feature_note" class="text_small sub_text"><?php echo $vip_gold_text; ?></p>
			</div>
		</div>
		<div id="vip_feature_gold" class="vip_feature_group vip_table_list text_small hpad10 bpad15">
			<?php echo listVipFeature('gold'); ?>
		</div>
		<div id="vip_feature_star" class="vip_feature_group vip_table_list text_small hpad10 bpad15 hidden">
			<?php echo listVipFeature('star'); ?>
		</div>
		<div class="vip_button_box centered_element vpad15">
			<button onclick="vipPart('vippart2');" class="reg_button ok_btn"><i class="fa fa-suitcase"></i> <?php echo $lang['vip_take']; ?></button>
		</div>
	</div>
	<div id="vippart2" class="vippart hidden">
		<?php if(boomAllow(1)){ ?>
		<div class="hpad10 bpad10">
			<div class="vip_text_top centered_element bold pad5">
				<p class="text_med"><?php echo $lang['vip_plan_title']; ?></p>
			</div>
			<div class="vip_tier_tabs">
				<button class="vip_tier_btn" data-tier="gold" onclick="vipTier('gold');"><?php echo vipLangValue('vip_tier_gold', 'Gold VIP'); ?></button>
				<button class="vip_tier_btn" data-tier="star" onclick="vipTier('star');"><?php echo vipLangValue('vip_tier_star', 'Star VIP'); ?></button>
			</div>
			<div class="vip_text_intro centered_element bpad15">
				<p id="vip_plan_note" class="text_small sub_text"><?php echo $vip_gold_text; ?></p>
			</div>
		</div>
		<div class="hidden"><input id="vip_active_tier" value="<?php echo $vip_tab; ?>"/></div>
		<div class="hpad15 bpad15">
			<div id="vip_plan_gold" class="vip_plan_group vip_table_list text_small bpad10">
				<?php echo listVipPricing('gold'); ?>
			</div>
			<div id="vip_plan_star" class="vip_plan_group vip_table_list text_small bpad10 hidden">
				<?php echo listVipPricing('star'); ?>
			</div>
		</div>
		<div id="vip_cart" class="hpad15 bpad15 centered_element hidden">
			<p class="sub_text text_small" id="vip_selected_title">---</p>
			<p class="bold text_med"><?php echo vipSymbol($addons['custom7']); ?><span id="vip_selected_price">---</span> <?php echo $addons['custom7']; ?></p>
			<div class="vpad15">
				<button value="0" onclick="showSpin();vipPaypal(this);" id="vip_selected" class="reg_button paypal_btn"><?php echo $lang['vip_checkout']; ?></button>
			</div>
		</div>
		<?php } ?>
		<?php if(!boomAllow(1)){ ?>
		<div class="hpad10 bpad10">
			<div class="vip_text_top centered_element bold pad5">
				<p class="text_med"><?php echo $lang['register']; ?></p>
			</div>
			<div class="vip_text_intro centered_element vpad10">
				<p class="text_small sub_text"><?php echo $lang['vip_guest']; ?></p>
			</div>
			<div class="vpad15 centered_element">
				<button class="cancel_modal reg_button ok_btn"><i class="fa fa-times"></i> <?php echo $lang['close']; ?></button>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<div id="vip_spin_box" class="hidden pad15 centered_element">
	<p><i class="fa fa-spinner fa-spin vip_spinner success"></i></p>
	<p class="text_small tpad15"><?php echo $lang['vip_redirect']; ?></p>
</div>
<script data-cfasync="false">
	var vipTierText = {
		gold: <?php echo json_encode($vip_gold_text); ?>,
		star: <?php echo json_encode($vip_star_text); ?>
	};
	vipResetSelection = function(){
		$('.vip_checkbox').children('i').removeClass('fa-check-circle').addClass('fa-circle').removeClass('success');
		if($('#vip_selected').length){
			$('#vip_selected').attr('value', 0);
			$('#vip_selected_title').text('---');
			$('#vip_selected_price').text('---');
			$('#vip_cart').hide();
		}
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
	}
	showSpin = function(part){
		$('#vip_main_content').hide();
		$('#vip_spin_box').show();
	}
	vipPlan = function(item, plan){
		if($('#vip_active_tier').val() == 'gold' && plan > 5){
			return;
		}
		if($('#vip_active_tier').val() == 'star' && plan < 6){
			return;
		}
		if($('#vip_selected').attr('value') == plan){
			$(item).children('.vip_checkbox').children('i').removeClass('fa-check-circle').addClass('fa-circle').removeClass('success');
			$('#vip_selected').attr('value', 0);
			$('#vip_selected_title').text('---');
			$('#vip_selected_price').text('---');
			$('#vip_cart').hide();
		}
		else {
			$('#vip_selected').attr('value', plan);
			$('.vip_checkbox').children('i').removeClass('fa-check-circle').addClass('fa-circle').removeClass('success');
			$(item).children('.vip_checkbox').children('i').removeClass('fa-circle').addClass('fa-check-circle').addClass('success');
			$('#vip_selected_title').text($(item).children('.vip_plan_title').text());
			$('#vip_selected_price').text($(item).children('.vip_price_cell').children('.vip_price').text());
			$('#vip_cart').show();
		}
	}
	vipPaypal = function(item){
		$.post('addons/vip/system/payment/paypal.php', { 
			plan: $(item).attr('value'),
			ref: window.location.href,
			token: utk,
			}, function(response) {
				if(response == 0){
					callSaved(system.error, 3);
					hideModal();
				}
				else if(response.indexOf("Fatal") >= 1 || response.indexOf("error") >= 1){
					callSaved(system.error, 3);
					hideModal();
				}
				else {
					openSamePage(response);
				}
		});
	}
	$(document).ready(function(){
		vipTier('<?php echo $vip_tab; ?>');
	});
</script>