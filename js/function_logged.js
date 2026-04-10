previewName = function(c){
	var n = $('.user_color').attr('data');
	var f = $('#fontitname').val();
	$('#preview_name').removeClass();
	$('#preview_name').addClass(n+' '+f);
}
var waitGuest = 0;
registerGuest = function() {
	var gname = $('#new_guest_name').val();
	var gpass = $('#new_guest_password').val();
	var gemail = $('#new_guest_email').val();
	if(gname == '' || gpass == '' || gemail == ''){
		callError(system.emptyField);
		return false;
	}
	else if (/^\s+$/.test($('#new_guest_name').val())){
		callError(system.emptyField);
		$('#new_guest_name').val("");
		return false;
	}
	else if (/^\s+$/.test($('#new_guest_password').val())){
		callError(system.emptyField);
		$('#new_guest_password').val("");
		return false;
	}
	else if (/^\s+$/.test($('#new_guest_email').val())){
		callError(system.emptyField);
		$('#new_guest_email').val("");
		return false;
	}
	else {
		if(waitGuest == 0){
			waitGuest = 1;
			$.post('system/action/action_member.php', {
				new_guest_name: gname,
				new_guest_password: gpass,
				new_guest_email: gemail,
				}, function(response) {
					if (response == 1){
						location.reload();
					}
					else if (response == 4){
						callError(system.invalidUsername);
						$('#new_guest_name').val("");
					}
					else if (response == 5){
						callError(system.usernameExist);
						$('#new_guest_name').val("");
					}
					else if (response == 6){
						callError(system.invalidEmail);
						$('#new_guest_email').val("");
					}
					else if (response == 10){
						callError(system.emailExist);
						$('#new_guest_email').val("");
					}
					else if (response == 16){
						callError(system.maxReg);
					}
					else if (response == 17){
						callError(system.invalidPass);
						$('#new_guest_password').val("");
					}
					else if(response == 0){
						callError(system.registerClose);
					}
					else {
						waitGuest = 0;
						return false;
					}
					waitGuest = 0;
			});
		}
		else{
			return false;
		}
	}
}
var waitSecure = 0;
secureAccount = function() {
	var sname = $('#secure_name').val();
	var spass = $('#secure_password').val();
	var semail = $('#secure_email').val();
	if(sname == '' || spass == '' || semail == ''){
		callError(system.emptyField);
		return false;
	}
	else if (/^\s+$/.test($('#secure_name').val())){
		callError(system.emptyField);
		$('#secure_name').val("");
		return false;
	}
	else if (/^\s+$/.test($('#secure_password').val())){
		callError(system.emptyField);
		$('#secure_password').val("");
		return false;
	}
	else if (/^\s+$/.test($('#secure_email').val())){
		callError(system.emptyField);
		$('#secure_email').val("");
		return false;
	}
	else {
		if(waitSecure == 0){
			waitSecure = 1;
			$.post('system/action/action_secure.php', {
				secure_name: sname,
				secure_password: spass,
				secure_email: semail,
				}, function(response) {
					if (response == 1){
						location.reload();
					}
					else if(response == 99){
						callError(system.error);
						$('#secure_password').val("");
						$('#secure_name').val("");
						$('#secure_email').val("");	
					}
					else if (response == 4){
						callError(system.invalidUsername);
						$('#secure_name').val("");
					}
					else if (response == 5){
						callError(system.usernameExist);
						$('#secure_name').val("");
					}
					else if (response == 6){
						callError(system.invalidEmail);
						$('#secure_email').val("");
					}
					else if (response == 10){
						callError(system.emailExist);
						$('#secure_email').val("");
					}
					else if (response == 16){
						callError(system.maxReg);
					}
					else if (response == 17){
						callError(system.invalidPass);
						$('#secure_password').val("");
					}
					else if(response == 0){
						callError(system.registerClose);
					}
					else {
						waitSecure = 0;
						return false;
					}
					waitSecure = 0;
			});
		}
		else{
			return false;
		}
	}
}
verifyAccount = function(){
	$('.resend_hide').hide();
	$.post('system/action/action_member.php', {
		send_verify: 1,
		}, function(response){	
		if(response == 1){
			callSuccess(system.emailSent);
		}
		else if(response == 3){
			callError(system.somethingWrong);
		}
		else {
			callError(system.oops);
		}
	});
}
boomSound = function(snd){
	if(uSound.match(snd)){
		return true;
	}
}
resetVerify = function(){
	$('#verify_one').show();
	$('#verify_two').hide();
}
toggleVerify = function(){
	$('#verify_one').hide();
	$('#verify_two').show();
}
validCode = function(type){
	var vCode = $('#boom_code').val();
	if (/^\s+$/.test(vCode) || vCode == ''){
		callError(system.emptyField);
	}
	else {
		$.post('system/action/action_member.php', {
			valid_code: vCode,
			verify_code:1,
			}, function(response) {	
			if(response == 0){
				callError(system.invalidCode);
			}
			else if(response == 1){
				if(type == 1){
					location.reload();
				}
				if(type == 2){
					$('#not_verify').replaceWith("");
					$('#verify_hide').replaceWith("");
					$('#now_verify').show();
				}
			}
			else {
				callError(system.somethingWrong);
			}
			$('#boom_code').val('');
		});
	}
}
var modalList = [];
scanModal = function(v){
	if('modal' in v){
		var m = v.modal;
		if(m.length > 0){
			for (var i = 0; i < m.length; i++){
				modalList.push(m[i]);
			}
		}
	}
}
registerModal = function(v){
	if('content' in v && 'type' in v && 'size' in v){
		modalList.push(v);
	}
}
checkModal = function(){
	if(systemLoaded == 0 || modalList.length === 0 || $('.modal_back:visible').length){
		return false;
	}
	else {
		var m = modalList.shift();
		if(m.type == 'modal'){
			showModal(m.content, m.size);
			callSound(m.sound);
		}
		else if(m.type == 'empty'){
			showEmptyModal(m.content, m.size);
			callSound(m.sound);
		}
	}
}
editProfile = function(){
	$.post('system/box/edit_profile.php', {
		}, function(response) {
			showEmptyModal(response, 520);
	});
}
storeArray = function(key, value) {
	localStorage.setItem(key, JSON.stringify(value));
}

getArray = function(key) {
	var stored = localStorage.getItem(key);
	if(stored != null) {
		return JSON.parse(stored);
	}
	else {
		return [];
	}
}
setArray = function(key, value){
	var arr = getArray(key);
	arr.push(value);
	storeArray(key, arr);
}

