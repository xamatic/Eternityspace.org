
// DEFINE DEFAULT VALUES

var docTitle = document.title;
var currentPrivate = 0;
var actSpeed = '';
var curActive = 0;
var firstPanel = 'userlist';
var morePriv = 1;
var moreMain = 1;
var scroll = 1;
var waitReply = 0;
var pWait = 0;
var fload = 0;
var lastPost = 0;
var cAction = 0;
var lastPriv = 0;
var curNotify = 0;
var curReport = 0;
var curFriends = 0;
var notifyLoad = 0;
var wallLoad = 0;
var curNews = 0;
var curRm = 0;
var curWarn = '';
var roomRank = 0;
var privLock = 0;
var pstate = 1;
var dragger = 0;
var autoAudio = 1;
var autoVideo = 0;
var rightHide = 1200;
var rightHide2 = 1201;
var leftHide = 1200;
var leftHide2 = 1201;
var defRightWidth = 280;
var defLeftWidth = 280;
var menuHide = 767;
var pfocus = false;
var curWall = 0;
var uPing = 0;
var reactionMenuScope = '';
var reactionMenuTarget = 0;
var reactionEmojiCache = [];
var reactionMenuLoading = 0;
var reactionSyncLock = 0;
var slashCommandMatches = [];
var slashCommandActive = 0;
var slashCommands = [
	{ command: '/topic', desc: 'Set room topic', usage: '/topic your text', insert: '/topic ' },
	{ command: '/clear', desc: 'Clear room messages', usage: '/clear', insert: '/clear' },
	{ command: '/logout', desc: 'Force logout user (owner)', usage: '/logout username', insert: '/logout ', ownerOnly: 1 },
	{ command: '/clearcache', desc: 'Refresh cache (owner)', usage: '/clearcache', insert: '/clearcache', ownerOnly: 1 },
	{ command: '/clean', desc: 'Clear your local chat view', usage: '/clean', insert: '/clean' },
	{ command: '/monitor', desc: 'Open monitor panel', usage: '/monitor', insert: '/monitor' },
	{ command: '/console', desc: 'Open console panel', usage: '/console', insert: '/console', staffOnly: 1 }
];

// PAGE TITLE

var PageTitleNotification = {  
	On: function(){
		$('#siteicon').attr('href', 'default_images/icon2.png'+bbfv);
	},
	Off: function(){
		$('#siteicon').attr('href', 'default_images/icon.png'+bbfv);
	}
}

focused = true;
window.onfocus = function() {
	focused = true;
	PageTitleNotification.Off();
}

window.onblur = function() {
	focused = false;
	pfocus = false;
}

// MAIN CHAT

chatReload = function(){
	var cPosted = Date.now();
	logsControl();
	$.ajax({
		url: "system/action/chat_log.php",
		type: "post",
		cache: false,
		timeout: speed,
		dataType: 'json',
		data: { 
			fload: fload,
			caction: cAction,
			last: lastPost,
			priv: currentPrivate,
			lastp: lastPriv,
			pcount: pCount,
			room: user_room,
			notify: globNotify,
			curset: curSet,
		},
		success: function(response){
			if('check' in response){
				if(response.check == 99){
					location.reload();
					return false;
				}
				else if(response.check == 199){
					return false;
				}
				else if(response.check == 188){
					if(response.act != userAction){
						location.reload();
						return false;
					}
					else {
						return false;
					}
				}
			}
			else {
				var mLogs = response.mlogs;
				var cact = response.cact;
				var pLogs = response.plogs;
				var getPcount = response.pcount;
				speed = response.spd;
				inOut = response.acd;
				priMin = response.pmin;
				
				if(response.act != userAction ){
					location.reload();
				}
				else {
					if('rdata' in response){
						resetRoom(response.rdata, 0);
					}
					else if(clearChat(mLogs)){
						loadChatHistory(mLogs);
						clearPlay();
					}
					else {
						appendChatMessage(mLogs);
					}
					cAction = cact;
					if('del' in response){
						var mainDel = response.del.split(',');
						for (var i = 0; i < mainDel.length; i++){
							$("#log"+mainDel[i]).replaceWith("");
							$(".quote"+mainDel[i]).replaceWith("");
						}
					}
					if('pdel' in response){
						var privDel = response.pdel.split(',');
						for (var i = 0; i < privDel.length; i++){
							privateRemove(privDel[i]);
						}
					}
					if(response.curp == currentPrivate){
						appendPrivateMessage(pLogs);
						if(getPcount !== pCount){
							pCount = getPcount;
							if(pLogs.length === 0 && fload == 1){
								privatePlay();
								tabNotify();
							}
						}
					}
					if('warn' in response){
						if(response.warn != curWarn){
							curWarn = response.warn;
							openWarn();
						}
					}
					if('pico' in response){
						$('#notify_private').show();
					}
					else {
						$('#notify_private').hide();
					}
					if('notify' in response){
						loadNotify(response.notify, 1);
					}
					if('gold' in response){
						$('#gold').text(response.gold);
					}
					if('ruby' in response){
						$('#ruby').text(response.ruby);
					}
					if('rset' in response){
						grantRoom();
					}
					else {
						ungrantRoom();
					}
					if('role' in response){
						roomRank = response.role;
					}
					else {
						roomRank = 0;
					}
					if('curset' in response){
						loadSettings(response.curset);
					}
					// goofy events payload
					if('gevents' in response){
						handleGoofyEvents(response.gevents);
					}
					if('call' in response){
						checkCall(response.call);
					}
					if('uwall' in response){
						wallNotify(response.uwall);
					}
					checkRm(response.rm);
					innactiveControl(cPosted);
					systemLoaded = 1;
					fload = 1;
				}
			}
		},
		error: function(){
			return false;
		}
	});
}

deleteChatMessage = function(t){
	$("#log"+t).replaceWith("");
	$(".quote"+t).replaceWith("");
}

clearChat = (data) => {
	if (fload == 1) {
		for (let i = 0; i < data.length; i++) {
			if (data[i].log_type === 'system__clear') {
				$("#show_chat ul").html('');
				clearPlay();
				return true;
			}
		}
	}
	return false;
};

logExist = t => {
	if($('#log'+t.log_id).length){
		return true;
	}
}

appendTopic = data => {
	$("#show_chat ul").append(renderTopic(data));
	scrollIt(0);
}

chatSound = m => {
	if(fload == 0){
		return;
	}
	else if(m.log_content.includes('my_notice')){
		usernamePlay();
		return;
	}
	else if(m.quote != null && mySelf(m.quote.quser)){
		quotePlay();
		return;
	}
	switch(m.log_type){
		case 'public__message':
			messagePlay();
			break;
		case 'system__join':
			joinPlay();
			break;
		case 'system__mute':
		case 'system__ban':
		case 'system__kick':
		case 'system__block':
		case 'system__name':
			actionPlay();
			break;
		default:
			break;
	}
}

processChatPost = function(message){
	$.ajax({
		url: "system/action/chat_process.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			content: message,
			quote: getQuote(),
		},
		success: function(response){
			if(typeof response != 'object'){
				waitReply = 0;
			}
			else {
				if (response.code == 1 && response.log != ''){
					$('#name').val('');
					appendSelfChatMessage(response.log);
				}
				waitReply = 0;
			}
		},
		error: function(){
			waitReply = 0;	
		}
	});
}

processChatCommand = function(message){
	resetQuote();
	$.ajax({
		url: "system/action/chat_command.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			content: message,
		},
		beforeSend: function(){
			var ccom = 0;
			if(message == '/console' && isStaff(user_rank)){
				getConsole();
				ccom++;
			}
			if(message == '/monitor'){
				getMonitor();
				ccom++;
			}
			if(message == '/clean'){
				$('.chat_log').replaceWith("");
				ccom++;
			}
			if(ccom > 0){
				waitReply = 0;
				return false;
			}
		},
		success: function(response){
			if(typeof response != 'object'){
				waitReply = 0;
			}
			else {
				var code = response.code;
				if(code == 99){
					noAction();
				}
				else if(code == 1){
					callSuccess(system.actionComplete);
				}
				else if (code == 4){
					callError(system.error);
				}
				else if(code == 14){
					appendTopic(response.data);
				}
				else if (code == 200){
					callError(system.invalidCommand);
				}
				else {
					noAction();
				}
				waitReply = 0;
			}
		},
		error: function(){
			waitReply = 0;		
		}
	});
}

getSlashCommandPool = function(){
	var pool = [];
	for(var i = 0; i < slashCommands.length; i++){
		var item = slashCommands[i];
		if(item.ownerOnly && user_rank < 100){
			continue;
		}
		if(item.staffOnly && !isStaff(user_rank)){
			continue;
		}
		pool.push(item);
	}
	return pool;
}
hideSlashMenu = function(){
	$('#slash_command_list').html('');
	$('#slash_command_menu').hide();
	slashCommandMatches = [];
	slashCommandActive = 0;
}
renderSlashMenu = function(){
	if(!Array.isArray(slashCommandMatches) || slashCommandMatches.length < 1){
		hideSlashMenu();
		return;
	}
	var html = '';
	for(var i = 0; i < slashCommandMatches.length; i++){
		var cmd = slashCommandMatches[i];
		var active = (i === slashCommandActive) ? ' slash_command_active' : '';
		html += '<div class="slash_command_item' + active + '" data-index="' + i + '">';
		html += '<div class="slash_command_left">';
		html += '<div class="slash_command_name">' + cmd.command + '</div>';
		html += '<div class="slash_command_desc">' + cmd.desc + '</div>';
		html += '</div>';
		html += '<div class="slash_command_usage">' + cmd.usage + '</div>';
		html += '</div>';
	}
	$('#slash_command_list').html(html);
	$('#slash_command_menu').show();
}
moveSlashSelection = function(step){
	if(!slashCommandMatches.length){
		return;
	}
	slashCommandActive = slashCommandActive + step;
	if(slashCommandActive < 0){
		slashCommandActive = slashCommandMatches.length - 1;
	}
	if(slashCommandActive >= slashCommandMatches.length){
		slashCommandActive = 0;
	}
	renderSlashMenu();
}
selectSlashCommand = function(index){
	index = parseInt(index);
	if(isNaN(index) || !slashCommandMatches[index]){
		return false;
	}
	var cmd = slashCommandMatches[index];
	var input = $('#content');
	input.val(cmd.insert);
	if(input[0] && input[0].setSelectionRange){
		input[0].setSelectionRange(cmd.insert.length, cmd.insert.length);
	}
	input.focus();
	hideSlashMenu();
	return true;
}
updateSlashMenu = function(value){
	value = (value || '').toString();
	if(value === '' || value.charAt(0) !== '/'){
		hideSlashMenu();
		return;
	}
	if(value.indexOf(' ') > -1){
		hideSlashMenu();
		return;
	}
	var query = value.substring(1).toLowerCase();
	var pool = getSlashCommandPool();
	var matches = [];
	for(var i = 0; i < pool.length; i++){
		var commandName = pool[i].command.substring(1).toLowerCase();
		if(query === '' || commandName.indexOf(query) === 0){
			matches.push(pool[i]);
		}
	}
	if(matches.length < 1){
		hideSlashMenu();
		return;
	}
	slashCommandMatches = matches.slice(0, 8);
	slashCommandActive = 0;
	renderSlashMenu();
}

appendSelfChatMessage = data => {
	if(!logExist(data)){
		$("#show_chat ul").append(createChatLog(data));
		triggerLinkedMessageEffects($('#show_chat ul').children().last());
	}
	scrollIt(0);
}

appendChatHistory = data => {
	var message = '';
	for (var i = 0; i < data.length; i++){
		if(!ignored(data[i].user_id) && !ignored(data[i].log_uid) && !boomAllow(data[i].log_rank)){
			data[i].log_content = myNotice(data[i]);
			message += createChatLog(data[i]);
		}
	}
	$("#show_chat ul").prepend(message);
}

loadChatHistory = data => {
	var message = '';
	for (var i = 0; i < data.length; i++){
		lastPost = data[i].log_id;
		if(!ignored(data[i].user_id) && !ignored(data[i].log_uid) && !boomAllow(data[i].log_rank)){
			data[i].log_content = myNotice(data[i]);
			message += createChatLog(data[i]);
		}
	}
	$("#show_chat ul").html(message);
	scrollIt(0);
}

appendChatMessage = data => {
	var message = '';
	for (var i = 0; i < data.length; i++){
		lastPost = data[i].log_id;
		if(!logExist(data[i]) && !ignored(data[i].user_id) && !ignored(data[i].log_uid) && !boomAllow(data[i].log_rank)){
			data[i].log_content = myNotice(data[i]);
			message += createChatLog(data[i]);
			chatSound(data[i]);
			tabNotify();
		}
	}
	$("#show_chat ul").append(message);
	if(message !== ''){
		triggerLinkedMessageEffects($('#show_chat ul').children().slice(-data.length));
	}
	scrollIt(1);
}

