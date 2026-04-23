<?php if(!boomAllow(50)){ ?>
<script data-cfasync="false">
openVip = function(vipTab){
	var openTab = vipTab || 'gold';
	$.post('addons/vip/system/vip_box.php', { 
		vip_tab: openTab,
		token: utk,
		}, function(response) {
			showModal(response, 540);
	});
}
vipRecover = function(){
	$.post('addons/vip/system/vip_recover.php', { 
		vip_recover: 1,
		token: utk,
		}, function(response) {
	});
}
$(document).ready(function(){
	boomAddCss('addons/vip/files/vip.css');
	<?php if($addons['custom6'] > 0 ){ ?>
	appLeftMenu('gem', '<?php echo $lang['vip_tier_gold']; ?>', 'openVip("gold");');
	<?php } ?>
	<?php if($data['vip_end'] > time() && boomAllow(1) && !boomAllow(50)){ ?>
	vipRecover();
	<?php } ?>
});
</script>
<?php } ?>

<?php if(boomAllow(50)){ ?>
<script data-cfasync="false">
vipClean = function(){
	if(user_rank > 1){
		$.post('addons/vip/system/vip_clean.php', { 
			clean_vip: 1,
			token: utk,
			}, function(response) {
		});
	}
}
$(document).ready(function(){
	cleanVip = setInterval(vipClean, 1200000);
	vipClean();
});
</script>
<?php } ?>