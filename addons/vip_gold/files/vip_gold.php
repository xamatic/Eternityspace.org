<?php if($addons['custom6'] > 0){ ?>
<script data-cfasync="false">
openVip = function(vipTab){
	var openTab = vipTab || 'gold';
	$.post('addons/vip_gold/system/box/vip_box.php', { 
		vip_tab: openTab,
		token: utk,
		}, function(response) {
			showModal(response, 540);
	});
}
$(document).ready(function(){
	boomAddCss('addons/vip_gold/files/vip_gold.css');
	appLeftMenu('gem', '<?php echo $lang['vip_tier_gold']; ?>', 'openVip("gold");', 'vip_addons_menu');
});
</script>
<?php } ?>