chatRank = (rank, cl) => {
	return `<img title="${systemRankTitle(rank)}" src="${systemRankIcon(rank)}" class="${cl}"/>`;
}
myAvatar = t => {
	if(t.includes('default')){
		return `default_images/avatar/${t}`;
	}
	else {
		return `avatar/${t}`;
	}
}
ghostLog = t => {
	if(t > 0){
		return 'ghost_post';
	}
	else {
		return '';
	}
}

chatLogDate = t => {
	return t.log_date;
}
privateLogDate = t => {
	return t.log_date;
}
bubbleColor = t => {
	if(mySelf(t.user_id) || ububble == 1){
		return t.user_tcolor;
	}
	else {
		return '';
	}
}
bubbleColor = t => {
	if(mySelf(t.user_id) || ububble == 1){
		return t.user_tcolor;
	}
	else {
		return '';
	}
}
messageEffectClass = t => {
	if(t !== null && typeof t === 'object' && typeof t.effect === 'string'){
		return t.effect;
	}
	return '';
}
myNotice = data => {
    let message = data.log_content;
    if (data.user_id !== user_id && data.log_sys == 0) {
        const regex = new RegExp('(^|[^\\w@#:/])(' + user_name + ')(?=\\s|$)', 'gi');
        message = message.replace(regex, function(match, before, name) {
            return before + `<span class="my_notice">${user_name}</span>`;
        });
    }
    return message;
}

const defaultReactionChoices = () => {
	return [
		{ key: 'default_images/reaction/like.svg', src: 'default_images/reaction/like.svg' },
		{ key: 'default_images/reaction/dislike.svg', src: 'default_images/reaction/dislike.svg' },
		{ key: 'default_images/reaction/love.svg', src: 'default_images/reaction/love.svg' },
		{ key: 'default_images/reaction/funny.svg', src: 'default_images/reaction/funny.svg' },
	];
}
normalizeReaction = r => {
	const reaction = {
		mine: '',
		items: [],
		total: 0,
	};
	if(r === null || typeof r !== 'object'){
		return reaction;
	}
	if(typeof r.mine === 'string'){
		reaction.mine = r.mine;
	}
	else if(typeof r.mine === 'number' && r.mine > 0){
		const defaults = defaultReactionChoices();
		if(defaults[(r.mine - 1)]){
			reaction.mine = defaults[(r.mine - 1)].key;
		}
	}
	if(Array.isArray(r.items)){
		for(let i = 0; i < r.items.length; i++){
			const item = r.items[i];
			if(item === null || typeof item !== 'object'){
				continue;
			}
			const key = typeof item.key === 'string' ? item.key : '';
			const src = typeof item.src === 'string' && item.src !== '' ? item.src : key;
			const count = parseInt(item.count) || 0;
			if(key === '' || count < 1){
				continue;
			}
			reaction.items.push({ key: key, src: src, count: count });
			reaction.total += count;
		}
	}
	if(reaction.items.length === 0){
		const defaults = defaultReactionChoices();
		const legacy = [
			{ count: parseInt(r.like) || 0, index: 0 },
			{ count: parseInt(r.dislike) || 0, index: 1 },
			{ count: parseInt(r.love) || 0, index: 2 },
			{ count: parseInt(r.funny) || 0, index: 3 },
		];
		for(let i = 0; i < legacy.length; i++){
			if(legacy[i].count < 1){
				continue;
			}
			const pick = defaults[legacy[i].index];
			reaction.items.push({ key: pick.key, src: pick.src, count: legacy[i].count });
			reaction.total += legacy[i].count;
		}
	}
	reaction.items.sort(function(a, b){
		return (b.count || 0) - (a.count || 0);
	});
	if(reaction.total < 1){
		reaction.total = reaction.items.reduce(function(sum, item){
			return sum + (parseInt(item.count) || 0);
		}, 0);
	}
	return reaction;
}
reactionSummaryTemplate = r => {
	const reaction = normalizeReaction(r);
	if(reaction.total < 1 || reaction.items.length < 1){
		return '';
	}
	let summary = '';
	const show = Math.min(3, reaction.items.length);
	for(let i = 0; i < show; i++){
		summary += `<img class="msg_react_icon" src="${reaction.items[i].src}"/>`;
	}
	return `<span class="msg_react_count">${summary}<span class="msg_react_total">${reaction.total}</span></span>`;
}
reactionPickerTemplate = (scope, id, r) => {
	const reaction = normalizeReaction(r);
	const list = defaultReactionChoices();
	let picker = '';
	for(let i = 0; i < list.length; i++){
		const active = reaction.mine === list[i].key ? ' active' : '';
		picker += `<button type="button" class="msg_react_btn${active}" data-scope="${scope}" data-target="${id}" data-react-key="${list[i].key}"><img src="${list[i].src}"/></button>`;
	}
	picker += `<button type="button" class="msg_react_more" data-scope="${scope}" data-target="${id}"><i class="fa-regular fa-face-smile"></i><span>+</span></button>`;
	return picker;
}
messageReactionTemplate = (scope, id, r) => {
	const reaction = normalizeReaction(r);
	const hasReaction = reaction.total > 0 ? ' has_reaction' : '';
	return `
		<div class="msg_react_box" data-scope="${scope}" data-target="${id}">
			<div class="msg_react_picker">${reactionPickerTemplate(scope, id, reaction)}</div>
			<div class="msg_react_summary${hasReaction}">${reactionSummaryTemplate(reaction)}</div>
		</div>
	`;
}
updateReactionBox = (scope, id, r) => {
	const reaction = normalizeReaction(r);
	const box = `.msg_react_box[data-scope="${scope}"][data-target="${id}"]`;
	$(box).find('.msg_react_summary').html(reactionSummaryTemplate(reaction));
	$(box).find('.msg_react_summary').toggleClass('has_reaction', reaction.total > 0);
	$(box).find('.msg_react_btn').removeClass('active');
	if(reaction.mine !== ''){
		$(box).find(`.msg_react_btn[data-react-key="${reaction.mine}"]`).addClass('active');
	}
}