renderSystemMessage = (data) => {
	let mess = systemLog[data.log_type] || (data.log_type === 'system__custom' ? '%custom%' : '');
	const replacements = {
		'%user%': `<span onclick="getProfile(${data.tid})" class="bclick sysname">${data.tname}</span>`,
		'%custom%': data.custom
	};
	for (const [key, value] of Object.entries(replacements)) {
		mess = mess.replaceAll(key, value);
	}
	return mess;
}

logsControl = function(){
	if($('#show_chat').attr('value') == 1){
		var countLog = $('.chat_log').length;
		var countLimit = 60;
		var countDiff = countLog - countLimit;
		if(countDiff > 0 && countDiff % 2 === 0){
				$('#chat_logs_container').find('.chat_log:lt('+countDiff+')').replaceWith("");
				moreMain = 1;
		}
	}
}

manageOthers = function(){
	if($('.chat_log').length > 40){
		var otherElem = $( "#show_chat ul li" ).first();
		if($(otherElem).hasClass("other_logs")){
			$(otherElem).replaceWith("");
		}
	}
}

mainLock = function(){
	$('#content, #submit_button, #chat_file').prop('disabled', true);
	if ($('#chat_file').length){
		$("#chat_file")[0].setAttribute("onchange", "doNothing()");
	}
	$('#container_input, #main_load').addClass('hidden');
	$('#main_disabled').removeClass('hidden');
	hideEmoticon();
	closeChatSub();
}

mainUnlock = function(){
	$('#content, #submit_button, #chat_file').prop('disabled', false);
	if ($('#chat_file').length){
		$("#chat_file")[0].setAttribute("onchange", "uploadChatFile()");
	}
	$('#main_disabled, #main_load').addClass('hidden');
	$('#container_input').removeClass('hidden');
}

scrollIt = function(f){
	var t = $('#show_chat ul');
	if(f == 0 || $('#show_chat').attr('value') == 1){
		t.scrollTop(t.prop("scrollHeight"));
	}
}

resizeScroll = function(){
	var m = $('#show_chat ul');
	m.scrollTop(m.prop("scrollHeight"));
	var p = $('#show_private');
	p.scrollTop(p.prop("scrollHeight"));
}

quoteLog = function(item){
	var id = $(item).attr('data');
	initQuote(id);
}
initQuote = function(id){
	if(boomAllow(uQuote)){
		var quoted = $('#quote_control').attr('data');
		var quoteAvatar = $('#log'+id+' .chat_avatar').attr('data-av');
		var quoteName = $('#log'+id+' .chat_avatar').attr('data-name');
		var quoteBot = $('#log'+id+' .chat_avatar').attr('data-bot');
		if(id == quoted || quoteBot > 0){
			resetQuote();
		}
		else {
			$('#quote_control').attr('data', id);
			$('#quote_avatar').attr('src', quoteAvatar);
			$('#quoted_user').text(quoteName);
			$('#quote_controller').show();
			$('#content').focus();
		}
		resetLogMenu();
	}
}
resetQuote = function(){
	$('#quote_control').attr('data', '0');
	$('#quote_avatar').attr('src', '');
	$('#quoted_user').text('');
	$('#quote_controller').hide();
}
getQuote = function(){
	var quote = $('#quote_control').attr('data');
	resetQuote();
	return quote;
}


// PRIVATE CHAT

privateLogExist = t => {
	if($('#priv'+t.log_id).length){
		return true;
	}
}

privateCount = function(c){
	if(c > 0){
		$('#notify_private').show();
	}
	else {
		$('#notify_private').hide();
	} 
}

appendPrivateMessage = (data) => {
    var message = '';
	if(data.length === 0){
		return;
	}

	if (data.length > 0) {
		resetCannotPrivate();
	}
	for (var i = 0; i < data.length; i++) {
		lastPriv = data[i].log_id;
		if (!privateLogExist(data[i]) && !ignored(data[i].user_id)) {
			message += createPrivateLog(data[i]);
		}
	}
    $("#show_private").append(message);
	if(message !== ''){
		triggerLinkedMessageEffects($('#show_private').children().slice(-data.length));
	}
    privSpinner(0);
    scrollPriv(0);
    if (message !== '') {
        privDown(1);
        privatePlay();
        tabNotify();
    }
};

appendPrivateHistory = data => {
	var message = '';
	for (var i = 0; i < data.length; i++){
		lastPriv = data[i].log_id;
		if(!ignored(data[i].user_id)){
			message += createPrivateLog(data[i]);
		}
	}
	$("#show_private").prepend(message);
}

loadPrivateHistory = data => {
	var message = '';
	for (var i = 0; i < data.length; i++){
		lastPriv = data[i].log_id;
		if(!ignored(data[i].user_id)){
			message += createPrivateLog(data[i]);
		}
	}
	$("#show_private").html(message);
	privSpinner(0);
	scrollPriv(1);
	morePriv = 1;
}

appendSelfPrivateMessage = data => {
	if(!privateLogExist(data)){
		resetCannotPrivate();
		$("#show_private").append(createPrivateLog(data));
		triggerLinkedMessageEffects($('#show_private').children().last());
	}
	scrollPriv(1);
}

appendCannotPrivate = data => {
	resetCannotPrivate();
	$("#show_private").append(cannotPrivateTemplate());
	scrollPriv(1);
}

resetCannotPrivate = () => {
	if($('#cannot_private').length){
		$('#cannot_private').replaceWith("");
	}
}

privateLock = function(v){
	$('#private_send, #private_file, #message_content').prop('disabled', true);
	if ($('#private_file').length){
		$("#private_file")[0].setAttribute("onchange", "doNothing()");
	}
	$('#private_input, #main_load').addClass('hidden');
	$('#private_disabled').removeClass('hidden');
	if(v == 1){
		$('.privelem').addClass('fhide');
		privLock = 1;
	}
	hidePrivEmoticon();
	closePrivSub();
}

privateUnlock = function(){
	$('#private_send, #private_file, #message_content').prop('disabled', false);
	if ($('#private_file').length){
		$("#private_file")[0].setAttribute("onchange", "uploadPrivateFile()");
	}
	$('#private_disabled, #private_load').addClass('hidden');
	$('#private_input').removeClass('hidden');
	$('.privelem').removeClass('fhide');
	privLock = 0;
}

scrollPriv = function(z){
	var p = $('#show_private');
	if(z == 1 || $('#private_content').attr('value') == 1){
		p.scrollTop(p.prop("scrollHeight"));
	}
}

privateBox = function(){
	pstate = 1;
	$('#private_center').removeClass('ppanel pfull').addClass('pboxed');
	$('#private_boxing').addClass('fhide');
	$('#private_paneling').removeClass('fhide');
	if(dragger == 1){
		$( "#private_center" ).draggable({
			handle: "#private_name",
			containment: "document",
		});
	}
	scrollPriv(1);
}

privatePanel = function(){
	pstate = 2;
	if($(window).width() > 800){
		$('#private_center').removeClass('pboxed').addClass('ppanel');
	}
	else {
		$('#private_center').removeClass('pboxed').addClass('pfull');
	}
	$('#private_paneling').addClass('fhide');
	$('#private_boxing').removeClass('fhide');
	if(dragger == 1){
		$( "#private_center" ).draggable( "destroy" );
	}
	adjustPrivate();
	scrollPriv(1);
}

privateConvert = function(){
	$('#private_center').removeClass('ppanel').addClass('pfull');
}

adjustPrivate = function(){
	if(pstate == 2){
		var winHeight = $(window).height();
		var headHeight = $('#chat_head').outerHeight();
		var menuFooter = $('#my_menu').outerHeight();
		var chatToping = $('#chat_toping').outerHeight();
		var privToping = $('#private_top').outerHeight();
		var privBottom = $('#priv_input').outerHeight();
		var ch = (winHeight - menuFooter - headHeight);
		var cpp = (ch - privToping - privBottom);
		$(".pcontent").css("height", cpp);
	}
}

openPrivate = function (who, whoName, whoAvatar) {
    privSpinner(1);
    if (who == user_id) return;
    currentPrivate = who;
    $('#private_av, #dpriv_av, #ptyping_av').attr('src', whoAvatar);
    $('#private_av').attr('data', who);
    if (useCall > 0 && boomAllow(canCall) && callLock == 0) {
        $('#private_call').removeClass('fhide');
    } 
	else {
        $('#private_call').addClass('fhide');
    }
    $('#private_call').attr('data', who);
    if (!$('#private_center:visible').length) {
        $('#private_center').removeClass('privhide');
        resetPrivate();
    }
    $('#private_name').text(whoName);
    forceHidePanel();
    adjustPrivate();
    loadPrivate(who);
};

loadPrivate = function(t){
	$.ajax({
		url: "system/action/private_load.php",
		type: "post",
		cache: false,
		timeout: speed,
		dataType: 'json',
		data: { 
			target: t,
		},
		success: function(response){
			loadPrivateHistory(response);
		},
		error: function(){
			return false;
		}
	});
}
privDown = function(v){
	if(v > 0){
		if($('#dpriv:visible').length){
			$('#dpriv_notify').show();
		}
	}
}
resetPrivate = function(){
	$('#private_center').removeClass('privhide');
	$('#dpriv').addClass('privhide');
	$('#dpriv_notify').hide();
	$('#message_content').val('');
	hidePrivEmoticon();
	closePrivSub();
	adjustPrivate();
	scrollPriv(1)
}
togglePrivate = function(type){
	if(type == 1){
		$('#dpriv').removeClass('privhide');
		$('#private_center').addClass('privhide');
		$('#dpriv_notify').hide();
	}
	if(type == 2){
		resetPrivate();
	}
}

getPrivate = function(){
	if(prepareMenu('private_menu')){
		$.post('system/float/private_notify.php', {
			}, function(response) {
				appendMenu('private_menu', response);
		});
	}
	else {
		hideMenu('private_menu');
	}
}

clearPrivateList = function(){
	if($('.priv_mess').length > 0){
		hideMenu('private_menu');
		$.post('system/box/clear_private.php', {
			}, function(response) {
				overModal(response);
		});
	}
}

openPrivateRead = function(){
	if($('.priv_mess').length > 0 && $('.pm_notify').length > 0){
		hideMenu('private_menu');
		$.post('system/box/private_read.php', {
			}, function(response) {
				overModal(response);
		});
	}
}

privateClear = function(){
	$.post('system/action/action_member.php', {
		clear_private: 1,
		}, function(response) {
			$('#notify_private').hide();
			hideOver();
	});
}

privateRead = function(){
	$.post('system/action/action_member.php', {
		read_private: 1,
		}, function(response) {
			$('#notify_private').hide();
			hideOver();
	});
}

confirmClearPrivate = function(){
	hideAll();
	$.post('system/box/private_delete.php', {
		target: currentPrivate,
		}, function(response) {
			overModal(response);
	});
}

clearPrivate = function(u){
	hideOver();
	resetPrivateQuote();
	$.post('system/action/action_chat.php', {
		del_private: 1,
		target: u,
		}, function(response) {
			if(response == 0){
				callError(system.cannotUser);
			}
			else if(response == 1){
				resetPrivateBox();
			}
			else {
				callError(system.error);
			}
	});
}

resetPrivateBox = function(){
	$("#show_private").html('');
	$('#message_content').focus();
	scrollPriv(1);
}

privSpinner = function(t){
	if(t == 1){
		$('#show_private').html('');
		$('#privspin').show();
	}
	else {
		$('#privspin').hide();
	}
}

privateRemove = function(id){
	$('#priv'+id).replaceWith("");
	$('.pquote'+id).replaceWith("");
}

deletePrivateLog = function(item){
	var id = $(item).attr('data-id');
	$.post('system/action/action_chat.php', {
		del_priv: id,
		target: currentPrivate,
		}, function(response) {
			if(response == 1){
				privateRemove(id);
			}
	});
}

clearPrivateBox = function(){
	$("#show_private").html('');
	scrollPriv(1);
}
quotePrivateLog = function(item){
	var id = $(item).attr('data-id');
	initPrivateQuote(id);
}

initPrivateQuote = function(id){
	if(boomAllow(upQuote)){
		var item = $('#priv'+id);
		var quoted = $('#pquote_control').attr('data');
		if(id == quoted){
			resetPrivateQuote();
		}
		else {
			$('#pquoted_user').text($(item).attr('data-name'));
			$('#pquote_avatar').attr('src', $(item).attr('data-av'));
			$('#pquote_control').attr('data', id);
			$('#pquote_controller').show();
			$('#message_content').focus();
		}
	}
}

resetPrivateQuote = function(){
	$('#pquote_controller').hide();
	$('#pquote_control').attr('data', 0);
	$('#pquote_avatar').attr('src', '');
	$('#pquoted_user').text('');
}

