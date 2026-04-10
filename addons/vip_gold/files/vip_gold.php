<?php if($addons['custom6'] > 0){ ?>
<script data-cfasync="false">
openVip = function(){
	$.post('addons/vip_gold/system/box/vip_box.php', { 
		token: utk,
		}, function(response) {
			showModal(response, 540);
	});
}
$(document).ready(function(){
	boomAddCss('addons/vip_gold/files/vip_gold.css');
	appLeftMenu('gem', 'Buy VIP', 'openVip();', 'vip_addons_menu');
});
</script>
<?php } ?>