setUserTheme = function(item){
	var theme = $(item).attr('data-theme');
	$.ajax({
		url: "system/action/action_profile.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			set_user_theme: theme,
		},
		success: function(response){
			$("#actual_theme").attr("href", "css/themes/" + response.theme + "/" + response.theme + ".css"+bbfv);
			$('#main_logo').attr('src', response.logo);
		},
	});
}
saveUserSound = function(){
	boomDelay(function() {
		$.ajax({
			url: "system/action/action_profile.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				change_sound: 1,
				chat_sound: $('#set_chat_sound').attr('data'),
				private_sound: $('#set_private_sound').attr('data'),
				notify_sound: $('#set_notification_sound').attr('data'),
				name_sound: $('#set_username_sound').attr('data'),
				call_sound: $('#set_call_sound').attr('data'),
			},
			success: function(response){
				if(response.code == 1) {
					uSound = response.data;
				}
				else {
					return false;
				}
			},
			error: function(){
				return false;
			}
		});
	}, 500);
}
systemLoad = function(){
	$.ajax({
		url: "system/action/system_load.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: {},
		success: function(response){
			scanModal(response);
		},
		error: function(){
		}
	});
}
logOut = function(){
	$.post('system/action/logout.php', { 
		logout_from_system: 1,
		}, function(response) {
			if(response == 1){
				location.reload();
			}
	});
}
saveMood = function(){
	$.post('system/action/action_profile.php', { 
		save_mood: $('#set_mood').val(),
		}, function(response) {
			if(response == 0){
				callError(system.error);
				hideOver();
			}
			else if(response == 2){
				callError(system.restrictedContent);
			}
			else {
				callSuccess(system.saved);
				hideOver();
			}
	});	
}
saveInfo = function(){
	$.ajax({
		url: "system/action/action_profile.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			save_info: 1,
			age: $('#set_profile_age').val(),
			gender: $('#set_profile_gender').val(),
		},
		success: function(response) {
			if(response.code == 1){
				$('.avatar_profile').attr('src', response.av);
				$('.avatar_profile').attr('href', response.av);
				$('.glob_av').attr('src', response.av);
				callSuccess(system.saved);
				hideOver();
			}
			else if(response.code == 3){
				callError(system.ageRequirement);
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
saveShare = function(){
	boomDelay(function() {
		$.post('system/action/action_profile.php', { 
				save_shared: 1,
				ashare: $('#set_ashare').attr('data'),
				sshare: $('#set_sshare').attr('data'),
				fshare: $('#set_fshare').attr('data'),
				gshare: $('#set_gshare').attr('data'),
				lshare: $('#set_lshare').attr('data'),
			}, function(response) {
		});
	}, 500);
}
saveAbout = function(){
	$.post('system/action/action_profile.php', { 
		save_about: '1',
		about: $('#set_user_about').val(),
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
				hideOver();
			}
			else if(response == 2){
				callError(system.restrictedContent);
			}
			else if(response == 0){
				callError(system.error);
			}
			else {
				return false;
			}
	});	
}
saveEmail = function(){
	$.post('system/action/action_secure.php', { 
		save_email: '1',
		email: $('#set_profile_email').val(),
		password: $('#email_password').val(),
		}, function(response) {
			if(response == 2){
				callError(system.invalidEmail);
			}
			else if(response == 3){
				callError(system.wrongPass);
				$('#email_password').val('');
			}
			else if(response == 4){
				callError(system.emailExist);
			}
			else if(response == 1){
				callSuccess(system.saved);
				hideOver();
			}
			else {
				callError(system.error);
			}
	});	
}
changePassword = function(){
	var actual = $('#set_actual_pass').val();
	var newPass = $('#set_new_pass').val();
	var newRepeat = $('#set_repeat_pass').val();
	$.post('system/action/action_secure.php', { 
		actual_pass: actual,
		new_pass: newPass,
		repeat_pass: newRepeat,
		change_password: 1,
		}, function(response) {
			if(response == 2){
				callError(system.emptyField);
			}
			else if(response == 3){
				callError(system.notMatch);
			}
			else if(response == 4){
				callError(system.invalidPass);
			}
			else if(response == 5){
				callError(system.badActual);
			}
			else if(response == 1){
				callSuccess(system.saved);
				hideOver();
			}
			else {
				callError(system.error);
				hideOver();
			}
	});
}
deleteMyAccount = function(){
	$.post('system/action/action_secure.php', { 
		delete_my_account: '1',
		delete_account_password: $('#delete_account_password').val(),
		}, function(response) {
			if(response == 2){
				callError(system.wrongPass);
				$('#delete_account_password').val('');
			}
			else if(response == 1){
				callSuccess(system.saved);
				$('#del_account_btn').replaceWith("");
				hideOver();
			}
			else {
				callError(system.error);
			}
	});	
}
cancelDelete = function(){
	$.post('system/action/action_secure.php', { 
		cancel_delete_account: '1',
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
				$('#delete_warn').replaceWith("");
			}
	});	
}
saveLocation = function(){
	$.post('system/action/action_profile.php', {
		user_timezone: $('#set_profile_timezone').val(),
		user_language: $('#set_profile_language').val(),
		user_country: $('#set_profile_country').val(),
		}, function(response) {
			if(response == 1){
				location.reload();
			}
			else {
				callSuccess(system.saved);
			}
	});
}
savePreference = function(){
	var saveBubble = $('#set_user_bubble').val();
	$.post('system/action/action_profile.php', {
		save_preference: 1,
		save_ulogin: $('#set_ulogin').val(),
		set_private_mode: $('#set_private_mode').val(),
		set_user_call: $('#set_user_call').val(),
		set_ufriend: $('#set_ufriend').val(),
		set_user_bubble: saveBubble,
		set_pmusic: $('#set_pmusic').val(),
		}, function(response) {
			if(response == 0){
				callError(system.error);
				hideOver();
			}
			else if(response == 1){
				callSuccess(system.saved);
				ububble = saveBubble;
			}
	});	
}
getProfile = function(profile){
	hideOver();
	$.post('system/box/profile.php', {
		get_profile: profile,
		}, function(response) {
			if(response == 1){
				return false;
			}
			else if(response == 2){
				callError(system.noUser);
			}
			else {
				showEmptyModal(response,520);
			}
	});
}
getBadgeInfo = function(){
	hideOver();
	$.post('system/box/badge_info.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response,540);
			}
	});
}
getDisplaySetting = function(){
	$.post('system/box/display.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
getPublicThemeHub = function(){
	if(typeof getPublicThemeLeft === 'function'){
		getPublicThemeLeft('market');
		return;
	}
	$.post('system/box/public_theme_hub.php', {
		}, function(response) {
			if(response == 0 || response == ''){
				callError(system.error);
				return;
			}
			overModal(response, 980);
			setTimeout(function(){
				initPublicThemeBuilder();
			}, 40);
	});
}
showPublicThemeMarket = function(){
	$('#public_theme_view_builder').addClass('fhide');
	$('#public_theme_view_market').removeClass('fhide');
	$('#public_theme_nav_builder').removeClass('theme_btn active').addClass('default_btn');
	$('#public_theme_nav_market').removeClass('default_btn').addClass('theme_btn active');
}
showPublicThemeBuilder = function(){
	if(!$('#public_theme_view_builder').length){
		return;
	}
	$('#public_theme_view_market').addClass('fhide');
	$('#public_theme_view_builder').removeClass('fhide');
	$('#public_theme_nav_market').removeClass('theme_btn active').addClass('default_btn');
	$('#public_theme_nav_builder').removeClass('default_btn').addClass('theme_btn active');
	setTimeout(function(){
		initPublicThemeBuilder();
	}, 20);
}
publicThemeIsLocked = function(){
	if(!$('#public_theme_builder').length){
		return false;
	}
	if($('#public_theme_builder').attr('data-locked') == '1'){
		return true;
	}
	return false;
}
publicThemeColor = function(value, fallback){
	if(/^#[0-9a-f]{6}$/i.test(String(value))){
		return String(value).toUpperCase();
	}
	return fallback;
}
publicThemeClamp = function(value, min, max, fallback, precision){
	var num = parseFloat(value);
	if(isNaN(num)){
		num = fallback;
	}
	if(num < min){
		num = min;
	}
	if(num > max){
		num = max;
	}
	if(precision <= 0){
		return Math.round(num);
	}
	var pow = Math.pow(10, precision);
	return Math.round(num * pow) / pow;
}
publicThemeHexToRgba = function(hex, alpha){
	hex = publicThemeColor(hex, '#FFFFFF').replace('#', '');
	var r = parseInt(hex.substring(0, 2), 16);
	var g = parseInt(hex.substring(2, 4), 16);
	var b = parseInt(hex.substring(4, 6), 16);
	alpha = publicThemeClamp(alpha, 0, 1, 1, 2);
	return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
}
publicThemeCssSafe = function(css){
	css = String(css || '');
	css = css.replace(/<\/?(script|style)/gi, '');
	css = css.replace(/[<>]/g, '');
	if(css.length > 6000){
		css = css.substring(0, 6000);
	}
	return css;
}
collectPublicThemeData = function(){
	var themeName = $.trim($('#public_theme_name').val());
	if(themeName.length > 32){
		themeName = themeName.substring(0, 32);
	}
	if(themeName == ''){
		themeName = 'My Public Theme';
	}
	return {
		theme_name: themeName,
		header_bg: publicThemeColor($('#public_theme_header_bg').val(), '#111827'),
		header_text: publicThemeColor($('#public_theme_header_text').val(), '#FFFFFF'),
		chat_bg: publicThemeColor($('#public_theme_chat_bg').val(), '#0F172A'),
		chat_text: publicThemeColor($('#public_theme_chat_text').val(), '#E2E8F0'),
		bubble_bg: publicThemeColor($('#public_theme_bubble_bg').val(), '#1E293B'),
		accent: publicThemeColor($('#public_theme_accent').val(), '#38BDF8'),
		default_btn: publicThemeColor($('#public_theme_default_btn').val(), '#334155'),
		panel_opacity: publicThemeClamp($('#public_theme_panel_opacity').val(), 0.30, 1.00, 0.85, 2),
		panel_blur: publicThemeClamp($('#public_theme_panel_blur').val(), 0, 24, 8, 0),
		theme_background: $.trim($('#public_theme_bg').val()),
		theme_custom_css: publicThemeCssSafe($('#public_theme_custom_css').val())
	};
}
buildPublicThemeLiveCss = function(theme){
	var panelBg = publicThemeHexToRgba(theme.chat_bg, theme.panel_opacity);
	var bubbleSoft = publicThemeHexToRgba(theme.bubble_bg, Math.min(1, parseFloat(theme.panel_opacity) + 0.1));
	var lineSoft = publicThemeHexToRgba(theme.header_text, 0.14);
	var hoverSoft = publicThemeHexToRgba(theme.header_text, 0.10);
	var inputBg = publicThemeHexToRgba(theme.header_text, 0.06);
	var bg = String(theme.theme_background || '').replace(/'/g, '%27');
	var css = '';
	css += '@import url("css/themes/Lite/Lite.css' + bbfv + '");';
	css += 'a{color:' + theme.accent + ';}';
	css += 'body{background:' + theme.chat_bg + ';color:' + theme.chat_text + ';';
	if(bg != ''){
		css += "background-image:url('" + bg + "');background-size:cover;background-position:center center;background-attachment:fixed;";
	}
	css += '}';
	css += 'input,textarea,.post_input_container{background:' + inputBg + ';border:1px solid ' + lineSoft + ' !important;color:' + theme.chat_text + ';}';
	css += '.setdef,.default_color,.user{color:' + theme.chat_text + ';}';
	css += '.bhead,.bsidebar,.modal_top,.pro_top,.bfoot,.foot,.back_pmenu,.back_ptop{background:' + theme.header_bg + ';color:' + theme.header_text + ';}';
	css += '.theme_color,.menui,.subi{color:' + theme.accent + ';}';
	css += '.theme_btn,.back_theme,.my_notice{background:' + theme.accent + ';color:' + theme.header_text + ';}';
	css += '.default_btn,.back_default,.defaultd_btn,.send_btn{background:' + theme.default_btn + ';color:' + theme.header_text + ';}';
	css += '.backglob,.back_chat,.back_priv,.back_panel,.back_menu,.back_box,.back_input,.back_modal,.page_element,.back_quote{background:' + panelBg + ';color:' + theme.chat_text + ';}';
	css += '.mbubble,.hunter_private,.targ_quote,.reply_item,.cquote{background:' + bubbleSoft + ';color:' + theme.chat_text + ';}';
	css += '.my_log,.target_private,.hunt_quote{background:' + theme.bubble_bg + ';color:' + theme.header_text + ';}';
	css += '.chat_system,.sub_text,.sub_date,.input_item{color:' + lineSoft + ';}';
	css += '.bback,.bbackhover,.modal_mback{background:' + inputBg + ';}';
	css += '.bhover:hover,.bhoverr:hover,.bbackhover:hover,.blisting:hover,.submenu:hover,.bmenu:hover,.bpmenu:hover,.bsub:hover{background:' + hoverSoft + ';}';
	css += '.bborder,.tborder,.lborder,.rborder,.fborder,.blisting,.blist,.float_top,.float_ctop,.modal_mborder{border-color:' + lineSoft + ';}';
	css += '.bshadow,.page_element,.float_menu,.btnshadow,.pboxed,.tab_menu{box-shadow:0 8px 24px rgba(0,0,0,0.35);}';
	css += '.modal_back{background-color:rgba(0,0,0,0.55);}';
	if(parseInt(theme.panel_blur, 10) > 0){
		css += '.backglob,.back_chat,.back_priv,.back_panel,.back_menu,.back_box,.back_input,.back_modal,.page_element,.back_quote{backdrop-filter:blur(' + parseInt(theme.panel_blur, 10) + 'px);-webkit-backdrop-filter:blur(' + parseInt(theme.panel_blur, 10) + 'px);}';
	}
	if(theme.theme_custom_css != ''){
		css += theme.theme_custom_css;
	}
	return css;
}
applyPublicThemeLivePreview = function(){
	if(!$('#public_theme_builder').length){
		return;
	}
	var theme = collectPublicThemeData();
	var styleTag = $('#public_theme_live_style');
	if(!styleTag.length){
		$('head').append('<style id="public_theme_live_style"></style>');
		styleTag = $('#public_theme_live_style');
	}
	styleTag.text(buildPublicThemeLiveCss(theme));

	$('#public_theme_panel_opacity_value').text(theme.panel_opacity);
	$('#public_theme_panel_blur_value').text(theme.panel_blur + 'px');
	$('#public_theme_live_name').text(theme.theme_name);
	$('#public_theme_bg_state').text(theme.theme_background != '' ? 'Background ready' : 'No background uploaded');

	var preview = $('#public_theme_live_preview');
	if(preview.length){
		preview.css('--pt-header-bg', theme.header_bg);
		preview.css('--pt-header-text', theme.header_text);
		preview.css('--pt-chat-bg', theme.chat_bg);
		preview.css('--pt-chat-text', theme.chat_text);
		preview.css('--pt-bubble-bg', theme.bubble_bg);
		preview.css('--pt-accent', theme.accent);
		preview.css('--pt-default', theme.default_btn);
		preview.css('--pt-opacity', theme.panel_opacity);
		preview.css('--pt-blur', parseInt(theme.panel_blur, 10) + 'px');
		if(theme.theme_background != ''){
			preview.css('--pt-bg-url', "url('" + String(theme.theme_background).replace(/'/g, '%27') + "')");
		}
		else {
			preview.css('--pt-bg-url', 'none');
		}
	}
}
initPublicThemeBuilder = function(){
	if(!$('#public_theme_builder').length){
		return;
	}
	applyPublicThemeLivePreview();
}
savePublicThemeDraft = function(){
	if(publicThemeIsLocked()){
		callError('Approved themes are immutable.');
		return;
	}
	var payload = collectPublicThemeData();
	payload.save_public_theme = 1;
	$.post('system/action/action_public_theme.php', payload, function(response){
		if(response.code == 1){
			callSuccess('Draft saved.');
		}
		else if(response.code == 2){
			callError('Theme name must be at least 3 characters.');
		}
		else if(response.code == 4){
			callError('VIP or higher rank is required to publish.');
		}
		else if(response.code == 5){
			callError('Approved themes are immutable.');
		}
		else {
			callError(system.error);
		}
	}, 'json');
}
submitPublicTheme = function(){
	if(publicThemeIsLocked()){
		callError('Approved themes are immutable.');
		return;
	}
	var payload = collectPublicThemeData();
	payload.submit_public_theme = 1;
	$.post('system/action/action_public_theme.php', payload, function(response){
		if(response.code == 1){
			callSuccess('Theme submitted for moderation.');
			if(typeof getPublicThemeLeft === 'function'){
				getPublicThemeLeft('market');
			}
			else {
				getPublicThemeHub();
			}
		}
		else if(response.code == 2){
			callError('Theme name must be at least 3 characters.');
		}
		else if(response.code == 4){
			callError('VIP or higher rank is required to publish.');
		}
		else if(response.code == 5){
			callError('Approved themes are immutable.');
		}
		else {
			callError(system.error);
		}
	}, 'json');
}
uploadPublicThemeBackground = function(){
	if(publicThemeIsLocked()){
		callError('Approved themes are immutable.');
		return;
	}
	var input = $('#public_theme_bg_file')[0];
	if(!input || !input.files || !input.files.length){
		callError('Select an image file first.');
		return;
	}
	var fd = new FormData();
	fd.append('upload_public_theme_bg', 1);
	fd.append('token', utk);
	fd.append('cp', curPage);
	fd.append('theme_background_file', input.files[0]);
	$.ajax({
		url: 'system/action/action_public_theme.php',
		type: 'POST',
		data: fd,
		dataType: 'json',
		processData: false,
		contentType: false,
		success: function(response){
			if(response.code == 1){
				$('#public_theme_bg').val(response.background);
				$('#public_theme_bg_file').val('');
				applyPublicThemeLivePreview();
				callSuccess('Background uploaded.');
			}
			else if(response.code == 3){
				callError('Invalid image or file too large.');
			}
			else if(response.code == 4){
				callError('VIP or higher rank is required to publish.');
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
removePublicThemeBackground = function(){
	if(publicThemeIsLocked()){
		callError('Approved themes are immutable.');
		return;
	}
	$('#public_theme_bg').val('');
	$('#public_theme_bg_file').val('');
	applyPublicThemeLivePreview();
}
applyPublicTheme = function(themeId){
	$.post('system/action/action_public_theme.php', {
		apply_public_theme: 1,
		theme_id: themeId
	}, function(response){
		if(response.code == 1){
			var href = "css/themes/" + response.theme + "/" + response.theme + ".css" + bbfv;
			href += (href.indexOf('?') > -1 ? '&' : '?') + 'ptv=' + (response.tv ? response.tv : new Date().getTime());
			$("#actual_theme").attr("href", href);
			$('#main_logo').attr('src', response.logo);
			callSuccess('Theme applied.');
		}
		else if(response.code == 2){
			callError('Theme not available.');
		}
		else {
			callError(system.error);
		}
	}, 'json');
}
moderatePublicTheme = function(themeId, action){
	var payload = {
		moderate_public_theme: 1,
		theme_id: themeId,
		theme_action: action,
	};
	if(action == 'reject'){
		var note = $.trim($('#public_theme_mod_note_' + themeId).val());
		if(note == ''){
			callError('Add a rejection reason.');
			return;
		}
		payload.theme_note = note;
	}
	$.post('system/action/action_public_theme.php', payload, function(response){
		if(response.code == 1){
			callSuccess(action == 'approve' ? 'Theme approved and published.' : 'Theme rejected.');
			if(typeof getPublicThemeLeft === 'function'){
				getPublicThemeLeft('market');
			}
			else {
				getPublicThemeHub();
			}
		}
		else if(response.code == 3){
			callError('A rejection reason is required.');
		}
		else if(response.code == 4){
			callError('Permission denied for moderation.');
		}
		else {
			callError(system.error);
		}
	}, 'json');
}
deletePublicTheme = function(themeId){
	themeId = parseInt(themeId, 10);
	if(!themeId){
		return;
	}
	if(!confirm('Delete this public theme now?')){
		return;
	}
	$.post('system/action/action_public_theme.php', {
		delete_public_theme: 1,
		theme_id: themeId,
	}, function(response){
		if(response.code == 1){
			callSuccess('Theme deleted.');
			if(typeof getPublicThemeLeft === 'function'){
				getPublicThemeLeft('market');
			}
			else {
				getPublicThemeHub();
			}
		}
		else if(response.code == 4){
			callError('Permission denied for delete.');
		}
		else {
			callError(system.error);
		}
	}, 'json');
}
$(document).on('input change', '#public_theme_builder input, #public_theme_builder textarea', function(){
	if($(this).attr('id') == 'public_theme_bg_file'){
		return;
	}
	applyPublicThemeLivePreview();
});
getActions = function(id){
	$.post('system/box/action_main.php', {
		id: id,
		}, function(response) {
			if(response == 0){
				callError(system.cannotUser);
			}
			else if(response == 1){
			}
			else {
				overModal(response,400);
			}
	});
}
getPassword = function(){
	$.post('system/box/edit_password.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
getUserLogin = function(){
	$.post('system/box/edit_login.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
getOtherLogout = function(){
	$.post('system/box/other_logout.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
otherLogout = function(){
	$.post('system/action/logout.php', {
		other_logout: 1,
		}, function(response) {
			if(response == 1){
				callSuccess(system.actionComplete);
			}
	});
}
getFriends = function(){
	$.post('system/box/manage_friends.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getGift = function(){
	$.post('system/box/my_gift.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response,440);
			}
	});
}

$(document).on('click', '.view_gift', function(){
	$('#view_gift_title').text($(this).attr('data-title'));
	$('#view_gift_img').attr('src', $(this).attr('data-img'));
	$('#view_gift_id').attr('data', $(this).attr('data-gift'));
	topModal($('#view_gift_template').html(), 300);
});

$(document).on('click', '#gift_delete', function(){
	var gift = $('#view_gift_id').attr('data');
	$.post('system/box/gift_delete.php', {
			gift: gift,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				topModal(response, 300);
			}
	});
});

$(document).on('click', '#delete_mgift', function(){
	var gift = $(this).attr('data');
	$.post('system/action/action_gift.php', {
			delete_mgift: gift,
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				hideTop();
				$('#mgift'+gift).replaceWith("");
			}
	});
});

$(document).on('click', '.view_friend', function(){
	$('#view_friend_name').text($(this).attr('data-name'));
	$('#view_friend_avatar').attr('src', $(this).attr('data-avatar'));
	$('#view_friend_id').attr('data', $(this).attr('data-id'));
	overModal($('#view_friend_template').html(), 400);
});

getUserGift = function(id){
	var cgift = $('#progift').attr('value');
	if(cgift == 0){
		$.post('system/box/gift_view.php', {
				target: id,
			}, function(response) {
				if(response == 0){
					return false;
				}
				else {
					$('#progift').html(response).attr('value', 1);
				}
		});
	}
}
getUserFriend = function(id){
	var cfriend = $('#profriend').attr('value');
	if(cfriend == 0){
		$.post('system/box/friend_view.php', {
				target: id,
			}, function(response) {
				if(response == 0){
					return false;
				}
				else {
					$('#profriend').html(response).attr('value', 1);
				}
		});
	}
}
getIgnore = function(){
	$.post('system/box/manage_ignore.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getLocation = function(){
	$.post('system/box/location.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getPrivateSettings = function(){
	$.post('system/box/private_settings.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getCallSettings = function(){
	$.post('system/box/call_settings.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getPreference = function(){
	$.post('system/box/preference.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 460);
			}
	});
}
getVerify = function(){
	$.post('system/box/verify_account.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
getEmail = function(){
	$.post('system/box/edit_email.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
getDeleteAccount = function(){
	$.post('system/box/user_delete.php', {
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response, 500);
			}
	});
}
acceptFriend = function(t, friend){
	$.post("system/action/action_friend.php", { 
		add_friend: friend,
		}, function(response) {
			$(t).parent().replaceWith("");
			if($('.friend_request').length < 1 && $('#friends_menu:visible').length){
				hideMenu('friends_menu');
			}
	});
}
declineFriend = function(t, id){
	$.post("system/action/action_friend.php", {
		remove_friend: id,
		}, function(response) {
			$(t).parent().replaceWith("");
			if($('.friend_request').length < 1){
				hideMenu('friends_menu');
			}
	});
}
removeFriend = function(t, id){
	$.post('system/action/action_friend.php', { 
		remove_friend: id,
		}, function(response) {
			$(t).parent().replaceWith("");
			if($('.friend_request').length < 1 && $('#friends_menu:visible').length){
				hideMenu('friends_menu');
			}
	});
}
deleteIgnore = function(t, id){
	$.post('system/action/action_member.php', { 
		remove_ignore: id,
		}, function(response) {
			$(t).parent().replaceWith("");
			removeIgnore(id);
	});
}
addFriend = function(id){
	$.post("system/action/action_friend.php", {
		add_friend: id,
		}, function(response) {
			if(response == 4){
				callError(syste.actLimit);
			}
			else if(response != 3){
				callSuccess(system.actionComplete);
			}
			else {
				callError(system.error);
			}
			hideOver();
	});
}
unFriend = function(id){
	$.post('system/action/action_friend.php', { 
		remove_friend: id,
		}, function(response) {
			callSuccess(system.actionComplete);
			hideOver();
	});
}
ignoreUser = function(id){
	$.post('system/action/action_member.php', { 
		add_ignore: id,
		}, function(response) {
			if(response == 0){
				callError(system.cannotUser);
			}
			else if(response == 1){
				callSuccess(system.actionComplete);
				addIgnore(id);
			}
			else if(response == 2){
				callSuccess(system.actionComplete);
				addIgnore(id);
			}
			else {
				callError(system.error);
			}
			hideOver();
	});
}
unIgnore = function(id){
	$.post('system/action/action_member.php', { 
		remove_ignore: id,
		}, function(response) {
			callSuccess(system.actionComplete);
			removeIgnore(id);
			hideOver();
	});
}
ignoreThisUser = function(){
	ignoreUser(currentPrivate);
}
changeUsername = function(){
	$.post('system/box/edit_name.php', { 
		}, function(response) {
			overModal(response);
	});
}
changeInfo = function(){
	$.post('system/box/edit_info.php', { 
		}, function(response) {
			overModal(response);
	});
}
changeShared = function(){
	$.post('system/box/edit_shared.php', { 
		}, function(response) {
			overModal(response);
	});
}
changeAbout = function(){
	$.post('system/box/edit_about.php', { 
		}, function(response) {
			overModal(response, 500);
	});
}
getTextOptions = function(){
	$.post('system/box/chat_text.php', {
		}, function(response) {
			overModal(response);
	});
}
getAnimationSettings = function(){
	$.post('system/box/animation_settings.php', {
		}, function(response) {
			overModal(response, 420);
	});
}
getGoofyAdminPanel = function(){
	$.post('system/box/goofy_admin.php', {
		}, function(response) {
			if(response == 0){
				callError(system.error);
				return;
			}
			overModal(response, 560);
	});
}

saveAnimationSettings = function(){
	var master = $('#anim_master').val();
	var chatfx = $('#anim_chatfx').val();
	var goofy = $('#anim_goofy').val();
	var overlay = $('#anim_overlay').val();
	$.post('system/action/action_animation.php', {
		token: utk,
		cp: curPage,
		save_animation: 1,
		anim_master: master,
		anim_chatfx: chatfx,
		anim_goofy: goofy,
		anim_overlay: overlay,
	}, function(response){
		if(response.code == 1 && response.config){
			try{
				animMaster = response.config.master;
				animChatfx = response.config.chatfx;
				animGoofy = response.config.goofy;
				animOverlay = response.config.overlay;
			}catch(e){}
			hideOver();
			callSuccess(system.actionComplete);
		}
		else {
			callError(system.error);
		}
	}, 'json');
}

// goofy sends
sendGoofyAnnouncement = function(){
	var text = $('#goofy_announce_text').val();
	var dur = $('#goofy_announce_duration').val();
	var drag = $('#goofy_announce_drag').val();
	var mode = $('#goofy_announce_target_mode').val();
	var targets = $('#goofy_announce_targets').val();
	$.ajax({
		url: 'system/action/action_goofy.php',
		type: 'POST',
		dataType: 'json',
		data: {
			token: utk,
			cp: curPage,
			send_announce: 1,
			announce_text: text,
			announce_duration: dur,
			announce_drag: (drag==1?1:0),
			target_mode: mode,
			targets: targets,
			room: user_room,
		},
		success: function(response){
			if(response.code == 1){
				callSuccess(system.actionComplete);
				hideOver();
			}
			else if(response.code == 4){
				callError('Permission denied for this action.');
			}
			else if(response.code == 3){
				callError('Add valid target usernames for "some users" mode.');
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

sendGoofyJump = function(){
	var dur = $('#goofy_jump_duration').val();
	var drag = $('#goofy_jump_drag').val();
	var mode = $('#goofy_jump_target_mode').val();
	var targets = $('#goofy_jump_targets').val();
	var text = $('#goofy_jump_text').val();
	var fd = new FormData();
	fd.append('send_jump', 1);
	fd.append('token', utk);
	fd.append('cp', curPage);
	fd.append('jump_duration', dur);
	fd.append('jump_drag', (drag==1?1:0));
	fd.append('target_mode', mode);
	fd.append('targets', targets);
	fd.append('jump_text', text);
	var img = $('#goofy_jump_image')[0];
	if(img && img.files && img.files.length){ fd.append('jump_image', img.files[0]); }
	var aud = $('#goofy_jump_audio')[0];
	if(aud && aud.files && aud.files.length){ fd.append('jump_audio', aud.files[0]); }
	fd.append('room', user_room);
	$.ajax({
		url: 'system/action/action_goofy.php',
		type: 'POST',
		data: fd,
		dataType: 'json',
		processData: false,
		contentType: false,
		success: function(response){
			if(response.code == 1){ callSuccess(system.actionComplete); hideOver(); }
			else if(response.code == 4){ callError('Permission denied for this action.'); }
			else if(response.code == 3){ callError('Add valid target usernames for "some users" mode.'); }
			else { callError(system.error); }
		},
		error: function(){
			callError(system.error);
		}
	});
}

sendGoofyAudio = function(){
	var mode = $('#goofy_audio_target_mode').val();
	var targets = $('#goofy_audio_targets').val();
	var fd = new FormData();
	fd.append('send_audio', 1);
	fd.append('token', utk);
	fd.append('cp', curPage);
	fd.append('target_mode', mode);
	fd.append('targets', targets);
	var f = $('#goofy_audio_file')[0];
	if(f && f.files && f.files.length){ fd.append('audio_file', f.files[0]); }
	fd.append('room', user_room);
	$.ajax({
		url: 'system/action/action_goofy.php',
		type: 'POST',
		data: fd,
		dataType: 'json',
		processData: false,
		contentType: false,
		success: function(response){
			if(response.code == 1){ callSuccess(system.actionComplete); hideOver(); }
			else if(response.code == 4){ callError('Permission denied for this action.'); }
			else if(response.code == 3){ callError('Add valid target usernames for "some users" mode.'); }
			else { callError(system.error); }
		},
		error: function(){
			callError(system.error);
		}
	});
}

sendGoofyRandom = function(){
	var dur = $('#goofy_random_duration').val();
	var mode = $('#goofy_random_target_mode').val();
	var targets = $('#goofy_random_targets').val();
	var eff = $('#goofy_random_effect').is(':checked') ? 1 : 0;
	var shake = $('#goofy_random_shake').is(':checked') ? 1 : 0;
	var spin = $('#goofy_random_spin').is(':checked') ? 1 : 0;
	$.ajax({
		url: 'system/action/action_goofy.php',
		type: 'POST',
		dataType: 'json',
		data: {
			token: utk,
			cp: curPage,
			send_random: 1,
			random_duration: dur,
			random_effect: eff,
			random_shake: shake,
			random_spin: spin,
			target_mode: mode,
			targets: targets,
			room: user_room,
		},
		success: function(response){
			if(response.code == 1){ callSuccess(system.actionComplete); hideOver(); }
			else if(response.code == 4){ callError('Permission denied for this action.'); }
			else if(response.code == 3){ callError('Add valid target usernames for "some users" mode.'); }
			else { callError(system.error); }
		},
		error: function(){
			callError(system.error);
		}
	});
}
getEffectsShop = function(){
	$.post('system/box/effects.php', {
		}, function(response) {
			overModal(response, 980);
			$('#over_modal').addClass('effects_modal_backdrop');
			$('#over_modal_in').addClass('effects_modal_shell');
			$('#over_modal_content').addClass('effects_modal_content_shell');
			if(typeof initEffectsTabs === 'function'){
				initEffectsTabs();
			}
			if(typeof initEffectsPreview === 'function'){
				setTimeout(function(){
					initEffectsPreview();
				}, 40);
			}
	});
}
getSoundSetting = function(){
	$.post('system/box/sound.php', {
		}, function(response) {
			overModal(response, 380);
	});
}
changeColor = function(){
	$.post('system/box/edit_color.php', { 
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
openSecure = function(){
	$.post('system/box/secure_account.php', { 
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
openGuestRegister = function(){
	$.post('system/box/guest_register.php', { 
		}, function(response) {
			if(response == 0){
				return false;
			}
			else {
				overModal(response);
			}
	});
}
changeMood = function(){
	$.post('system/box/edit_mood.php', { 
		}, function(response) {
			overModal(response);
	});
}
changeMyUsername = function(){
	var myNewName = $('#my_new_username').val();
	$.post('system/action/action_profile.php', { 
		edit_username: 1,
		new_name: myNewName,
		}, function(response) {
			if(response == 1){
				$('.globname').text(myNewName);
				hideOver();
			}
			else if(response == 2){
				callError(system.invalidUsername);
				$('#my_new_username').val('');
			}
			else if(response == 3){
				callError(system.usernameExist);
				$('#my_new_username').val();
			}
			else if(response == 4){
				callError(system.actLimit);
				hideOver();
			}
			else {
				callError(system.error);
				hideOver();
			}
	});
}
saveNameColor = function(){
	$.post('system/action/action_profile.php', {
		my_username_color: $('.user_color').attr('data'),
		my_username_font: $('#fontitname').val(),
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
			}
			else {
				callError(system.error);
			}
	});
}
saveUserColor = function(u){
	$.post('system/action/action_users.php', {
		user_color: $('.user_color').attr('data'),
		user_font: $('#fontitname').val(),
		user: u,
		}, function(response) {
			if(response == 1){
				callSuccess(system.saved);
			}
			else {
				callError(system.error);
			}
	});	
}
openAddRoom = function(){
	$.post('system/box/create_room.php', {
		}, function(response) {
			overModal(response);
	});
}
openShareWallet = function(id){
	$.post('system/box/wallet_share.php', {
			target: id,
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else {
				overModal(response);
			}
	});
}
openSendGift = function(id){
	$.post('system/box/gift.php', {
			target: id,
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else {
				overModal(response, 460);
			}
	});
}
viewLevelStatus = function(id){
	$.post('system/box/level_status.php', {
			target: id,
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else {
				overModal(response, 480);
			}
	});
}
var waitShare = 0;
shareGold = function(id){
	if(waitShare == 0){
		waitShare = 1;
		$.post('system/action/action_member.php', {
				share_gold: id,
				shared_gold: $('#gold_shared').val(),
			}, function(response) {
				if(response == 1){
					callSuccess(system.actionComplete);
					hideOver();
				}
				else if(response == 2){
					callError(system.lowBalance);
				}
				else if(response == 3){
					callError(system.cannotUser);
					hideOver();
				}
				else if(response == 4){
					callError(system.invalidAmount);
				}
				else if(response == 5){
					callError(system.actLimit);
				}
				else {
					callError(system.error);
					hideOver();
				}
				waitShare = 0;
		});
	}
}
shareRuby = function(id){
	if(waitShare == 0){
		waitShare = 1;
		$.post('system/action/action_member.php', {
				share_ruby: id,
				shared_ruby: $('#ruby_shared').val(),
			}, function(response) {
				if(response == 1){
					callSuccess(system.actionComplete);
					hideOver();
				}
				else if(response == 2){
					callError(system.lowBalance);
				}
				else if(response == 3){
					callError(system.cannotUser);
					hideOver();
				}
				else if(response == 4){
					callError(system.invalidAmount);
				}
				else {
					callError(system.error);
					hideOver();
				}
				waitShare = 0;
		});
	}
}
roomBlockBox = function(id){
	$.post('system/box/room_block.php', {
		room_block: id,
		}, function(response) {
			if(response == 0){
				callError(system.cannotUser);
			}
			else {
				overModal(response);
			}
	});
}
roomMuteBox = function(id){
	$.post('system/box/room_mute.php', {
		room_mute: id,
		}, function(response) {
			if(response == 0){
				callError(system.cannotUser);
			}
			else {
				overModal(response);
			}
	});
}
actionResponse = function(r){
	if(r == 0){
		callError(system.cannotUser);
	}
	else if(r == 1){
		callSuccess(system.actionComplete);
	}
	else if (r == 2){
		callError(system.alreadyAction);
	}
	else if (r == 3){
		callError(system.noUser);
	}
	else {
		callError(system.error);
	}
}
roomMuteUser = function(target){
	$.post('system/action/action.php', {
		room_mute: target,
		delay: $('#room_mute_delay').val(),
		reason: $('#room_mute_reason').val(),
		}, function(response) {
			actionResponse(response);
			hideOver();
	});
}
roomBlockUser = function(target){
	$.post('system/action/action.php', {
		room_block: target,
		delay: $('#room_block_delay').val(),
		reason: $('#room_block_reason').val(),
		}, function(response) {
			actionResponse(response);
			hideOver();
	});
}

listAction = function(target, act){
	closeTrigger();
	if(act == 'ban'){
		banBox(target);
	}
	else if(act == 'kick'){
		kickBox(target);
	}
	else if(act == 'mute'){
		muteBox(target);
	}
	else if(act == 'main_mute'){
		mainMuteBox(target);
	}
	else if(act == 'private_mute'){
		privateMuteBox(target);
	}
	else if(act == 'ghost'){
		ghostBox(target);
	}
	else if(act == 'warn'){
		warnBox(target);
	}
	else if(act == 'room_mute'){
		roomMuteBox(target);
	}
	else if(act == 'room_block'){
		roomBlockBox(target);
	}
	else if(act == 'change_rank'){
		adminGetRank(target);
	}
	else if(act == 'room_rank'){
		openRoomRank(target);
	}
	else if(act == 'delete_account'){
		eraseAccount(target);
	}
	else {
		$.post('system/action/action.php', {
			take_action: act,
			target: target,
			}, function(response) {
				if(response == 0){
					callError(system.cannotUser);
				}
				else if(response == 1){
					hideOver();
					callSuccess(system.actionComplete);
					processAction(act);
					if(act == 'unghost'){
						$('.ghst'+target).replaceWith("");
					}
				}
				else if(response == 2){
					callError(system.alreadyAction);
				}
				else {
					callError(system.error);
				}
		});
	}
}
processAction = function(act){
	if(act == 'unmute'){
		$('.im_muted').replaceWith("");
	}
	else if(act == 'unban'){
		$('.im_banned').replaceWith("");
	}
}
removeRoomAction = function(elem, action, target){
	$.post('system/action/action.php', {
		take_action: action,
		target: target,
		}, function(response) {
			if(response == 1){
				$(elem).parent().replaceWith("");
			}
			else {
				callError(system.error);
			}
	});
}
appLeftMenu = function(aIcon, aText, aCall, optMenu) {
	renderLeftMenu(aIcon, aText, aCall, optMenu);
}
appSettingMenu = function(aIcon, aText, aCall){
	renderRightMenu(aIcon, aText, aCall, 'setting_menu_content');
}
appLeadMenu = function(aIcon, aText, aCall){
	renderSideMenu(aIcon, aText, aCall, 'leaderboard_menu_content', 'fmenu_img');
}
appGameMenu = function(aIcon, aText, aCall){
	renderSideMenu(aIcon, aText, aCall, 'game_menu_content', 'fmenu_gimg');
}
appAppMenu = function(aIcon, aText, aCall){
	renderSideMenu(aIcon, aText, aCall, 'app_menu_content', 'fmenu_aimg');
}
appStoreMenu = function(aIcon, aText, aCall){
	renderSideMenu(aIcon, aText, aCall, 'store_menu_content', 'fmenu_simg');
}
appToolMenu = function(aIcon, aText, aCall){
	renderSideMenu(aIcon, aText, aCall, 'tool_menu_content', 'fmenu_timg');
}
appHelpMenu = function(aIcon, aText, aCall){
	renderSideMenu(aIcon, aText, aCall, 'help_menu_content', 'fmenu_himg');
}
appPanelMenu = function(icon, text, pCall){
	var panMenu = '<div title="'+text+'" class="panel_option" onclick="'+pCall+'"><i class="fa fa-'+icon+'"></i></div>';
	$('#right_panel_bar').append(panMenu);
}
appInputMenu = function(mIcon, mCall){
	var inpMenu = '<div class="sub_options" onclick="'+mCall+'"><img src="'+mIcon+'"/></div>';
	$('#main_input_extra').append(inpMenu);
}
appPrivInputMenu = function(mIcon, mCall){
	var privInpMenu = '<div class="psub_options" onclick="'+mCall+'"><img src="'+mIcon+'"/></div>';
	$('#priv_input_extra').append(privInpMenu);
}
noDataTemplate = function(){
	return '<div class="pad_box"><p class="centered_element text_med sub_text">'+system.noResult+'</p></div>';
}
cleanData = function(){
	if(isStaff(user_rank)){
		$.ajax({
			url: "system/action/system_clean.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				clean_data: 1,
			},
			success: function(response){
				return false;
			},
			error: function(){
				return false;
			}
			
		});
	}
}
isStaff = function(urank){
	if(urank >= 70){
		return true;
	}
}
betterRank = function(urank){
	if(user_rank > urank){
		return true;
	}
}

removeRoomStaff = function(elem, target){
	$.post('system/action/action.php', {
		remove_room_staff: 1,
		target: target,
		}, function(response) {
			if(response == 1){
				$(elem).parent().replaceWith("");
			}
			else {
				callError(system.error);
			}
	});
}
joinRoomPassword = function(rt, rank){
	if(!boomAllow(rank)){
		callError(system.accessRequirement);
		return;
	}
	$.ajax({
		url: "system/action/action_room.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			pass: $('#pass_input').val(),
			room: rt,
			join_room_pass: 1,
		},
		success: function(response){
			if(response.code == 10){
				if(insideChat()){
					resetRoom(response.data);
					hideOver();
				}
				else {
					location.reload();
				}
			}
			else if(response.code == 5){
				callError(system.wrongPass);
				$('#pass_input').val('');
			}
			else if(response.code == 2){
				callError(system.accessRequirement);
			}
			else if(response.code == 99){
				callError(system.roomBlock);
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

var waitJoin = 0;
switchRoom = function(room, pass, rank){
	if(insideChat()){
		if(room == user_room){
			return;
		}
	}
	if(!boomAllow(rank)){
		callError(system.accessRequirement);
		return;
	}
	if(waitJoin == 0){
		waitJoin = 1;
		if(pass == 1){
			$.post('system/box/pass_room.php', {
				room_rank: rank,
				room_id: room,
				}, function(response) {
					overModal(response);
					waitJoin = 0;
			});
		}
		else {
			$.ajax({
				url: "system/action/action_room.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					room: room,
					join_room: 1,
				},
				success: function(response){
					if(response.code == 10){
						if(insideChat()){
							resetRoom(response.data);
						}
						else {
							location.reload();
						}
					}
					else if(response.code == 99){
						callError(system.roomBlock);
						waitJoin = 0;
					}
					else {
						waitJoin = 0;
						return false;
					}
				},
				error: function(){
					callError(system.error);	
					waitJoin = 0;
				}
			});			
		}
	}
	else {
		return false;
	}
}
var waitRoom = 0;
addRoom = function(){
	if(waitRoom == 0){
		waitRoom = 1;
		$.ajax({
			url: "system/action/action_room.php",
			type: "post",
			cache: false,
			dataType: 'json',
			data: { 
				set_name: $("#set_room_name").val(),
				set_type: $("#set_room_type").val(),
				set_pass: $("#set_room_password").val(),
				set_description: $("#set_room_description").val(),
				set_ricon: $('#set_room_icon').attr('data'),
			},
			success: function(response){
				if(response.code == 1){
					callError(system.error);
				}
				else if (response.code == 2){
					callError(system.roomName);
				}
				else if (response.code == 5){
					hideModal();
					callError(system.maxRoom);
				}
				else if (response.code == 6){
					callError(system.roomExist);
				}
				else if(response.code == 7){
					if(insideChat()){
						resetRoom(response.data);
					}
					else {
						location.reload();
					}
				}
				else {
					waitRoom = 0;
					return false;
				}
				waitRoom = 0;
			},
			error: function(){
				callError(system.error);	
			}
		});
	}
	else {
		return false;
	}	
}

// files functions 
var mupload;
var pupload;

uploadChatFile = function(){
	if($('#chat_file').val() === ''){
		return;
	}
	else {
		uploadChat($("#chat_file").prop("files")[0]);
	}
}

var waitUpload = 0;
uploadChat = function(f){
	var filez = (f.size / 1024 / 1024).toFixed(2);
	if( filez > fileMax ){
		callError(system.fileBig);
	}
	else {
		if(waitUpload == 0){
			uploadStatus('chat_file', 2);
			waitUpload = 1;
			var form_data = new FormData();
			form_data.append("file", f)
			form_data.append("token", utk)
			mupload = $.ajax({
				url: "system/action/file_chat.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				beforeSend: function(){
					startMainUp();
				},
				xhr: function() {
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(evt) {
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total;
							upMainStatus((Math.round(percentComplete * 100)));
						}
					}, false);
					return xhr;
				},
				success: function(response){
					if(response.code == 1){
						callError(system.wrongFile);
					}
					else if(response.code == 9){
						callError(system.fileBlocked);
					}
					else if(response.code == 5){
						appendSelfChatMessage(response.logs);
					}
					else {
						callError(system.error);
					}
					resetMainUp();
				},
				error: function(){
					resetMainUp();
				}
			})
		}
		else {
			return false;
		}
	}
}

// up functions controller

startMainUp = function(){
	upMainStatus(0);
	$('#main_progress').show();
}
upMainStatus = function(v){
	$('#mprogress').css('width', v+'%');
}
cancelMainUp = function(){
	mupload.abort();
}
resetMainUp = function(){
	$('#main_progress').hide();
	$("#chat_file").val("");
	uploadStatus('chat_file', 1);
	waitUpload = 0;
}

startPrivateUp = function(){
	upPrivateStatus(0);
	$('#private_progress').show();
}
upPrivateStatus = function(v){
	$('#pprogress').css('width', v+'%');
}
cancelPrivateUp = function(){
	pupload.abort();
}
resetPrivateUp = function(){
	$("#private_file").val("");
	$('#private_progress').hide();
	uploadStatus('private_file', 1);
	waitUpload = 0;
}

uploadPrivateFile = function(){
	if($('#private_file').val() === ''){
		return;
	}
	else {
		uploadPrivate($("#private_file").prop("files")[0]);
	}
}

uploadPrivate = function(f){
	var filez = (f.size / 1024 / 1024).toFixed(2);
	if( filez > fileMax ){
		callError(system.fileBig);
	}
	else {
		if(waitUpload == 0){
			uploadStatus('private_file', 2);
			waitUpload = 1;
			var form_data = new FormData();
			form_data.append("file", f)
			form_data.append("target", currentPrivate)
			form_data.append("zone", 'private')
			form_data.append("token", utk)
			pupload = $.ajax({
				url: "system/action/file_private.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				beforeSend: function(){
					startPrivateUp();
				},
				xhr: function() {
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(evt) {
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total;
							upPrivateStatus((Math.round(percentComplete * 100)));
						}
					}, false);
					return xhr;
				},
				success: function(response){
					if(response.code == 1){
						callError(system.wrongFile);
					}
					else if(response.code == 9){
						callError(system.fileBlocked);
					}
					else if(response.code == 5){
						appendSelfPrivateMessage(response.logs);
					}	
					else if(response.code == 99){
						appendCannotPrivate();
					}
					else {
						callError(system.error);
					}
					resetPrivateUp();
				},
				error: function(){
					resetPrivateUp();
				}
			})
		}
		else {
			return false;
		}
	}
}

var waitCover = 0;
uploadCover = function(){
	var file_data = $("#cover_file").prop("files")[0];
	var filez = ($("#cover_file")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > coverMax ){
		callError(system.fileBig);
	}
	else if($("#cover_file").val() === ""){
		callError(system.noFile);
	}
	else {
		if(waitCover == 0){
			waitCover = 1;
			uploadIcon('cover_icon', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("self", 1)
			form_data.append("token", utk)
			$.ajax({
				url: "system/action/cover.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response.code == 1){
						callError(system.wrongFile);
					}
					else if(response.code == 9){
						callError(system.fileBlocked);
					}
					else if(response.code == 5){
						addCover(response.data);
					}
					else {
						callError(system.error);
					}
					uploadIcon('cover_icon', 2);
					waitCover = 0;
				},
				error: function(){
					callError(system.error);
					uploadIcon('cover_icon', 2);
					waitCover = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
var waitRoomIcon = 0;
addRoomIcon = function(id){
	var file_data = $("#ricon_image").prop("files")[0];
	var filez = ($("#ricon_image")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > riconMax ){
		callError(system.fileBig);
	}
	else if($("#ricon_image").val() === ""){
		callError(system.noFile);
	}
	else {
		if(waitRoomIcon == 0){
			waitRoomIcon = 1;
			uploadIcon('ricon_icon', 1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("add_icon", 1)
			form_data.append("token", utk)
			$.ajax({
				url: "system/action/room_icon.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response.code == 1){
						callError(system.wrongFile);
					}
					else if(response.code == 5){
						$('.ricon_current').attr('src', response.data);
						$('#ricon'+id).attr('src', response.data);
					}
					else {
						callError(system.error);
					}
					uploadIcon('ricon_icon', 2);
					waitRoomIcon = 0;
				},
				error: function(){
					callError(system.error);
					uploadIcon('ricon_icon', 2);
					waitRoomIcon = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}
var wallUpload = 0;
uploadWall = function(){
	var file_data = $("#wall_file").prop("files")[0];
	var filez = ($("#wall_file")[0].files[0].size / 1024 / 1024).toFixed(2);
	if( filez > fileMax ){
		callError(system.fileBig);
	}
	else if($("#wall_file").val() === ""){
		callError(system.noFile);
	}
	else {
		if(wallUpload == 0){
			wallUpload = 1;
			postIcon(1);
			var form_data = new FormData();
			form_data.append("file", file_data)
			form_data.append("token", utk)
			form_data.append("zone", 'wall')
			$.ajax({
				url: "system/action/file_wall.php",
				dataType: 'json',
				cache: false,
				contentType: false,
				processData: false,
				data: form_data,
				type: 'post',
				success: function(response){
					if(response.code > 0){
						if(response.code == 1){
							callError(system.wrongFile);
						}
						else if(response.code == 9){
							callError(system.fileBlocked);
						}
						postIcon(2);
					}
					else {
						$('#post_file_data').attr('data-key', response.key);
						$('#post_file_data').html(response.file);
					}
					wallUpload = 0;
				},
				error: function(){
					wallUpload = 0;
				}
			})
		}
		else {
			return false;
		}
	}
}

changeRelation = function(){
	callError(system.error);
	return false;
}

proMusic = function(s){
	if(s == 1){
		$('#up_pmusic, #del_pmusic').addClass('fhide');
		$('#add_pmusic').removeClass('fhide');
	}
	if(s == 2){
		$('#add_pmusic, #del_pmusic').addClass('fhide');
		$('#up_pmusic').removeClass('fhide');
	}
	if(s == 3){
		$('#add_pmusic, #up_pmusic').addClass('fhide');
		$('#del_pmusic').removeClass('fhide');
	}
}

var waitMusic = 0;
uploadMusic = function(){
	if($("#pmusic_file").val() === ""){
		callError(system.noFile);
		return false;
	}
	var file_data = $("#pmusic_file").prop("files")[0];
	var filez = ($("#pmusic_file")[0].files[0].size / 1024 / 1024).toFixed(2);
	if(filez > fileMax){
		callError(system.fileBig);
		return false;
	}
	if(waitMusic == 0){
		waitMusic = 1;
		var form_data = new FormData();
		form_data.append("file", file_data)
		form_data.append("upload_music", 1)
		form_data.append("token", utk)
		$.ajax({
			url: "system/action/file_music.php",
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: 'post',
			beforeSend: function(){
				proMusic(2);
			},
			success: function(response){
				if(response.code == 1){
					proMusic(3);
				}
				else if(response.code == 2){
					callError(system.lowBalance);
					proMusic(1);
				}
				else {
					proMusic(1);
				}
				waitMusic = 0;
			},
			error: function(){
				proMusic(1);
				waitMusic = 0;
			}
		})
	}
}

removeProfileMusic = function(){
	$.post('system/action/file_music.php', {
		remove_pmusic: 1,
		token: utk,
		}, function(response) {
			if(response.code == 1){
				proMusic(1);
			}
	}, 'json');
}

// misc function for files

addCover = function(cover){
	$('.profile_background').css('background-image', 'url('+cover+')');
	$('.profile_background').addClass('cover_size');
}
delCover = function(cover){
	$('.profile_background').css('background-image', '');
	$('.profile_background').removeClass('cover_size');
}
uploadIcon = function(target, type){
	var upIcon = $('#'+target).attr('data');
	if(type == 2){
		$('#'+target).removeClass('fa-circle-notch fa-spin fa-fw').addClass(upIcon);
	}
	else {
		$('#'+target).removeClass(upIcon).addClass('fa-circle-notch fa-spin fa-fw');
	}
}
uploadStatus = function(target, type){
	if(type == 2){
		$("#"+target).prop('disabled', true);
	}
	else {
		$("#"+target).prop('disabled', false);
	}
}
postIcon = function(type){
	if(type == 2){
		$('#post_file_data').html('').hide();
	}
	else {
		$('#post_file_data').html(regSpinner).show();
	}
	$('#post_file_data').attr('data-key', '');
}
removeFile = function(target){
	postIcon(2);
	$.post('system/action/action_files.php', {
		remove_uploaded_file: target,
		}, function(response) {
	});
}

avatarUpload = function(t = 0) {
    const data = t > 0 ? { target: t } : { self: 1 };
    $.post('system/box/avatar.php', data, function(response) {
        overModal(response, 400);
    });
};

// files removal options

deleteAvatar = function(){
	$.ajax({
		url: "system/action/avatar.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			delete_avatar: 1,
		},
		success: function(response) {
			$('.avatar_profile').attr('src', response.data);
			$('.avatar_profile').attr('href', response.data);
			$('.glob_av').attr('src', response.data);
		},
		error: function(){
			callError(system.error);
		}
	});
}
deleteCover = function(){
	$.post('system/action/cover.php', { 
		delete_cover: 1,
		}, function(response) {
			delCover();
	});
}
removeRoomIcon = function(id){
	$.ajax({
		url: "system/action/room_icon.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			remove_icon: id,
		},
		success: function(response){
			if(response.code == 1) {
				$('.ricon_current').attr('src', response.data);
				$('#ricon'+id).attr('src', response.data);
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
infoPop = function(i){
	$.post('system/action/help.php', { 
			info:i,
		}, function(response) {
			topModal(response, 500);
	});
}

/* gift functions */

$(document).on('click', '.select_gift', function(){
	var gimg = $(this).attr('data-img');
	var gprice = $(this).attr('data-price');
	var gid = $(this).attr('data-id');
	var gtitle = $(this).attr('data-title');
	var gmethod = $(this).attr('data-method');
	
	if(gmethod == 1){
		$('#gift_sruby').hide();
		$('#gift_sgold').show();
	}
	if(gmethod == 2){
		$('#gift_sgold').hide();
		$('#gift_sruby').show();
	}
	
	$('#gift_second').attr('data-id', gid);
	$('#gift_selected').attr('src', gimg);
	$('#gift_pricing').text(gprice);
	$('#gift_title').text(gtitle);
	$('#gift_first').hide();
	$('#gift_second').show();
});
$(document).on('click', '.open_gift', function(){
	$.post('system/box/open_gift.php', {
		open_gift: $(this).attr('data'),
		}, function(response) {
			if(response == 0){
				callError(system.error);
			}
			else {
				overModal(response, 300);
			}
	});
});

var waitSendGift = 0;
sendGift = function(){
	if(waitSendGift == 0){
		waitSendGift = 1;
		$.post('system/action/action_gift.php', {
			send_gift: $('#gift_second').attr('data-id'),
			target: $('#gift_second').attr('data-user'),
			}, function(response) {
				if(response == 1){
					callSuccess(system.actionComplete);
					hideOver();
				}
				else if(response == 11){
					callSuccess(system.actionComplete);
					hideOver();
				}
				else if(response == 2){
					callError(system.noGold);
				}
				else {
					callError(system.error);
					hideOver();
				}
				waitSendGift = 0;
		});
	}
}
backGift = function(){
	$('#gift_second').hide();
	$('#gift_first').show();
}

/* document ready functions */

$(document).ready(function(){
	
	systemLoad();
	setTimeout(cleanData, 5000)
	runClean = setInterval(cleanData, 300000);

	$(document).on('click', '.get_info', function(){
		var profile = $(this).attr('data');
		closeTrigger();
		getProfile(profile);
	});
	$(document).on('click', '.get_finfo', function(){
		var profile = $(this).attr('data');
		closeTrigger();
		hideOver();
		getProfile(profile);
	});
	$(document).on('click', '.open_profile', function(){
		editProfile();
	});
	$(document).on('click', '.badge_info', function(){
		getBadgeInfo();
	});
	$(document).on('click', '.get_actions', function(){
		var id = $(this).attr('data');
		closeTrigger();
		getActions(id);
	});
	$(document).on('click', '.open_same_page', function(){
		var l = $(this).attr('data');
		openSamePage(l);
	});
	$(document).on('click', '.open_link_page', function(){
		var l = $(this).attr('data');
		openLinkPage(l);
	});
	$(document).on('click', '.get_room_actions', function(){
		var id = $(this).attr('data');
		closeTrigger();
		getRoomActions(id);
	});

	$(document).on('click', '.name_choice, .choice', function() {	
		var curColor = $(this).attr('data');
		if($('.user_color').attr('data') == curColor){
			$('.bccheck').replaceWith("");
			$('.user_color').attr('data', 'user');
		}
		else {
			$('.bccheck').replaceWith("");
			$(this).append('<i class="fa fa-check bccheck"></i>');
			$('.user_color').attr('data', curColor);
		}
		previewName();
	});
	
	$(document).on('change', '#fontitname', function(){		
		previewName();
	});
	
	$(document).on('click', '.infopop', function(){		
		infoPop($(this).attr('data'));
	});
	
	$(document).on('keydown', function(event) {
		if( event.which === 8 && event.ctrlKey && event.altKey ) {
			getConsole();
		}
	});
	
	$(document).on('change, paste, keyup', '#search_chat_room', function(){
		var sr = $(this).val().toLowerCase();
		if(sr == ''){
			$(".room_element").each(function(){
				$(this).show();
			});	
		}
		else {
			$(".room_element").each(function(){
				var rt = $(this).find('.roomtitle').text().toLowerCase();
				var rd = $(this).find('.roomdesc').text().toLowerCase();
				if(rt.indexOf(sr) < 0 && rd.indexOf(sr) < 0){
					$(this).hide();
				}
				else {
					$(this).show();
				}
			});
		}
	});
	
	$(document).on("mouseenter", '.srnk', function(){
		var rtitle = $(this).attr('data-r');
		$(this).attr('title', systemRankTitle(parseInt(rtitle)));
	});
	$(document).on("mouseenter", '.rrnk', function(){
		var rtitle = $(this).attr('data-r');
		$(this).attr('title', roomRankTitle(parseInt(rtitle)));
	});
	$(document).on("mouseenter", '.sttle', function(){
		var stitle = $(this).attr('data-s');
		$(this).attr('title', statusTitle(parseInt(stitle)));
	});
});