getPrivateQuote = function(){
	var quote = $('#pquote_control').attr('data');
	resetPrivateQuote();
	return quote;
}

// SETTINGS

loadSettings = function(t){
	avatarMax = t.avatarmax;
	coverMax = t.covermax;
	riconMax = t.riconmax;
	fileMax = t.filemax;
	speed = t.speed;
	canCall = t.cancall;
	useCall = t.usecall;
	inOut = t.inout;
	uQuote = t.uquote;
	upQuote = t.upquote;
	priMin = t.primin;
	canScontent = t.canscontent;
	canContent = t.cancontent;
	canRoomLogs = t.canrlogs;
	canReport = t.canreport;
	maxEmo = t.maxemo;
	curSet = t.curset;
	useLevel = t.uselevel;
	useBadge = t.usebadge;
}

// ROOM

grantRoom = function(){
	$('.room_granted').removeClass('nogranted');	
}
ungrantRoom = function(){
	$('.room_granted').addClass('nogranted');
}

resetRoom = function(data, load = 1){
	if(load > 0){
		hideAllModal();
	}
	resetLeftPanel(1);

	if(data.room_name == ''){
		data.room_name = docTitle;
	}
	$('.glob_ricon').attr('src', data.room_icon);
	$('.glob_rname').text(data.room_name);
	document.title = data.room_name;
	docTitle = data.room_name;
	
	user_room = data.room_id;
	cAction = data.room_action;
	roomRank = data.room_role;
	$("#show_chat ul").html(loadChatHistory(data.room_logs));
	appendTopic(data.room_topic);
	checkRm(data.room_rm);
	moreMain = 1;
	waitJoin = 0;

	if(load == 1 && $('#container_user:visible').length){
		userReload(1);
	}
}

getRoomList = function(){
	$.ajax({
		url: "system/panel/room_list.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
		},
		beforeSend: function(){
			prepareLeft(380);
		},
		success: function(response){
			showLeftPanel(response.content, 380, response.title);
		},
		error: function(){
			callError(system.error);
		}
	});
}

openRoomSettings = function(){
	$.post('system/box/room_setting.php', {
		}, function(response) {
			showModal(response, 500);
	});
}
openRoomStaff = function(){
	$.post('system/box/room_staff.php', {
		}, function(response) {
			showModal(response, 460);
	});
}
openRoomActions = function(){
	$.post('system/box/room_actions.php', {
		}, function(response) {
			showModal(response, 460);
	});
}
openRoomRank = function(u){
	$.post('system/box/edit_room_rank.php', {
		target: u,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
changeRoomRank = function(id){
	$.post('system/action/action_room.php', {
		target: id,
		room_staff_rank: $('#room_staff_rank').val(),
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
				hideOver();
			}
			else if(response == 2){
				callError(system.noUser);
			}
			else {
				callError(system.cannotUser);
				hideOver();
			}
	});
}
saveRoom = function(){
	$.post('system/action/action_room.php', { 
		save_room: '1',
		set_room_name: $('#set_room_name').val(),
		set_room_description: $('#set_room_description').val(),
		set_room_password: $('#set_room_password').val(),
		set_room_player: $('#set_room_player').val(),
		set_room_access: $('#set_room_access').val(),
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
			}
			if(response == 2){
				callError(system.roomExist);
			}
			if(response == 3){
				location.reload();
			}
			if(response == 4){
				callError(system.roomName);
			}
			if(response == 0){
				callError(system.error);
			}
	});	
}

// IGNORE

ignored = function(id){
	return ignoreList.has(id);
}
addIgnore = function(id){
	ignoreList.add(parseInt(id));
}
removeIgnore = function(id){
	ignoreList.delete(id);
}

// RULES AND PREMISSIONS

checkRm = function(m){
	if(m != curRm){
		if(m.indexOf('m') > 0){
			mainLock();
		}
		else {
			mainUnlock();
		}
		if(m.indexOf('p') > 0){
			privateLock(1);
		}
		else if(m.indexOf('s') > 0){
			privateLock(0);
		}
		else {
			privateUnlock();
		}
		if(m.indexOf('w') > 0){
			postLock();
		}
		curRm = m;
	}
}

// MISC

tabNotify = function(){
	if(focused == false){
		PageTitleNotification.On();
	}
}

doNothing = function(){
	event.preventDefault();
}

noAction = function(){
	return;
}

isInnactive = function(){
	if(curActive > inOut && !isStaff(user_rank) && inOut > 0){
		logOut();
	}
}

showElement = function(t){
	if($('#'+t+':visible').length){
		$('#'+t).hide();
	}
	else {
		$('#'+t).show();
		selectIt();
	}
}


// WARNING

warningBox = function(content){
	var bbox = '<div class="pad_box centered_element"><i class="fa fa-exclamation-triangle warn text_ultra bmargin10"></i><h3>'+content+'</h3></div>';
	showModal(bbox);
}

openWarn = function(){
	$.ajax({
		url: "system/box/warning.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {},
		success: function(response){
			registerModal(response);
		},
		error: function(){
			return false;
		}
	});
}

acceptWarn = function(t, id, p){
	$.post('system/action/action_member.php', {
		accept_warn: 1,
		}, function(response) {
			if(response == 1){
				hideModal();
			}
	});
}

checkWarn = function(v){
	if(v === true){
		openWarn();
	}
}


// PANEL LIST

userReload = function(type){
	if($('#container_user:visible').length || type == 1 || firstPanel == 'userlist'){
		if(type == 1){
			prepareRight(0);
		}
		$.post('system/panel/user_list.php', { 
			}, function(response) {
			chatRightIt(response);
			firstPanel = '';
		});
	}
}

staffList = function(type){
	if($('#container_staff:visible').length || type == 1){
		if(type == 1){
			prepareRight(0);
		}
		$.post('system/panel/staff_list.php', { 
			}, function(response) {
			chatRightIt(response);
			firstPanel = '';
		});
	}
}

myFriends = function(type){
	if($('#container_friends:visible').length || type == 1){
		if(type == 1){
			prepareRight(0);
		}
		$.post('system/panel/friend_list.php', {
			}, function(response) {
				chatRightIt(response);
		});
	}
}

// USERS ACCOUNT

checkRoomGrant = function(v){
	if(v === true){
		grantRoom();
	}
	else {
		ungrantRoom();
	}
}

innactiveControl = function(cPost){
	inactiveStart = 2;
	inMaxStaff = 2;
	inMaxUser = 3;
	inIncrement = 125;
	cLatency = (Date.now() - cPost);
	sp = parseInt(speed);
	nsp = sp + ((curActive - inactiveStart) * inIncrement);
	msp = sp * inMaxUser;
	if(isStaff(user_rank)){
		msp = sp * inMaxStaff;
	}
	if(nsp > msp){
		nsp = msp;
	}
	if(curActive >= inactiveStart){
		clearInterval(chatLog);
		chatLog = setInterval(chatReload, nsp);
		actSpeed = nsp;
	}
	else {
		clearInterval(chatLog);
		chatLog = setInterval(chatReload, sp);
		actSpeed = sp;
	}
	$('#current_active').text(curActive);
	$('#current_speed').text(actSpeed);
	$('#current_latency').text(cLatency);
	$('#logs_counter').text($('.chat_log').length);
}

updateStatus = function(st){;
	$.ajax({
		url: "system/action/action_profile.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			update_status: st,
		},
		success: function(response){
			if(response.code == 1){
				$('.status_icon').attr('src', response.icon);
				$('.status_text').text(response.text);
				hideMenu('status_menu');
			}
			else {
				return false;
			}
		},
		error: function(){
			return false;
		}
		
	});
}

overWrite = function(){
	$.post('system/action/logout.php', { 
		overwrite: 1,
		}, function(response) {
			location.reload();
	});
}

backHome = function(){
	$.post('system/action/action_room.php', { 
		leave_room: '1',
		}, function(response) {
			location.reload();
	});	
}

previewText = function(){
	var c = $('.color_choices').attr('data');
	var b = $('#boldit').val();
	var f = $('#fontit').val();
	$('#preview_text').removeClass();
	$('#preview_text').addClass(c+' '+b+' '+f);
}

saveColor = function(){
	var newColor = $('.color_choices').attr('data');
	var newBold = $('#boldit').val();
	var newFont = $('#fontit').val();
	$.post('system/action/action_profile.php', {
		save_color: newColor,
		save_bold: newBold,
		save_font: newFont,
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
			}
	});
}
refreshEffectsShop = function(){
	if(typeof getEffectsShop === 'function'){
		getEffectsShop();
		return;
	}
	if(typeof getTextOptions === 'function'){
		getTextOptions();
	}
}
initEffectsTabs = function(){
	$(document).off('click.effectsTab', '.effects_tab').on('click.effectsTab', '.effects_tab', function(){
		var target = $(this).attr('data-target');
		if(!target){
			return false;
		}
		$('.effects_tab').removeClass('effects_tab_active');
		$(this).addClass('effects_tab_active');
		$('.effects_tab_content').removeClass('effects_tab_show');
		$('#' + target).addClass('effects_tab_show');
		return false;
	});
}
playEffectPreview = function(item){
	var effectClass = $(item).attr('data-effect-class');
	if(!effectClass){
		return false;
	}
	var row = $(item).closest('.chat_effect_preview_row');
	var demo = row.find('.chat_effect_demo').first();
	var target = row.find('.chat_effect_demo_target').first();
	if(!demo.length){
		return false;
	}
	demo.removeClass(effectClass + ' cfx_preview_live');
	if(target.length){
		target.removeClass('cfx_target_hit');
	}
	void demo[0].offsetWidth;
	demo.addClass(effectClass + ' cfx_preview_live');
	if(effectClass.indexOf('cefx_link') !== -1 && target.length){
		void target[0].offsetWidth;
		target.addClass('cfx_target_hit');
	}
	return false;
}
initEffectsPreview = function(){
	$('.chat_effect_demo[data-effect-class]').each(function(index){
		var demo = $(this);
		var row = demo.closest('.chat_effect_preview_row');
		var target = row.find('.chat_effect_demo_target').first();
		var effectClass = demo.attr('data-effect-class');
		if(!effectClass){
			return;
		}
		setTimeout(function(){
			demo.removeClass(effectClass + ' cfx_preview_live');
			if(target.length){
				target.removeClass('cfx_target_hit');
			}
			void demo[0].offsetWidth;
			demo.addClass(effectClass + ' cfx_preview_live');
			if(effectClass.indexOf('cefx_link') !== -1 && target.length){
				void target[0].offsetWidth;
				target.addClass('cfx_target_hit');
			}
		}, 70 + (index * 90));
	});
}
triggerLinkedMessageEffects = function(context){
	var scope = context ? $(context) : $(document);
	scope.find('.bubble.cefx_link, .hunter_private.cefx_link, .target_private.cefx_link').each(function(){
		var bubble = $(this);
		if(bubble.attr('data-link-fired') == '1'){
			return;
		}
		bubble.attr('data-link-fired', '1');
		var holder = bubble.closest('li.chat_log, li.privlog');
		if(!holder.length){
			return;
		}
		var prev = holder.prevAll('li.chat_log, li.privlog').filter(function(){
			return $(this).find('.bubble, .hunter_private, .target_private').length > 0;
		}).first();
		if(!prev.length){
			return;
		}
		var target = prev.find('.bubble, .hunter_private, .target_private').last();
		if(!target.length){
			return;
		}
		target.addClass('cfx_target_hit');
		setTimeout(function(){
			target.removeClass('cfx_target_hit');
		}, 760);
	});
}
buyChatEffect = function(effect){
		effect = parseInt(effect);
		if(!effect){
			return false;
		}
		$.ajax({
			url: 'system/action/action_chat_effect.php',
			type: 'post',
			cache: false,
			dataType: 'json',
			data: {
				buy_effect: effect,
			},
			success: function(response){
				if(typeof response == 'object' && response.code == 1){
					callSuccess('Chat effect purchased');
					refreshEffectsShop();
				}
				else if(typeof response == 'object' && response.code == 2){
					callError(system.noGold);
				}
				else if(typeof response == 'object' && response.code == 3){
					callError('You already own this effect');
				}
				else {
					callError(system.error);
				}
			},
			error: function(){
				callError(system.error);
			}
		});
	}
