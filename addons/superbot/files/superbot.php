<?php if(boomAllow($addons['addons_access'])){ ?>
<script data-cfasync="false">
var requestType = 'reg';
var superbot = '<?php echo $addons['bot_name']; ?>';
var superLow = '<?php echo strtolower($addons['bot_name']); ?>';
var superbotId = '<?php echo $addons['bot_id']; ?>';
var checkbot = '';
var privBot = '';

sendSuperbotMain = function(){
	$.post('addons/superbot/system/superbot_main.php', { 
		search: checkbot,
		name: superbot,
		type: requestType,
		}, function(response) {

	});
};
sendSuperbotPrivate = function(){
	$.post('addons/superbot/system/superbot_private.php', { 
		search: privBot,
		name: superbot,
		bid: currentPrivate,
		}, function(response) {

	});
};

$(document).ready(function(){

	$('#main_input').submit(function(event){
		checkbot = $('#content').val();
		var checkbotLow = $('#content').val().toLowerCase();
		if( checkbot.match(superbot) || checkbotLow.match(superLow) ){
			setTimeout(sendSuperbotMain, 1000);
		}
		else {
			checkbot = '';
		}
	});
	$('#private_input').submit(function(event){
		privBot = $('#message_content').val();
		if(privBot == '' || /^\s+$/.test($('#message_content').val()) ){
			event.preventDefault();
		}
		else {
			if(currentPrivate == superbotId){
				setTimeout(sendSuperbotPrivate, 1000);
			}
			else {
				privBot = '';
			}
		}
	});
});
</script>
<?php } ?>