// CHAT LOGS

chatLogTemplate = t => {
	var quoted = '';
	if(t.quote !== null && !ignored(t.quote.quser)){
		quoted = `
			<div class="cqbox quote${t.quote.qid}">
				<div class="cquote">
					<div class="cqwrap">
						<div class="cqav">
							<img src="${myAvatar(t.quote.qtumb)}" onerror="avFix(this);"/>
						</div>
						<div class="cqcontent">
							<div class="cqname">${t.quote.qname}</div>
							<div class="cqmess">${t.quote.qcontent}</div>
						</div>
					</div>
				</div>
			</div>
		`;
	}
	return 	`
		<li id="log${t.log_id}" data="${t.log_id}" class="chat_log ch_logs ${t.log_type} ${ghostLog(t.gpost)}">
			<div class="avtrig avs_menu chat_avatar" data-i="l${t.log_id}" data-av="${myAvatar(t.user_tumb)}" data-cover="${t.user_cover}" data-id="${t.user_id}" data-name="${t.user_name}" data-rank="${t.user_rank}" data-level="${t.user_level}" data-bot="${t.user_bot}" data-gender="${t.user_gender}" data-country="${t.user_country}" data-age="${t.user_age}">
				<img class="cavatar avav ${t.gborder}" src="${myAvatar(t.user_tumb)}" onerror="avFix(this);"/>
			</div>
			<div class="my_text">
				<div class="btable">
					<div class="cname">${chatRank(t.user_rank, 'chat_rank')}<span class="username ${t.user_color}">${t.user_name}</span> <span class="sub_text text_xsmall lmargin10 hidden">${chatLogDate(t)}</span></div>
					<div class="cdate sub_chat">${chatLogDate(t)}</div>
				</div>
				<div class="log_content"  data-id="${t.log_id}" data-user="${t.user_id}" data-bot="${t.user_bot}">
					${quoted}
					<div class="chat_message tpad5">
						<div class="mbubble bubble ${bubbleColor(t)} ${messageEffectClass(t)}">${t.log_content}</div>
					</div>
					${messageReactionTemplate('chat', t.log_id, t.reaction)}
				</div>
			</div>
		</li>
	`;
}
systemLogTemplate = t => {
	return 	`
		<li id="log${t.log_id}" class="chat_log sys_log">
			<div data="${t.user_id}" class="get_info chat_savatar">
				<img class="savatar avav" src="${myAvatar(t.user_tumb)}" onerror="avFix(this);"/>
			</div>
			<div class="bcell_mid chat_system hpad5">
				${renderSystemMessage(t)}
			</div>
		</li>
	`;
}

createChatLog = (t) => {
	if(t === null){
		return;
	}
	if(t.log_sys > 0){
		return `${systemLogTemplate(t)}`;
	}
	else {
		return `${chatLogTemplate(t)}`;
	}
}

createSystemChatLog = t => {
	if(t === null){
		return;
	}
	return `${systemLogTemplate(t)}`;
}

// CHAT TOPIC 