selectChatEffect = function(effect){
		effect = parseInt(effect);
		if(isNaN(effect) || effect < 0){
			return false;
		}
		$.ajax({
			url: 'system/action/action_chat_effect.php',
			type: 'post',
			cache: false,
			dataType: 'json',
			data: {
				set_effect: effect,
			},
			success: function(response){
				if(typeof response == 'object' && response.code == 1){
					callSuccess(system.saved);
					refreshEffectsShop();
				}
				else if(typeof response == 'object' && response.code == 3){
					callError('Purchase this effect first');
				}
				else {
					callError(system.error);
				}
			},
			error: function(){
				callError(system.error);
			}
		});
	}
	buyProfileEffect = function(category, effect){
		category = (category || '').toString();
		effect = parseInt(effect);
		if(category == '' || !effect){
			return false;
		}
		$.ajax({
			url: 'system/action/action_chat_effect.php',
			type: 'post',
			cache: false,
			dataType: 'json',
			data: {
				buy_profile_effect: 1,
				effect_category: category,
				effect_id: effect,
				token: utk,
				cp: curPage,
			},
			success: function(response){
				if(typeof response == 'object' && response.code == 1){
					callSuccess('Profile effect purchased');
					refreshEffectsShop();
				}
				else if(typeof response == 'object' && response.code == 2){
					callError(system.noGold);
				}
				else if(typeof response == 'object' && response.code == 3){
					callError('You already own this effect');
				}
				else {
					callError(system.error);
				}
			},
			error: function(){
				callError(system.error);
			}
		});
	}
	selectProfileEffect = function(category, effect){
		category = (category || '').toString();
		effect = parseInt(effect);
		if(category == '' || isNaN(effect) || effect < 0){
			return false;
		}
		$.ajax({
			url: 'system/action/action_chat_effect.php',
			type: 'post',
			cache: false,
			dataType: 'json',
			data: {
				set_profile_effect: 1,
				effect_category: category,
				effect_id: effect,
				token: utk,
				cp: curPage,
			},
			success: function(response){
				if(typeof response == 'object' && response.code == 1){
					callSuccess(system.saved);
					refreshEffectsShop();
				}
				else if(typeof response == 'object' && response.code == 3){
					callError('Purchase this effect first');
				}
				else {
					callError(system.error);
				}
			},
			error: function(){
				callError(system.error);
			}
		});
	}

// EMOTICON

emoticon = function(target, data){
	var curText = $("#"+target).val();
	var count = ((curText.match(/:/g)||[]).length);
	if(count < (maxEmo * 2)){
		if(/\s$/.test(curText) || curText == ''){
			$("#"+target).val($("#"+target).val() +data+' ').focus();
		}
		else {
			$("#"+target).val($("#"+target).val() +' '+data+' ').focus();
		}
	}
}

// PANELS

hidePanel = function(){
	var wh = $(window).width();
	if(wh < leftHide2){
		if(!$(".left_keep:visible").length){
			closeLeft();
		}
	}
	if(wh < rightHide2){
		if(!$(".boom_keep:visible").length){
			$("#chat_right").hide();
		}
	}
}

forceHidePanel = function(){
	var wh = $(window).width();
	if(wh < leftHide2){
		closeLeft();
	}
	if(wh < rightHide2){
		$("#chat_right").hide();
	}
}

closeList = function(){
	resetAvMenu();
	hidePanel();
}

prepareRight = function(size, h){
	hideAll();
	var winWidth = $(window).width();
	if(!h){
		h = 0;
	}
	else {
		$('.panel_option').removeClass('bselected');
	}
	if(size == 0){
		$('#chat_right').css('width', defRightWidth+'px');
	}
	else {
		$('#chat_right').css('width', size+'px');
	}
	chatRightIt(largeSpinner);
	if(winWidth < rightHide2){
		if($('#chat_left:visible').length){
			toggleLeft();
		}
	}
	if(!$('#chat_right:visible').length){
		$('#chat_right').toggle();
	}
}

showLeftPanel = function(data, size, head){
	hideAll();
	var winWidth = $(window).width();
	if(size == 0){
		$('#chat_left').css('width', defRightWidth+'px');
	}
	else {
		$('#chat_left').css('width', size+'px');
	}
	if(!head){
		$('#leftpanel_head').html('');
	}
	else {
		$('#leftpanel_head').html(head);
	}
	$('#chat_left_data').html('');
	if(winWidth < rightHide2){
		if($('#chat_right:visible').length){
			closeRight();
		}
	}
	if(!$('#chat_left:visible').length){
		toggleLeft();
	}
	$('#chat_left_data').html(data);
	selectIt();
}

prepareLeft = function(size){
	hideAll();
	var winWidth = $(window).width();
	if(size == 0){
		$('#chat_left').css('width', defRightWidth+'px');
	}
	else {
		$('#chat_left').css('width', size+'px');
	}
	$('#leftpanel_head').html('');
	$('#chat_left_data').html(largeSpinner);
	if(winWidth < rightHide2){
		if($('#chat_right:visible').length){
			toggleRight();
		}
	}
	if(!$('#chat_left:visible').length){
		toggleLeft();
	}
}

resetLeftPanel = function(){
	$('#chat_left').css('width', defLeftWidth+'px');
	closeLeft();
}

chatRightIt = function(data){
	$('#chat_right_data').html(data);
}

resetRightPanel = function(){
	$('.panel_option').removeClass('bselected');
	$('#users_option').addClass('bselected');
	userReload(1);
}
toggleRight = function(){
	if($('#chat_right:visible').length){
		closeRight();
	}
	else {
		resetRightPanel();
	}
}
closeRight = function(){
	$("#chat_right").toggle();
}
closeLeft = function(){
	$('#chat_left_data').html('')
	$('#public_theme_live_style').remove();
	$("#chat_left").addClass('left_hide');
}
openLeft = function(){
	$("#chat_left").removeClass('left_hide');
}
toggleLeft = function(){
	if($('#chat_left:visible').length){
		closeLeft();
	}
	else {
		openLeft();
	}
}

toggleLeftMenu = function(){
	if($('#chat_left:visible').length){
		return;
	}
	else if($('#left_menu:visible').length){
		hideLeftMenu();
	}
	else {
		showLeftMenu();
	}
}

hideLeftMenu = function(){
	$("#left_menu").addClass('fhide');
}

showLeftMenu = function(){
	$("#left_menu").removeClass('fhide');
}

leftMenuVisible = function(){
	if($('#left_menu:visible').length){
		return true;
	}
	else {
		return false;
	}
}

// NOTIFICATION

loadNotify = function(n, t){
	toggleNotify('notify_friends', n.friends);
	toggleNotify('notify_notify', n.notify);
	toggleNotify('news_notify', n.news);
	toggleNotify('left_notify', n.news);
	toggleNotify('report_notify', n.report);
	if(notifyLoad > 0 && t == 1){
		if(n.news > curNews){
			newsPlay();
		}
		if(n.notify > curNotify || n.friends > curFriends || n.report > curReport){
			notifyPlay();
		}
	}
	curNotify = n.notify;
	curFriends = n.friends;
	curReport = n.report;
	curNews = n.news;
	globNotify = n.nnotif;
	notifyLoad = 1;
}

wallNotify = function(t){
	if(t > curWall){
		toggleNotify('wall_notify', t);
		toggleNotify('left_notify', t);
		if(wallLoad > 0){
			wallPlay();
		}
	}
	curWall = t;
	wallLoad = 1;
}

getReport = function(){
	if(prepareMenu('report_menu')){
		$.post('system/float/report_notify.php', {
			}, function(response) {
				appendMenu('report_menu', response);
		});
	}
	else {
		hideMenu('report_menu');
	}
}

friendRequest = function(){
	$('#notify_friends').hide();
	if(prepareMenu('friends_menu')){
		$.post('system/float/friend_request.php', { 
			}, function(response) {
				appendMenu('friends_menu', response);
				curFriends = 0;
		});
	}
	else {
		hideMenu('friends_menu');
	}
}
getNotification = function(){
	$('#notify_notify').hide();
	if(prepareMenu('notification_menu')){
		$.post('system/float/notification.php', { 
			}, function(response) {
				appendMenu('notification_menu', response);
				curNotify = 0;
		});
	}
	else {
		hideMenu('notification_menu');
	}
}

notifyClear = function(){
	$.post('system/action/action_member.php', {
		clear_notification: 1,
		}, function(response) {
			hideOver();
	});
}

clearNotification = function(){
	if($('.notify_item').length > 0){
		hideMenu('notification_menu');
		$.post('system/box/clear_notify.php', {
			}, function(response) {
				overModal(response);
		});
	}
}

// BADGE

updateBadge = function(){
	if(user_rank > 0 && useBadge > 0){
		$.post('system/action/action_badge.php', {
			}, function(response) {
		});
	}
}


getLeaderboard = function(f){
	$.ajax({
		url: "system/panel/leaderboard/"+f+".php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
		},
		beforeSend: function(){
			prepareLeft(380);
		},
		success: function(response){
			showLeftPanel(response.content, 380, response.title);
		},
		error: function(){
			callError(system.error);
		}
	});
}

// REPORT

showPrivateReport = function(id, item) {
	var post_id = id;
	$.post('system/box/show_private_report.php', { 
		private_report: id,
		}, function(response) {
			if(response == 1){
				item.replaceWith("");
				callError(system.alreadyErase);
			}
			else {
				overModal(response, 400);
			}
	});
}
showProfileReport = function(id, u, type){
	var post_id = id;
	unsetReport(id, type);
	getProfile(u);
}

showChatReport = function(id, item) {
	var post_id = id;
	$.post('system/box/show_chat_report.php', { 
		chat_report: id,
		}, function(response) {
			if(response == 1){
				item.replaceWith("");
				callError(system.alreadyErase);
			}
			else {
				overModal(response, 500);
			}
	});
}
showWallReport = function(id, item) {
	var post_id = id;
	$.post('system/box/show_wall_report.php', { 
		wall_report: id,
		}, function(response) {
			if(response == 1){
				item.replaceWith("");
				callError(system.alreadyErase);
			}
			else {
				overModal(response, 500);
			}
	});
}
showNewsReport = function(id, item) {
	var post_id = id;
	$.post('system/box/show_news_report.php', { 
		news_report: id,
		}, function(response) {
			if(response == 1){
				item.replaceWith("");
				callError(system.alreadyErase);
			}
			else {
				overModal(response, 500);
			}
	});
}

unsetReport = function(id, type){
	hideOver();
	$.post('system/action/action_reports.php', {
		unset_report: id,
		type: type,
		}, function(response) {
			if(response == 1){
				$('.report'+id).replaceWith("");
			}
			else {
				callError(system.error);
			}
	});
}
removeReport = function(t, id, p){
	hideOver();
	$.post('system/action/action_reports.php', {
		remove_report: 1,
		type: t,
		report: id,
		}, function(response) {
			if(response == 1){
				callSuccess(system.actionComplete);
				getActions(p);
			}
			else {
				callError(system.error);
			}
	});
}
makeReport = function(t, p){
	var r = $('#report_reason').val();
	if(r == 0){
		callError(system.selectSomething);
	}
	else{
		hideOver();
		$.post('system/action/action_report.php', { 
			send_report: 1,
			type: t,
			report: p,
			reason: r,
			}, function(response) {
				if(response == 1){
					callSuccess(system.reported);
				}
				else if(response == 3){
					callError(system.reportLimit);
				}
				else if(response == 9){
					callError(system.cannotUser);
				}
				else {
					callError(system.error);
				}
		});
	}
}

reportChatLog = function(item){
	var id = $(item).attr('data');
	resetLogMenu();
	openReport(id, 1);
}

reportWallLog = function(id){
	openReport(id, 2);
}

reportNewsLog = function(id){
	openReport(id, 5);
}

reportPrivateLog = function(){
	openReport(currentPrivate, 3);
}

openReport = function(i, t){
	$.post('system/box/report.php', {
		id: i,
		type: t,
		}, function(response) {
			if(response == 3){
				callError(system.reportLimit);
			}
			else {
				overModal(response);
			}
	});
}

