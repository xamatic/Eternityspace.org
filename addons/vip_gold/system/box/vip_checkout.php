<?php
$load_addons = 'vip_gold';
require_once('../../../../system/config_addons.php');

if(!boomAllow(1)){
	die();
}
if(!useVip()){
	die();
}
if(!isset($_POST['plan'], $_POST['type'])){
	die();
}

$plan = escape($_POST['plan'], true);
$type = escape($_POST['type'], true);

if($type == 1 && maxVip($data)){
	die();
}

if(!vipValidPlan($plan)){
	echo 0;
	die();
}
$price = round(vipPrice($plan));
$plan_name = vipPlanName($plan);
?>
<div class="pad15">
	<div class="modal_content">
		<div class="viptop bpad15 bmargin15">
			<div class="text_med bold bpad5">
				<?php echo $lang['vip_order']; ?>
			</div>
			<div class="btable">
				<div class="bcell_mid">
					<p><?php echo $plan_name; ?></p>
					<p><?php echo $price; ?></p>
				</div>
			</div>
		</div>
		<?php if($type == 2){ ?>
		<div class="bpad15">
			<p class="label"><?php echo $lang['vip_who']; ?></p>
			<input id="vip_username" class="full_input vipinput" placeholder="<?php echo $lang['username']; ?>"/>
		</div>
		<?php } ?>
		<?php if($type == 1){ ?>
		<div class="hidden">
			<input id="vip_username" class="full_input"/>
		</div>
		<?php } ?>
	</div>
	<div class="modal_control">
		<button class="reg_button theme_btn" onclick="vipCheckout();"><?php echo $lang['vip_submit']; ?></button>
		<button class="reg_button default_btn cancel_modal"><?php echo $lang['cancel']; ?></button>
	</div>
</div>
<script>
var vipWait = 0;
vipCheckout = function(st){;
	if(vipWait == 0){
		vipWait = 1;
		$.ajax({
			url: "addons/vip_gold/system/vip_transaction.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				plan: '<?php echo $plan; ?>',
				type: '<?php echo $type; ?>',
				username: $('#vip_username').val(),
			},
			success: function(response){
				if(response.code == 1){
					callSaved(system.actionComplete, 1);
					hideModal();
				}
				else if(response.code == 2){
					callSaved(system.cannotUser, 3);
				}
				else if(response.code == 3){
					callSaved(system.noUser, 3);
				}
				else if(response.code == 4){
					callSaved(system.noGold, 3);
				}
				else {
					callSaved(system.error, 3);
					hideModal();
				}
				vipWait = 0;
			},
			error: function(){
				callSaved(system.error, 3);
				hideModal();
			}
			
		});
	}
}
</script>