topicTemplate = t => {
	if('content' in t){
		return `
			<li class="other_logs splog topic_log">
				<div class="topic_icon">
					<img class="tpicon" src="${t.icon}"/>
				</div>
				<div class="topic_text">
					<div class="btable">
						<div class="bcell_mid bold tptitle">
							${t.title}
						</div>
						<div onclick="hideThisPost(this)"; class="tpclear">
							<i class="fa fa-times"></i>
						</div>
					</div>
					<div class="topic_message text_small tptext">
						${t.content}
					</div>
				</div>
			</li>
		`;
	}
}
renderTopic = t => {
	if(t !== ''){
		return topicTemplate(t);
	}
}

// PRIVATE LOGS

hunterPrivateTemplate = t => {
	var quoted = '';
	if(t.quote !== null){
		quoted = `<div class="pquote${t.quote.qpost} hunt_quote">${t.quote.qcontent}</div><div class="clear"></div>`;
	}
	return `
		<li  data-id="${t.log_id}" data-av="${myAvatar(t.user_tumb)}" data-name="${t.user_name}" class="outpriv privlog" id="priv${t.log_id}">
			<div class="private_logs">
				<div class="priwrap">
					<div class="privcont">
						<div class="privopt sub_priv"></div>
						<div class="prbox">${quoted}<div class="hunter_private ${bubbleColor(t)} ${messageEffectClass(t)}">${t.log_content}</div>${messageReactionTemplate('private', t.log_id, t.reaction)}</div>
					</div>
					<div class="prdate sub_priv"><i class="fa fa-check success prview ${privateView(t.view)}"> </i>${privateLogDate(t)}</div>
				</div>
				<div class="private_avatar">
					<img data="${t.user_id}" class="get_info avatar_private" src="${myAvatar(t.user_tumb)}" onerror="avFix(this);"/>
				</div>
			</div>
		</li>
	`;
}
targetPrivateTemplate = t => {
	var quoted = '';
	if(t.quote !== null){
		quoted = `<div class="pquote${t.quote.qpost} targ_quote">${t.quote.qcontent}</div><div class="clear"></div>`;
	}
	return `
		<li data-id="${t.log_id}" data-av="${myAvatar(t.user_tumb)}" data-name="${t.user_name}" class="inpriv privlog" id="priv${t.log_id}">
			<div class="private_logs">
				<div class="private_avatar">
					<img data="${t.user_id}" class="get_info avatar_private" src="${myAvatar(t.user_tumb)}" onerror="avFix(this);"/>
				</div>
				<div class="priwrap">
					<div class="privcont">
						<div class="prbox">${quoted}<div class="target_private ${bubbleColor(t)} ${messageEffectClass(t)}">${t.log_content}</div>${messageReactionTemplate('private', t.log_id, t.reaction)}</div>
						<div class="privopt sub_priv"></div>
					</div>
					<div class="prdate sub_priv prviewed">${privateLogDate(t)}</div>
				</div>
			</div>
		</li>
	`;
}
privateView = t => {
	return 'prview_hide';
}
createPrivateLog = t => {
	if(t === null){
		return;
	}
	if(mySelf(t.user_id)) {
		return `${hunterPrivateTemplate(t)}`;
	} 
	else {
		return `${targetPrivateTemplate(t)}`;
	}
}
cannotPrivateTemplate = () => {
	return `
		<li id="cannot_private" class="vpad5 hpad5">
			<div class="cannotpriv">
				<div class="bcell_mid  pad10 alert_neutral centered_element brad10 text_small bold">
					${system.cannotContact}
				</div>
			</div>
		</li>
	`;
}

// MENU

renderRightMenu = function(aIcon, aText, aCall, aMenu){
	var qmenu = `
		<div class="fmenu_item bhover mmenu_item" onclick="${aCall}">
			<div class="fmenu_icon">
				<i class="fa fa-${aIcon} menui"></i>
			</div>
			<div class="fmenu_text">
				${aText}
			</div>
		</div>
	`;
	$('#'+aMenu).append(qmenu);
}

renderSideMenu = function(aIcon, aText, aCall, aMenu, aClass){
	var qmenu = `
		<div class="fmenu_item bhover" onclick="${aCall}">
			<div class="${aClass}">
				<img src="${aIcon}"/>
			</div>
			<div class="fmenu_text">
				${aText}
			</div>
		</div>
	`;
	$('#'+aMenu).append(qmenu);
}
renderLeftMenu = (aIcon, aText, aCall, optMenu = '') => {
	const qmenu = `
		<div title="${aText}" onclick="${aCall}" class="bhover left_menu_item">
			<div class="left_menu_icon">
				<i class="fa fa-${aIcon} leftmenui"></i>
				${optMenu ? `<div id="${optMenu}" class="head_notify side_notify bnotify"></div>` : ''}
			</div>
		</div>
	`;
	$('#left_menu_content').append(qmenu);
}