collectReactionTargets = function(scope){
	var ids = [];
	var seen = {};
	$('.msg_react_box[data-scope="' + scope + '"]').each(function(){
		var id = parseInt($(this).attr('data-target'));
		if(!id || seen[id]){
			return;
		}
		seen[id] = 1;
		ids.push(id);
		if(ids.length >= 60){
			return false;
		}
	});
	return ids;
}
syncVisibleReactions = function(){
	if(reactionSyncLock === 1){
		return;
	}
	if(!$('.msg_react_box').length){
		return;
	}
	var chatTargets = collectReactionTargets('chat');
	var privateTargets = collectReactionTargets('private');
	if(chatTargets.length < 1 && privateTargets.length < 1){
		return;
	}
	reactionSyncLock = 1;
	$.ajax({
		url: "system/action/action_reaction_sync.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {
			chat: chatTargets.join(','),
			private: privateTargets.join(','),
		},
		success: function(response){
			if(typeof response === 'object' && response.code == 1){
				if(typeof updateReactionBox === 'function'){
					if(response.chat && typeof response.chat === 'object'){
						Object.keys(response.chat).forEach(function(target){
							updateReactionBox('chat', parseInt(target), response.chat[target]);
						});
					}
					if(response.private && typeof response.private === 'object'){
						Object.keys(response.private).forEach(function(target){
							updateReactionBox('private', parseInt(target), response.private[target]);
						});
					}
				}
			}
		},
		complete: function(){
			reactionSyncLock = 0;
		}
	});
}
renderReactionEmojiMenu = function(items){
	var html = '';
	for(var i = 0; i < items.length; i++){
		var key = items[i].key || '';
		var src = items[i].src || key;
		if(key == '' || src == ''){
			continue;
		}
		html += '<button type="button" class="reaction_menu_item" data-react-key="' + key + '"><img src="' + src + '"/></button>';
	}
	$('#reaction_picker_list').html(html);
}
closeReactionMenu = function(){
	$('#reaction_picker_menu').removeClass('show_menu');
	reactionMenuScope = '';
	reactionMenuTarget = 0;
}
openReactionMenu = function(scope, target){
	target = parseInt(target);
	if(!scope || !target){
		return false;
	}
	reactionMenuScope = scope;
	reactionMenuTarget = target;
	$('#reaction_picker_menu').addClass('show_menu');
	if(Array.isArray(reactionEmojiCache) && reactionEmojiCache.length > 0){
		renderReactionEmojiMenu(reactionEmojiCache);
		return true;
	}
	if(reactionMenuLoading === 1){
		return true;
	}
	reactionMenuLoading = 1;
	$('#reaction_picker_list').html(largeSpinner);
	$.ajax({
		url: "system/action/action_reaction_emoji.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {},
		success: function(response){
			if(typeof response === 'object' && response.code == 1 && Array.isArray(response.items)){
				reactionEmojiCache = response.items;
				renderReactionEmojiMenu(reactionEmojiCache);
			}
			else {
				$('#reaction_picker_list').html('');
			}
		},
		complete: function(){
			reactionMenuLoading = 0;
		}
	});
	return true;
}
openLogReactionMenu = function(item){
	var target = parseInt($(item).attr('data'));
	if(!target){
		return;
	}
	resetLogMenu();
	openReactionMenu('chat', target);
}
reactMessage = function(scope, target, reactKey){
	target = parseInt(target);
	reactKey = (reactKey || '').toString();
	if(!scope || !target || reactKey == ''){
		return false;
	}
	$.ajax({
		url: "system/action/action_reaction.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {
			scope: scope,
			target: target,
			react_key: reactKey,
		},
		success: function(response){
			if(typeof response == 'object' && response.code == 1){
				if(typeof updateReactionBox === 'function'){
					updateReactionBox(scope, target, response.reaction);
				}
				closeReactionMenu();
				setTimeout(syncVisibleReactions, 120);
			}
			else {
				callError(system.error);
			}
		},
		error: function(){
			callError(system.error);
		}
	});
}

chatActivity = function(){
	curActive++;
	isInnactive();
}
resetChatActivity = function(){
	curActive = 0;
}

openOnair = function(){
	$.post('system/box/onair.php', {
		}, function(response) {
			showModal(response, 360);
	});
}
userOnair = function(){
	$.post('system/action/action_dj.php', {
		user_onair: $('#set_user_onair').val(),
		}, function(response) {
	});
}


var curDel = 1000;
deleteLog = function(item){
	var id = $(item).attr('data');
	var delTime = Math.round(new Date() / 1000);
	resetLogMenu();
	curDel = delTime;
	$.post('system/action/action_chat.php', {
			del_post: id,
			}, function(response) {	
				$("#log"+id).replaceWith("");
				$(".quote"+id).replaceWith("");
	});
}
hideLog = function(item){
	var id = $(item).attr('data');
	resetLogMenu();
	$("#log"+id).replaceWith("");
}

hideThisPost = function(elem){
	$(elem).closest( ".other_logs" ).replaceWith("");
}
openAddons = function(){
	var addonsContent = $('#addons_loaded').html();
	showModal('<div class="pad_box">'+addonsContent+'<div class="clear"></div></div>');
}
getMonitor = function(){
	$('#monitor_data').toggle();
}
chatInput = function(){
	$('#content').val('');
	if($(window).width() > 768 && $(window).height() > 480){
		$('#content').focus();
	}	
}
checkSubItem = function(){
	if($('.sub_options').length){
		$('#ok_sub_item').removeClass('sub_hidden');
	}
}
checkPrivSubItem = function(){
	if($('.psub_options').length){
		$('#ok_priv_item').removeClass('sub_hidden');
	}
}
proLike = function(u){
	$.post('system/action/action_member.php', {
		like_profile: u,
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else if(response == 4){
				callError(system.actLimit);
			}
			else {
				$('#plikepro').replaceWith(response);
			}
	});
}
getChatSub = function(){
	hideEmoticon();
	$('#main_input_extra').toggle();
}
getPrivSub = function(){
	hidePrivEmoticon();
	$('#priv_input_extra').toggle();
}
closeChatSub = function(){
	$('#main_input_extra').hide();
}
closePrivSub = function(){
	$('#priv_input_extra').hide();
}
showEmoticon = function(){
	closeChatSub();
	$('#main_emoticon').toggle();
	$('#main_emoticon').attr('value', 0);
	if($('#emo_item').attr('value') == 0){
		$('#emo_item').attr('value', 1);
	}
}
showPrivEmoticon = function(){
	closePrivSub();
	$('#private_emoticon').toggle();
	if($('#emo_item_priv').attr('value') == 0){
		$('#emo_item_priv').attr('value', 1);
	}
}
showPostEmoticon = function(){
	$('#post_emo').toggle();
}
	
hideEmoticon = function(){
	$('#main_emoticon').hide();
}
hidePrivEmoticon = function(){
	$('#private_emoticon').hide();
}
hidePostEmoticon = function(){
	$('#post_emo').hide();
}

adjustPanelWidth = function(){
	$('.cright, .cright2').css('width', defRightWidth+'px');
	$('.cleft, .cleft2').css('width', defLeftWidth+'px');
}

leftMenuCheck = function(){
	if ($('#leaderboard_menu_content').html().trim() !== '') {
		$('#leaderboard_menu_btn').removeClass('fhide');
	}
	if ($('#tool_menu_content').html().trim() !== '') {
		$('#tool_menu_btn').removeClass('fhide');
	}
	if ($('#game_menu_content').html().trim() !== '') {
		$('#game_menu_btn').removeClass('fhide');
	}
	if ($('#app_menu_content').html().trim() !== '') {
		$('#app_menu_btn').removeClass('fhide');
	}
	if ($('#store_menu_content').html().trim() !== '') {
		$('#store_menu_btn').removeClass('fhide');
	}
	if ($('#help_menu_content').html().trim() !== '') {
		$('#help_menu_btn').removeClass('fhide');
	}
}
leftMenuShow = function(){
	var winWidth = $(window).width();
	if(winWidth > menuHide){
		showLeftMenu();
	}
}

rightMenuCheck = function(){
	if ($('#setting_menu_content').html().trim() !== '') {
		$('#setting_menu_on').removeClass('fhide');
	}
}

// PWA 

checkPwa = function() {
    const pwaOn = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
    if (isPwa() && isMobile() && !pwaOn) {
        $('#app_install').removeClass('fhide');
    } 
    else if (pwaOn) {
        $('#app_reload').removeClass('fhide');
    }
}

// WALL AND NEWS

addNews = function(){
	$.post('system/box/add_news.php', { 
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else {
				showModal(response, 500);
			}
	});
}
addWall = function(){
	$.post('system/box/add_wall.php', { 
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else {
				showModal(response, 500);
			}
	});
}
getWall = function(){
	$.ajax({
		url: "system/panel/friend_wall.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
		},
		beforeSend: function(){
			prepareLeft(380);
		},
		success: function(response){
			showLeftPanel(response.content, 380, response.title);
			$('#wall_notify').hide();
		},
		error: function(){
			callError(system.error);
		}
	});
}

getNews = function(){
	$.ajax({
		url: "system/panel/news.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
		},
		beforeSend: function(){
			prepareLeft(380);
		},
		success: function(response){
			showLeftPanel(response.content, 380, response.title);
			$('#news_notify').hide();
		},
		error: function(){
			callError(system.error);
		}
	});
}

getDiscord = function(){
	$.ajax({
		url: "system/panel/discord.php",
		type: "post",
		cache: false,
		dataType: "json",
		data: {},
		beforeSend: function(){
			prepareLeft(380);
		},
			success: function(response){
			showLeftPanel(response.content, 380, response.title);
		},
			error: function(){
			callError(system.error);
		}
	});
}

getPublicThemeLeft = function(view){
	var targetView = (view == 'builder') ? 'builder' : 'market';
	$.ajax({
		url: "system/panel/public_themes.php",
		type: "post",
		cache: false,
		dataType: "json",
		data: {},
		beforeSend: function(){
			prepareLeft(420);
		},
		success: function(response){
			showLeftPanel(response.content, 420, response.title);
			setTimeout(function(){
				if(targetView == 'builder' && typeof showPublicThemeBuilder === 'function'){
					showPublicThemeBuilder();
				}
				else if(typeof showPublicThemeMarket === 'function'){
					showPublicThemeMarket();
				}
			}, 30);
		},
		error: function(){
			callError(system.error);
		}
	});
}

