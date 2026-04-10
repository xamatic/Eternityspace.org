<?php
$load_addons = 'vip_gold';
require_once('../../../../system/config_addons.php');
redisUpdateAddons('vip_gold');


function listVipFeature(){
	global $feature, $lang;
	$list = '';
	foreach($feature as $f){
		if($f != ''){
			$list .= boomTemplate('../addons/vip_gold/system/template/vip_feature', $f);
		}
	}
	return $list;
}

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
				<div class="vip_text_intro centered_element bpad15">
					<p class="text_small sub_text"><?php echo $lang['vip_feature_text']; ?></p>
				</div>
				<div class="vip_table_list text_small hpad10 bpad15">
					<?php echo listVipFeature(); ?>
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
				<div class="vip_text_intro centered_element bpad15">
					<p class="text_small sub_text"><?php echo $lang['vip_plan_text']; ?></p>
				</div>
			</div>
			<div class="vip_table_list text_small bpad10">
				<?php echo boomAddonsTemplate('../addons/vip_gold/system/template/vip_pricing',1); ?>
				<?php echo boomAddonsTemplate('../addons/vip_gold/system/template/vip_pricing',2); ?>
				<?php echo boomAddonsTemplate('../addons/vip_gold/system/template/vip_pricing',3); ?>
				<?php echo boomAddonsTemplate('../addons/vip_gold/system/template/vip_pricing',4); ?>
				<?php echo boomAddonsTemplate('../addons/vip_gold/system/template/vip_pricing',5); ?>
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
	vipPart = function(part){
		$('.vippart').hide();
		$('#'+part).show();
		selectIt();
	}
	vipPlan = function(item, plan){
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
</script>