renderExtensionsPanel = function(){
	var $wrap = $('#extensions_dynamic_list');
	if(!$wrap.length){
		return;
	}

	var fallbackExtensions = [
		{
			title: 'Codychat Custom Theme',
			desc: 'Apply custom visual styling and theme controls for your chat.',
			buttons: [
				{ label: 'GitHub', url: 'https://github.com/xamatic/Codychat-Custom-Theme', kind: 'github' },
				{ label: 'Stylus', url: 'https://userstyles.world/style/26426', kind: 'stylus' }

			],
			image: 'js/images/webchat-themes-ext.png'
		},
		{
			title: 'Youtube Viewer',
			desc: 'Watch and share YouTube content from inside your chat panel.',
			buttons: [
				{ label: 'GreasyFork', url: 'https://greasyfork.org/en/scripts/573128-chats-mood-changer', kind: 'script' }
			],
			image: 'js/images/webchat-mood-ext.png'
		}
	];

	var extInfo = {
		'custom theme': {
			desc: 'Apply custom visual styling and theme controls for your chat.',
			buttons: [
				{ label: 'GitHub', url: 'https://github.com/xamatic/Codychat-Custom-Theme', kind: 'github' },
				{ label: 'Stylus', url: 'https://userstyles.world/style/26426', kind: 'stylus' }
			]

		},
		'codychat custom theme': {
			desc: 'Apply custom visual styling and theme controls for your chat.',
			buttons: [
				{ label: 'GitHub', url: 'https://github.com/xamatic/Codychat-Custom-Theme', kind: 'github' },
				{ label: 'Stylus', url: 'https://userstyles.world/style/26426', kind: 'stylus' }

			]
		},
		'mood changer': {
			desc: 'Change chat mood based on youtube videos you watch.',
			buttons: [
				{ label: 'GreasyFork', url: 'https://greasyfork.org/en/scripts/573128-chats-mood-changer', kind: 'script' }
			]
		},
		'chats mood changer': {
			desc: 'Change chat mood based on youtube videos you watch.',
			buttons: [
				{ label: 'GreasyFork', url: 'https://greasyfork.org/en/scripts/573128-chats-mood-changer', kind: 'script' }
			]
		}
	};

	var getExtensionInfo = function(title){
		var normalized = (title || '').toLowerCase();
		if(extInfo[normalized]){
			return extInfo[normalized];
		}
		for(var key in extInfo){
			if(!extInfo.hasOwnProperty(key)){
				continue;
			}
			if(normalized.indexOf(key) !== -1 || key.indexOf(normalized) !== -1){
				return extInfo[key];
			}
		}
		return {};
	};

	var esc = function(text){
		return $('<div/>').text(text || '').html();
	};

	var buildButtonsHtml = function(openCall, buttons, githubUrl){
		var actionsHtml = '';
		if(openCall !== ''){
			actionsHtml += '<button type="button" class="extensions_open_btn" onclick="resetLeftPanel();' + openCall + '">Open</button>';
		}

		var actionButtons = buttons;
		if(!Array.isArray(actionButtons) || actionButtons.length === 0){
			actionButtons = githubUrl !== ''
				? [{ label: 'GitHub', url: githubUrl, kind: 'github' }]
				: [];
		}

		for(var bi = 0; bi < actionButtons.length; bi++){
			var btn = actionButtons[bi] || {};
			if(!btn.url){
				continue;
			}
			var btnLabel = btn.label || 'Link';
			var btnKind = (btn.kind || '').toLowerCase();
			var btnClass = 'extensions_link_btn extensions_alt_btn';
			if(btnKind === 'github'){
				btnClass = 'extensions_link_btn extensions_github_btn';
			}
			else if(btnKind === 'script'){
				btnClass = 'extensions_link_btn extensions_script_btn';
			}

			actionsHtml += '<a class="' + btnClass + '" href="' + esc(btn.url) + '" target="_blank" rel="noopener noreferrer">' + esc(btnLabel) + '</a>';
		}

		return actionsHtml;
	};

	var buildCard = function(extTitle, extDesc, preview, actionsHtml){
		return '' +
			'<div class="extensions_card">' +
				'<div class="extensions_preview">' + preview + '</div>' +
				'<div class="extensions_title">' + esc(extTitle) + '</div>' +
				'<div class="extensions_desc">' + esc(extDesc) + '</div>' +
				'<div class="extensions_actions">' + actionsHtml + '</div>' +
			'</div>';
	};

	var cards = '';
	var total = 0;

	$('#app_menu_content .fmenu_item').each(function(){
		var $item = $(this);
		var extTitle = $.trim($item.find('.fmenu_text').text());
		if(extTitle === ''){
			return;
		}

		total++;
		var lookup = getExtensionInfo(extTitle);
		var extDesc = lookup.desc || ('Launch and manage ' + extTitle + ' directly from your app menu.');
		var githubUrl = lookup.github || ('https://github.com/search?q=' + encodeURIComponent(extTitle));
		var optionButtons = lookup.buttons || [];
		var openCall = ($item.attr('onclick') || '').replace(/"/g, '&quot;');
		var preview = '';

		var iconImg = $item.find('img').first().attr('src');
		var previewImg = lookup.image || iconImg;
		if(previewImg){
			preview = '<img src="' + esc(previewImg) + '" alt="' + esc(extTitle) + ' icon">';
		}
		else {
			var iconClass = $item.find('i').first().attr('class') || 'fa fa-puzzle-piece';
			preview = '<i class="' + esc(iconClass) + '"></i>';
		}

		var cardActions = buildButtonsHtml(openCall, optionButtons, githubUrl);
		cards += buildCard(extTitle, extDesc, preview, cardActions);
	});

	if(total === 0){
		for(var i = 0; i < fallbackExtensions.length; i++){
			var fallback = fallbackExtensions[i];
			var fallbackPreview = fallback.image
				? '<img src="' + esc(fallback.image) + '" alt="' + esc(fallback.title) + ' icon">'
				: '<i class="fa fa-puzzle-piece"></i>';
			var fallbackActions = buildButtonsHtml('', fallback.buttons || [], fallback.github || '');
			cards += buildCard(
				fallback.title,
				fallback.desc,
				fallbackPreview,
				fallbackActions
			);
		}
	}

	$wrap.html(cards);
}

getExtensions = function(){
	$.ajax({
		url: "system/panel/extensions.php",
		type: "post",
		cache: false,
		dataType: "json",
		data: {},
		beforeSend: function(){
			prepareLeft(420);
		},
		success: function(response){
			showLeftPanel(response.content, 420, response.title);
			renderExtensionsPanel();
		},
		error: function(){
			callError(system.error);
		}
	});
}

var nLoadMore = 0;
moreNews = function(){
	var lastNews = $('#container_news').children().last().attr('data');
	wLoadMore = 1;
	$.post('system/action/action_news.php', { 
		more_news: lastNews,
		}, function(response) {
			if(response == 0){
				$('.load_more_news').replaceWith("");
			}
			else {
				$('#container_news').append(response);
				if($(response).filter(".news_box").length < 10){
					$('.load_more_news').replaceWith("");
				}
			}
			wLoadMore = 0;
	});
}
waitNews = 0;
sendNews = function(){
	hidePostEmoticon();
	if(waitNews == 0){
		var myNews = $('#news_data').val();
		var comment = $('#comment_lock').attr('value');
		var like = $('#like_lock').attr('value');
		var news_file = $('#post_file_data').attr('data-key');
		if (/^\s+$/.test(myNews) && news_file == '' || myNews == '' && news_file == ''){
			return false;
		}
		if(myNews.length > 2000){
			return false;
		}
		else{	
			waitNews = 1;
			$.post('system/action/action_news.php', {
				add_news: myNews,
				post_file: news_file,
				comment: comment,
				like: like,
				}, function(response) {
					if(response == 0){
						waitNews = 0;
						return false;
					}
					else {
						$("#container_news").prepend(response);
						hideModal();
						waitNews = 0;
					}
			});
		}
	}
	else {
		return false;
	}
}
var repNews = 0;
newsReply = function(id, item) {
	var content = $(item).val();
	var replyTo = id;
	if (/^\s+$/.test(content) || content == ''){
		return false;
	}
	if(content.length > 1000){
		alert("text is too long");
	}
	else {
		$(item).val('');
		if(repNews == 0){
			repNews = 1;
			$.ajax({
				url: "system/action/action_news.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					content: content,
					reply_news: replyTo,
					token: utk
				},
				success: function(response){
					if(response.code == 1) {
						$('.ncmtbox'+replyTo).prepend(response.data);
						nrepCount(id, response.total);
						repNews = 0;
					}
					else if(response.code == 4){
						callError(system.actLimit);
						repNews = 0;
						return false;
					}
					else {
						repNews = 0;
						return false;
					}
				},
				error: function(){
					repNews = 0;
					return false;
				}
			});	
		}
		else {
			return false;
		}
	}
}
moreNewsComment = function(t, id){
	var offset = $('.ncmtbox'+id).children().last().attr('data');
	$.post('system/action/action_news.php', {
		load_news_reply: 1,
		current: offset,
		id: id,
		}, function(response) {
			if(response == 99){
				return false;
			}
			else if(response == 0){
				$('.nmorebox'+id).html('');
			}
			else {
				$('.ncmtbox'+id).append(response);
				if($(response).filter(".reply_item").length < 10){
					$('.nmorebox'+id).html('');
				}
			}
	});
}
deleteNewsReply = function(t){
	$.ajax({
		url: "system/action/action_news.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			delete_news_reply: t,
		},
		success: function(response){
			if(response.code == 1){
				hideOver();
				$('#nreply'+response.reply).replaceWith("");
				nrepCount(response.news, response.total);
			}
			else {
				hideOver();
				return false;
			}
		},
		error: function(){
			hideOver();
			return false;
		}
	});	
}
newsLike = function(id, type){
	$.ajax({
		url: "system/action/action_news.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			like_news: id,
			like_type:type,
		},
		success: function(response){
			if(response.code == 1) {
				$('.newslike'+id).html(response.data);
			}
			else {
				return false;
			}
		},
		error: function(){
			return false;
		}
	});
}
loadNewsComment = function(item, id){
	if($(item).attr('data') == 1){
		$('.ncmtboxwrap'+id).toggle();
	}	
	else {
		$(item).attr('data', 1);
		$.ajax({
			url: "system/action/action_news.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				load_news_comment: 1,
				id: id,
			},
			success: function(response){
				var comments = response.reply;
				var more = response.more;
				if(comments == 0){
					return false;
				}
				else {
					$('.ncmtbox'+id).html(comments);
					$('.ncmb'+id).show();
					
					if(more != 0){
						$('.nmorebox'+id).html(more);
					}
				}
			},
			error: function(){
				return false;
			}
		});
	}
}
nrepCount = function(id, c){
	if(c > 0){
		$('#nrepcount'+id).text(c);
		$('#nrepcount'+id).parent().removeClass('hidden');
	}
	else {
		$('#nrepcount'+id).text(0);
		$('#nrepcount'+id).parent().addClass('hidden');
	}
}
deleteNews = function(news){
	$.post('system/action/action_news.php', {
		remove_news: news,
		}, function(response) {	
		if(response == 1){
			hideOver();
		}
		else {
			$('#'+response).replaceWith("");
			hideOver();
		}
	});
}
openNewsOptions = function(i){
	$.post('system/box/news_options.php', {
		id: i,
		}, function(response) {	
		if(response == 0){
			callError(system.error);
		}
		else {
			showModal(response);
		}
	});
}
saveNewsOptions = function(){
	var i = $('#news_target').attr('data');
	$.ajax({
		url: "system/action/action_news.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			news_comment: $('#set_ncomment').attr('data'),
			news_like: $('#set_nlike').attr('data'),
			news_id: i,
		},
		success: function(response){
			if(response.code == 1){
				$('#boom_news'+i).replaceWith(response.data);
			}
			else {
				callError(system.error);
			}
		},
	});
}
getNewsOptions = function(){ 
	$.post('system/box/news_post_options.php', {
		pcom: $('#comment_lock').attr('value'),
		plike: $('#like_lock').attr('value'),
		}, function(response) {	
			if(response == 0){
				callError(system.error);
			}
			else {
				overModal(response);
			}
	});
}

setNewsOptions = function(){
	$('#comment_lock').attr('value', $('#set_pcomment').attr('data'));
	$('#like_lock').attr('value', $('#set_plike').attr('data'));
}
viewNewsLikes = function(t){
	$.post('system/box/news_likes.php', { 
		id: t,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				showModal(response, 400);
			}
	});
}
openPostOptions = function(item){
	$(item).children('.post_menu').toggle();
}

var wp = 0;
postWall = function(){
	hidePostEmoticon();
	if(wp == 0){
		var mypost = $('#friend_post').val();
		var post_file = $('#post_file_data').attr('data-key');
		var comment = $('#comment_lock').attr('value');
		var like = $('#like_lock').attr('value');
		if (/^\s+$/.test(mypost) && post_file == '' || mypost == '' && post_file == ''){
			return false;
		}
		if(mypost.length > 2000){
			return false;
		}
		else{
			wp = 1;
			$.post('system/action/action_wall.php', { 
				post_to_wall: mypost,
				post_file: post_file,
				comment: comment,
				like: like,
				}, function(response) {
					if(response == 2){
						wp = 0;
						return false;
					}
					else if(response == 4){
						callError(system.actLimit);
						wp = 0;
						return false;
					}
					else if(response == 0){
						callError(system.error);
					}
					else {
						$('#container_wall').prepend(response);
						hideModal();
						wp = 0;
					}
			});
		}
	}
	else {
		return false;
	}
}
var wr = 0;
postReply = function(id, item) {
	var content = $(item).val();
	var replyTo = id;
	var updateZone = $(item);
	if (/^\s+$/.test(content) || content == ''){
		return false;
	}
	if(content.length > 1000){
		alert("text is too long");
	}
	else {
		$(item).val('');
		if(wr == 0){
			wr = 1;
			$.ajax({
				url: "system/action/action_wall.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					content: content,
					reply_to_wall: replyTo,
					token: utk
				},
				success: function(response){
					if(response.code == 1) {
						$('.cmtbox'+replyTo).prepend(response.data);
						repCount(id, response.total);
						wr = 0;
					}
					else if(response.code == 4){
						callError(system.actLimit);
						wr = 0;
					}
					else {
						wr = 0;
						return false;
					}
				},
				error: function(){
					wr = 0;
					return false;
				}
			});	
		}
		else {
			return false;
		}
	}
}
moreComment = function(t, id){
	var offset = $('.cmtbox'+id).children().last().attr('data');
	$.post('system/action/action_wall.php', {
		load_reply: 1,
		current: offset,
		id: id,
		}, function(response) {
			if(response == 99){
				return false;
			}
			else if(response == 0){
				$('.morebox'+id).html('');
			}
			else {
				$('.cmtbox'+id).append(response);
				if($(response).filter(".reply_item").length < 10){
					$('.morebox'+id).html('');
				}
			}
	});
}
loadComment = function(item, id){
	if($(item).attr('data') == 1){
		$('.cmtboxwrap'+id).toggle();
	}	
	else {
		$(item).attr('data', 1);
		$.ajax({
			url: "system/action/action_wall.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				load_comment: 1,
				id: id,
			},
			success: function(response){
				var comments = response.reply;
				var more = response.more;
				if(comments == 0){
					return false;
				}
				else {
					$('.cmtbox'+id).html(comments);
					$('.cmb'+id).show();
					
					if(more != 0){
						$('.morebox'+id).html(more);
					}
				}
			},
		});
	}
}
showPost = function(i) {
	var post_id = i;
	$.ajax({
		url: "system/box/show_post.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			show_this_post: 1,
			post_id: post_id,
		},
		beforeSend: function(){
			prepareLeft(380);
		},
		success: function(response){
			hideAllMenu();
			showLeftPanel(response.content, 380, response.title);
		},
		error: function(){
			callError(system.error);
		}
	});
}
openWallOptions = function(i){
	$.post('system/box/wall_options.php', {
		id: i,
		}, function(response) {	
		if(response == 0){
			callError(system.error);
		}
		else {
			showModal(response);
		}
	});
}
saveWallOptions = function(){
	var i = $('#wall_target').attr('data');
	$.ajax({
		url: "system/action/action_wall.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			post_comment: $('#set_wcomment').attr('data'),
			post_like: $('#set_wlike').attr('data'),
			post_id: i,
		},
		success: function(response){
			if(response.code == 1){
				$('#boom_post'+i).replaceWith(response.data);
			}
			else {
				callError(system.error);
			}
		},
	});
}
setWallOptions = function(){
	$('#comment_lock').attr('value', $('#set_wcomment').attr('data'));
	$('#like_lock').attr('value', $('#set_wlike').attr('data'));
}
getWallOptions = function(){ 
	$.post('system/box/wall_post_options.php', {
		wcom: $('#comment_lock').attr('value'),
		wlike: $('#like_lock').attr('value'),
		}, function(response) {	
			if(response == 0){
				callError(system.error);
			}
			else {
				overModal(response);
			}
	});
}

openDeletePost = function(t, i){
	$.post('system/box/delete_post.php', {
		type: t,
		id: i,
		}, function(response) {	
		if(response == 1){
			return false;
		}
		else {
			overModal(response);
		}
	});
}
deleteWall = function(t){
	$.post('system/action/action_wall.php', { 
		delete_wall_post: t,
		}, function(response) {
		if(response == 1){
			hideOver();
		}
		else {
			hideOver();
			$('#'+response).replaceWith("");
		}

	});
}
viewWallLikes = function(t){
	$.post('system/box/wall_likes.php', { 
		id: t,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				showModal(response, 400);
			}
	});
}

deleteReply = function(t){
	$.ajax({
		url: "system/action/action_wall.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			delete_reply: t,
		},
		success: function(response){
			if(response.code == 1){
				hideOver();
				$('#wreply'+response.reply).replaceWith("");
				repCount(response.wall, response.total);
			}
			else {
				hideOver();
				return false;
			}
		},
		error: function(){
			hideOver();
			return false;
		}
	});	
}
repCount = function(id, c){
	if(c > 0){
		$('#repcount'+id).text(c);
		$('#repcount'+id).parent().removeClass('hidden');
	}
	else {
		$('#repcount'+id).text(0);
		$('#repcount'+id).parent().addClass('hidden');
	}
}
likeIt = function(id, type){
	$.ajax({
		url: "system/action/action_wall.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			like: id,
			like_type:type,
		},
		success: function(response){
			if(response.code == 1) {
				$('.like'+id).html(response.data);
			}
			else {
				return false;
			}
		},
		error: function(){
			return false;
		}
	});
}
var wLoadMore = 0;
moreWall = function(d){
	var actual = parseInt($(d).attr("data-current"));
	var maxCount = parseInt($(d).attr("data-total"));
	if(actual < maxCount && wLoadMore == 0){
		wLoadMore = 1;
		$.post('system/action/action_wall.php', { 
			load_more_wall: 1,
			offset: actual,
			load_more: 1,
			}, function(response) {
				$(d).attr("data-current", actual + 10);
				if(response != 0){
					$('#container_wall').append(response);
				}
				var newOf = actual + 10;
				if(newOf >= maxCount){
					$(d).replaceWith("");
				}
				wLoadMore = 0;
		});
	}
	else {
		wLoadMore = 0;
		return false;
	}
}

postLock = function(){
	$(".post_input_container, .add_comment, .do_comment").replaceWith("");
}

adjustHeight = function(){
	var winWidth = $(window).width();
	var winHeight = $(window).height();
	var headHeight = $('#chat_head').outerHeight();
	var menuFooter = $('#my_menu').outerHeight();
	var topChatHeight = $('#top_chat_container').outerHeight();
	var chatToping = $('#chat_toping').outerHeight();
	var rightPanelBar = $('#right_panel_bar').outerHeight();
	var leftPanelBar = $('#left_panel_bar').outerHeight();
	var floatBox = (winHeight - headHeight - menuFooter);
	var ccenter = $('#chat_center').outerWidth();

	var ch = (winHeight - menuFooter - headHeight);
	var ch2 = (winHeight - menuFooter - headHeight);
	var ch3 = (winHeight);
	var cb = (ch - topChatHeight - chatToping);
	var cpr = (ch2 - rightPanelBar);
	var cpl = (ch2 - leftPanelBar);

	$(".chatheight, .pheight, .ppanel, .pfull").css("height", ch2);
	$(".crheight").css('height', cpr);
	$(".clheight").css('height', cpl);
	$("#warp_show_chat").css({"height": cb});
	$('.float_menu').css({ "max-height": floatBox - 20 });
	
	if(winWidth > leftHide){
		$("#chat_left").removeClass("cleft2").addClass("cleft").css("display", "table-cell");
	}
	else {
		$("#chat_left").removeClass("cleft").addClass("cleft2");
		$("#chat_left").css("top", headHeight);
	}
	if(winWidth > rightHide){
		$("#chat_right").removeClass("cright2").addClass("cright").css("display", "table-cell");
	}
	else {
		$("#chat_right").removeClass("cright").addClass("cright2");
		$("#chat_right").css("top", headHeight);
	}
	if(winWidth < 801){
		if($('.ppanel:visible').length){
			privateConvert();
		}
	}
}

getSearchUser = function() {
  prepareRight(0);
  $.post('system/panel/user_search.php', {
	}, function(response) {
		chatRightIt(response);
		selectIt();
  });
}

getCallList = function(){
		prepareRight(0);
		$.post('system/panel/call_list.php', { 
			}, function(response) {
			chatRightIt(response);
		});
}

searchUser = function(){
	$("#usearch_result").fadeIn().html(largeSpinner);
	boomDelay(function() {
		$.post('system/action/action_search.php', {
			query: $('#usearch_input').val(),
			search_type: $('#usearch_type').val(),
			search_order: $('#usearch_order').val(),
			}, function(response) {
				$('#usearch_result').fadeIn();
				$("#usearch_result").html(response);
		});
	}, 1500);
}

function systemInit() {
	
	adjustHeight();
	adjustPrivate();

	document.title = roomTitle;
	adjustPanelWidth();

	userlist     = setInterval(userReload, 30000);
	friendlis    = setInterval(myFriends, 30000);
	stafflis     = setInterval(staffList, 30000);
	addBalance   = setInterval(chatActivity, 60000);
	clearOtherLogs = setInterval(manageOthers, 30000);
	runModal     = setInterval(checkModal, 1500);
	badgeUpdate  = setInterval(updateBadge, 120000);
	chatLog = setInterval(chatReload, speed);
	setInterval(syncVisibleReactions, 2500);

	userReload();
	adjustHeight();
	chatActivity();
	checkSubItem();
	checkPrivSubItem();
	manageOthers();
	checkModal();
	chatReload();
	syncVisibleReactions();
	leftMenuShow();

	setTimeout(updateBadge, 3000);
	setTimeout(leftMenuCheck, 1000);
	setTimeout(rightMenuCheck, 1000);
	setTimeout(checkPwa, 1000);
}



/* document ready functions */

$(document).ready(function(){
	
	systemInit();
	
	$(document).click(function() {
		resetChatActivity();
	});
	
	$(document).keydown(function(){
		resetChatActivity();
	});
	
	$('#content, #submit_button').prop('disabled', false);
	
	$('#container_show_chat').on('click', '#show_chat .username', function() {
		emoticon('content', $(this).text());
	});

	$(document).on('click', '.msg_react_btn', function(event){
		event.preventDefault();
		event.stopPropagation();
		var scope = $(this).attr('data-scope');
		var target = parseInt($(this).attr('data-target'));
		var react = ($(this).attr('data-react-key') || '').toString();
		if(!scope || !target || react == ''){
			return false;
		}
		reactMessage(scope, target, react);
	});

	$(document).on('click', '.msg_react_more', function(event){
		event.preventDefault();
		event.stopPropagation();
		var scope = $(this).attr('data-scope');
		var target = parseInt($(this).attr('data-target'));
		if(!scope || !target){
			return false;
		}
		openReactionMenu(scope, target);
	});

	$(document).on('click', '.reaction_menu_item', function(event){
		event.preventDefault();
		event.stopPropagation();
		if(!reactionMenuScope || !reactionMenuTarget){
			return false;
		}
		var react = ($(this).attr('data-react-key') || '').toString();
		if(react == ''){
			return false;
		}
		reactMessage(reactionMenuScope, reactionMenuTarget, react);
	});

	$(document).on('click', '#reaction_picker_close', function(event){
		event.preventDefault();
		event.stopPropagation();
		closeReactionMenu();
	});

	$(document).on('click', function(event){
		if(!$(event.target).closest('#reaction_picker_menu, .msg_react_more, .log_react').length){
			closeReactionMenu();
		}
		if(!$(event.target).closest('#slash_command_menu, #content').length){
			hideSlashMenu();
		}
	});

	$(document).on('mousedown', '.slash_command_item', function(event){
		event.preventDefault();
		event.stopPropagation();
		selectSlashCommand($(this).attr('data-index'));
	});

	$(document).on('input focus', '#content', function(){
		updateSlashMenu($(this).val());
	});

	$(document).on('keydown', '#content', function(event){
		if(!$('#slash_command_menu:visible').length || slashCommandMatches.length < 1){
			return;
		}
		if(event.key === 'ArrowDown'){
			event.preventDefault();
			moveSlashSelection(1);
		}
		else if(event.key === 'ArrowUp'){
			event.preventDefault();
			moveSlashSelection(-1);
		}
		else if(event.key === 'Tab' || event.key === 'Enter'){
			event.preventDefault();
			selectSlashCommand(slashCommandActive);
		}
		else if(event.key === 'Escape'){
			event.preventDefault();
			hideSlashMenu();
		}
	});

	$(document).on('animationend', '.bubble[class*="cefx_"], .hunter_private[class*="cefx_"], .target_private[class*="cefx_"], .chat_effect_demo[class*="cefx_"]', function(event){
		var anim = '';
		if(event.originalEvent && event.originalEvent.animationName){
			anim = event.originalEvent.animationName;
		}
		if(anim != '' && !/^(cfx_pop|cfx_lift|cfx_swing|cfx_pulse|cfx_tilt|cfx_jelly|cfx_bloom|cfx_rocket|cfx_wave|cfx_slam|cfx_flip|cfx_volt|cfx_rift|cfx_hammer|cfx_comet|cfx_prism|cfx_duel)$/.test(anim)){
			return;
		}
		var matches = this.className.match(/cefx_\d+/g);
		if(matches && matches.length){
			$(this).removeClass(matches.join(' '));
		}
		$(this).removeClass('cfx_preview_live cefx_link');
	});
	
	$(document).on('click', '.ch_logs .emocc', function(){
		emoticon('content', $(this).attr('data'));
	});
	
	$(document).on('click', '.private_logs .emocc', function(){
		emoticon('message_content', $(this).attr('data'));
	});
	
	$(document).on('click', '.memot', function(){
		emoticon('content', $(this).attr('data'));
	});
	
	$(document).on('click', '.pemot', function(){
		emoticon('message_content', $(this).attr('data'));
	});
	
	$(document).on('click', '.wemot', function(){
		emoticon('friend_post', $(this).attr('data'));
		hidePostEmoticon();
	});
	
	$(document).on('click', '.nemot', function(){
		emoticon('news_data', $(this).attr('data'));
		hidePostEmoticon();
	});
	
	$(document).on('click', '.show_post', function() {
		var item = $(this).attr('data');
		showPost(item);
	});
	
	$('#main_input').submit(function(event){
		hideSlashMenu();
		var message = $('#content').val();
		if(message == ''){
			event.preventDefault();
		}
		else if (/^\s+$/.test(message)){
			event.preventDefault();
			chatInput();
		}
		else{
			chatInput();
			if(waitReply == 0){
				waitReply = 1;
				if(message.match("^\/") ){
					processChatCommand(message);
				}
				else {
					processChatPost(message);
				}
			}
			else {
				event.preventDefault();
			}
		}
		return false;
	});
	
	$(document).on('click', '.avitem', function(){
		resetAvMenu();
	});
	
	$(document).on('click', '.closesmilies', function(){
		if(!emoMainLocked()){
			$('#main_emoticon').toggle();
		}
	});
	$(document).on('click', '.closesmilies_priv', function(){
		if(!emoPrivLocked()){
			$('#private_emoticon').toggle();
		}
	});
	
	$(document).on('click', '#content, #submit_button', function(){
		hideEmoticon();
		closeChatSub();
		resetAvMenu();
		resetLogMenu();
	});
	$(document).on('click', '#message_content, #private_send', function(){
		hidePrivEmoticon();
		closePrivSub();
	});
	
	$(document).on('click', '.sub_options', function(){
		closeChatSub();
	});
	$(document).on('click', '.psub_options', function(){
		closePrivSub();
	});
	
	$(document).on('click', '.panel_option', function(){
		$('.panel_option').removeClass('bselected');
		$(this).addClass('bselected');
	});
	
	$(document).on('click', '.emo_menu_item', function(){
		var thisEmo = $(this).attr('data');
		var emoSelect = $(this);
		$.post('system/action/emoticon.php', { 
			get_emo: thisEmo,
			type: 1,
			}, function(response) {
				$('#main_emo').html(response);
				$('.emo_menu_item').removeClass('bselected');
				emoSelect.addClass('bselected');
		});
	});
	
	$(document).on('click', '.emo_menu_item_priv', function(){
		var thisEmo = $(this).attr('data');
		var emoSelect = $(this);
		$.post('system/action/emoticon.php', { 
			get_emo: thisEmo,
			type: 2,
			}, function(response) {
				$('#private_emo').html(response);
				$('.emo_menu_item_priv').removeClass('bselected');
				emoSelect.addClass('bselected');
		});
	});
	
	
	$(document).on('click', '.gprivate', function(){
		if($('#private_menu:visible').length){
			hideMenu('private_menu');
		}
		morePriv = 0;
		closeList();
		hideModal();
		hideOver();
		lastPriv = 0;
		var thisPrivate = $(this).attr('data');
		var thisUser = $(this).attr('value');
		var thisAvatar = $(this).attr('data-av');
		openPrivate(thisPrivate, thisUser, thisAvatar);
	});
	
	$(document).on('click', '.delete_private', function(){
		var toDelete = $(this).attr('data');
		var toClear = $(this);
		$.post('system/action/action_chat.php', { 
			private_delete: toDelete,
			}, function(response) {
				if(response == 1){
					toClear.parent().replaceWith("");
					if( $('.priv_mess').length < 1 && $('#private_menu:visible').length){
						hideMenu('private_menu');
					}
				}
				else {
					return false;
				}
		});
	});
	
	$('#private_input').submit(function(event){
		var message = $('#message_content').val();
		$('#message_content').val('');
		if(message == ''){
			pWait = 0;
			event.preventDefault();
		}
		else if (/^\s+$/.test(message)){
			pWait = 0;
			event.preventDefault();
		}
		else{
			if(pWait == 0){
				pWait = 1;
				$.ajax({
					url: "system/action/private_process.php",
					type: "post",
					cache: false,
					dataType: 'json',
					data: { 
						target: currentPrivate,
						content: message,
						quote: getPrivateQuote(),
					},
					success: function(response){
						if(typeof response != 'object'){
							pwait = 0;
						}
						else {
							if(response.code == 1) {
								if(response.log !== null){
									appendSelfPrivateMessage(response.log);
									$('#message_content').focus();
								}
							}
							else if(response.code == 99) {
								appendCannotPrivate();
							}
							pWait = 0;
						}
					},
					error: function(){
						pwait = 0;		
					}
				});
			}
			else {
				event.preventDefault();
			}
		}
		return false;
	});
	
	$(document).on('click', '#save_room', function(){
		saveRoom();
	});
	
	$('body').css('overflow', 'hidden');
	
	$(function() {
		if($(window).width() > 1024){
			$( "#private_center" ).draggable({
				handle: "#private_name",
				containment: "document",
			});
			dragger = 1;
		}
	});

// Handle goofy events delivered by the poll
handleGoofyEvents = function(events){
	if(!Array.isArray(events) || events.length === 0) return;
	for(var i=0;i<events.length;i++){
		var ev = events[i];
		if(!ev.type) continue;
		switch(ev.type){
			case 'announce':
				showGoofyAnnouncement(ev.data, ev.drag);
				break;
			case 'jumpscare':
				showGoofyJumpscare(ev.data, ev.drag);
				break;
			case 'audio':
				playGoofyAudio(ev.data);
				break;
			case 'goofy':
				triggerGoofyBurst(ev.data);
				break;
			default:
				break;
		}
	}
}

showGoofyAnnouncement = function(data, drag){
	var text = data.text || '';
	$('#goofy_event_text').text(text);
	$('#goofy_event_box').show();
	if(drag && $("#goofy_event_box").draggable){
		$("#goofy_event_box").draggable({ handle: '#goofy_event_handle', containment: 'document' });
	}
	setTimeout(function(){ $('#goofy_event_box').fadeOut(); }, 10000);
}

showGoofyJumpscare = function(data, drag){
	var img = data.image || '';
	var text = data.text || '';
	var audio = data.audio || '';
	if(img !== ''){
		$('#goofy_jumpscare_img').attr('src', img);
		$('#goofy_jumpscare_text').text(text);
		$('#goofy_jumpscare').show();
		if(drag && $("#goofy_jumpscare").draggable){
			$("#goofy_jumpscare").draggable({ containment: 'document' });
		}
		if(audio !== ''){
			$('#goofy_audio_player').attr('src', audio);
			$('#goofy_audio_player')[0].play();
		}
		setTimeout(function(){ $('#goofy_jumpscare').fadeOut(); }, 8000);
	}
}

playGoofyAudio = function(data){
	var audio = data.audio || '';
	if(audio !== ''){
		$('#goofy_audio_player').attr('src', audio);
		try{ $('#goofy_audio_player')[0].play(); }catch(e){}
	}
}

triggerGoofyBurst = function(data){
	var flags = data.flags || {};
	if(flags.effects){
		// spawn a few random chat FX on the screen
		for(var k=0;k<6;k++){
			var cls = 'cefx_' + (Math.floor(Math.random()*16)+1);
			var $b = $('<div class="goofy_fx_demo '+cls+'" style="position:fixed;left:'+ (20+Math.random()*60) +'%;top:'+ (20+Math.random()*60) +'%;z-index:99999;width:120px;height:40px;background:#fff;border-radius:8px;opacity:.95"></div>');
			$('body').append($b);
			(function(el){ setTimeout(function(){ $(el).remove(); }, 2500); })( $b );
		}
	}
	if(flags.shake){
		$('body').addClass('goofy_shake');
		setTimeout(function(){ $('body').removeClass('goofy_shake'); }, 1500);
	}
	if(flags.spin){
		$('.glob_av, .avatar, .avatar_preview, .glob_av_big').addClass('goofy_spin');
		setTimeout(function(){ $('.glob_av, .avatar, .avatar_preview, .glob_av_big').removeClass('goofy_spin'); }, 3500);
	}
}
	
	$('#show_chat ul').scroll(function() {
		var s = $('#show_chat ul').scrollTop();
		var c = $('#show_chat ul').innerHeight();
		var d = $('#show_chat ul')[0].scrollHeight;
		if(s + c >= d - 100){
			$('#show_chat').attr('value', 1);
		}
		else {
			$('#show_chat').attr('value', 0);
		}
		
	});
	
	$('#show_private').scroll(function() {
		var s = $('#show_private').scrollTop();
		var c = $('#show_private').innerHeight();
		var d = $('#show_private')[0].scrollHeight;
		if(s + c >= d - 100){
			$('#private_content').attr('value', 1);
		}
		else {
			$('#private_content').attr('value', 0);
		}
		
	});
		
	$('#show_chat ul').on('scroll', function() {
		var el = $(this);
		var distanceFromBottom = el[0].scrollHeight - (el.scrollTop() + el.innerHeight());

		if (distanceFromBottom > 200) {
			$('#back_to_bottom').fadeIn();
		} else {
			$('#back_to_bottom').fadeOut();
		}
	});
	
	$('#back_to_bottom').on('click', function(){
		$('#show_chat ul').animate({ scrollTop: $('#show_chat ul')[0].scrollHeight }, 300);
	});

	var waitScroll = 0;
	$('#show_chat ul').scroll(function() {
		if(moreMain == 1 && $('#show_chat ul .chat_log').length != 0){
			var pos = $('#show_chat ul').scrollTop();
			if (pos == 0) {
				if(waitScroll == 0){
					waitScroll = 1;
					var lastlog = $('#show_chat ul .chat_log').eq(0).attr('id');
					lastget = lastlog.replace('log', '');	
					$.ajax({
						url: "system/action/action_log.php",
						type: "post",
						cache: false,
						dataType: 'json',
						data: { 
							more_chat: lastget,
						},
						success: function(response)
						{
							var ccount = response.total;
							var newLogs = response.clogs;
							
							appendChatHistory(newLogs);

							if(ccount < 60){
								moreMain = 0;
							}
							$("#"+lastlog).get(0).scrollIntoView();
							waitScroll = 0;
						},
					});		
				}
				else {
					return false;
				}
			}
		}
	});
	
	var waitpScroll = 0;
	$('#show_private').scroll(function() {
		if(morePriv == 1){
			var pos = $('#show_private').scrollTop();
			if (pos == 0) {
				if(waitpScroll == 0){
					waitpScroll = 1;
					var lprivate = $('#show_private li').eq(0).attr('id');
					lastgetp = lprivate.replace('priv', '');	
					$.ajax({
						url: "system/action/action_log.php",
						type: "post",
						cache: false,
						dataType: 'json',
						data: { 
							more_private: lastgetp,
							target: currentPrivate,
						},
						success: function(response)
						{
							var prcount = response.total;
							var newpLogs = response.clogs;

							appendPrivateHistory(newpLogs);
							
							if(prcount < 30){
								morePriv = 0;
							}
							$("#"+lprivate).get(0).scrollIntoView();
							waitpScroll = 0;
						},
					});		
				}
				else {
					return false;
				}
			}
		}
	});

	$(document).on('click', '.user_choice', function() {	
		var curColor = $(this).attr('data');
		if($('.color_choices').attr('data') == curColor){
			$('.bccheck').replaceWith("");
			$('.color_choices').attr('data', '');
		}
		else {
			$('.bccheck').replaceWith("");
			$(this).append('<i class="fa fa-check bccheck"></i>');
			$('.color_choices').attr('data', curColor);
		}
		previewText();
	});
	
	$(document).on('change', '#boldit', function(){		
		previewText();
	});
	
	$(document).on('change', '#fontit', function(){		
		previewText();
	});
	
	$(document).on('click', '.more_left', function(){		
		$('#more_menu_list').toggle();
		closeLeft();
	});

	$(document).on('keydown', function(event) {
		if( event.which === 13 && event.ctrlKey && event.altKey ) {
			getMonitor();
		}
	});

	$(document).on('click', '.menu_header', function() {
		if ($('.menu_drop:visible').length){
			$(".menu_drop").fadeOut(100);
		}
		else {
			$(".menu_drop").fadeIn(200);
		}
		$("#wrap_options").fadeOut(100);
	});
	
	$(document).on('click', '.other_panels, .addon_button, .head_li, #content', function(){
		$(".menu_drop, #wrap_options").fadeOut(100);
	});
	
	var addons = '';
	
	$( window ).resize(function() {
		adjustHeight();
		adjustPrivate();
		resizeScroll();
		hidePanel();
		resetAvMenu();
	});
	
	$(document).on('change, paste, keyup', '#search_friend', function(){
		var searchFriend = $(this).val().toLowerCase();
		if(searchFriend == ''){
			$("#container_friends .user_item").each(function(){
				$(this).show();
			});	
		}
		else {
			$("#container_friends .user_item").each(function(){
				var fdata = $(this).find('.username').text().toLowerCase();
				if(fdata.indexOf(searchFriend) < 0){
					$(this).hide();
				}
				else if(fdata.indexOf(searchFriend) > 0){
					$(this).show();
				}
			});
		}
	});
	
	$(document).on('click', '.open_addons', function(){		
		$('#addons_chat_list').toggle();
	});
	
	$(document).on('click', '.post_video_save', function(){		
		var vlink = $(this).attr('data');
		$(this).removeClass('post_video_save').addClass('post_video').html('<video preload="auto" src="'+vlink+'" controls></video>');
	});
	
	$(document).on('click', '.post_audio_save', function(){		
		var vlink = $(this).attr('data');
		$(this).removeClass('post_audio_save').addClass('post_audio').html('<audio autoplay src="'+vlink+'" controls></audio>');
	});
	
	$(document).on('click', '.post_menu_item', function(){		
		$(this).parent('.post_menu').hide();
	});

	$(document).on('click', '#news_file, #wall_file, #news_data, #friend_post', function(){
		hidePostEmoticon()
	});
	
	$(document).on('change', '#usearch_type, #usearch_order', function() {
		var evSearchVal = $(this).val();
		searchUser();
	});
	$(document).on('keyup', '#usearch_input', function() {
		searchUser();
	});
	
	$(document).on('submit', '.friend_reply_form', function(){
		event.preventDefault();
		var item = $(this).children('input');
		var id = $(this).attr('data-id');
		postReply(id, item);
	});
	$(document).on('submit', '.news_reply_form', function(){
		event.preventDefault();
		var item = $(this).children('input');
		var id = $(this).attr('data-id');
		newsReply(id, item);
	});
	
	$(document).on('click', '#private_close', function(){
		currentPrivate = 0;
		$('#private_name').text('');
		$('#private_center').addClass('privhide');
		lastPriv = 0;
	});
	
	const classes = ['chat_message', 'chat_system', 'target_private', 'hunter_private', 'cqmess'];
	var curFont = 0;
	
});