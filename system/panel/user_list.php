<?php
require('../config_session.php');

function addUserToRoleGroup(&$groups, $user, $item){
	$role = userListRoleInfo($user);
	if(!isset($groups[$role['key']])){
		$groups[$role['key']] = [
			'title'=> $role['title'],
			'sort'=> $role['sort'],
			'count'=> 0,
			'users'=> '',
		];
	}
	$groups[$role['key']]['count']++;
	$groups[$role['key']]['users'] .= $item;
}

function renderRoleGroups($groups, $listClass){
	if(empty($groups)){
		return '';
	}
	usort($groups, function($a, $b){
		if($a['sort'] == $b['sort']){
			return strcasecmp((string) $a['title'], (string) $b['title']);
		}
		return ($a['sort'] < $b['sort']) ? 1 : -1;
	});
	$out = '';
	foreach($groups as $group){
		$out .= '<div class="user_role_group">';
		$out .= '<div class="role_group_head"><span class="role_group_title">' . $group['title'] . '</span><span class="role_group_badge">' . $group['count'] . '</span></div>';
		$out .= '<div class="' . $listClass . ' role_group_users">' . $group['users'] . '</div>';
		$out .= '</div>';
	}
	return $out;
}

$check_action = getDelay();
$online_delay = time() - ( 86400 * 7 );
$online_user = '';
$offline_user = '';
$onair_user = '';
$online_group_list = [];
$onair_group_list = [];
$online_count = 0;
$onair_count = 0;
$lazy_state = 0;
$lazy_min = 20;

$data_list = $mysqli->query("
	SELECT user_id, user_name, user_color, user_font, user_rank, user_level, user_dj, user_onair, user_join, user_sex, user_age, user_tumb, user_cover, user_status, country,
	user_ghost, user_mute, user_rmute, user_mmute, room_mute, last_action, user_bot, user_role, user_mood, sshare, lshare, ashare
	FROM `boom_users`
	WHERE `user_roomid` = {$data["user_roomid"]}  AND last_action > '$check_action' AND user_status != 99 || user_bot = 1
	ORDER BY `user_rank` DESC, user_role DESC, `user_name` ASC 
");

if($setting['max_offcount'] > 0){
	$offline_list = $mysqli->query("
		SELECT user_id, user_name, user_color, user_font, user_rank, user_level, user_dj, user_onair, user_join, user_sex, user_age, user_tumb, user_cover, user_status, country,
		user_ghost, user_mute, user_rmute, user_mmute, room_mute, last_action, user_bot, user_role, user_mood, sshare, lshare, ashare
		FROM `boom_users`
		WHERE `user_roomid` = {$data["user_roomid"]}  AND last_action > '$online_delay' AND last_action < '$check_action' AND user_status != 99 AND  user_rank != 0 AND user_bot = 0
		ORDER BY last_action DESC LIMIT {$setting['max_offcount']}
	");
}

mysqli_close($mysqli);

if ($data_list->num_rows > 0){
	while ($list = $data_list->fetch_assoc()){
		$item = '';
		if($lazy_state < $lazy_min){
			$item = createUserlist($list);
		}
		else {
			$item = createUserlist($list, true);
		}
		if(empty($item)){
			continue;
		}
		if($list['user_dj'] == 1 && $list['user_onair'] == 1){
			addUserToRoleGroup($onair_group_list, $list, $item);
			$onair_count++;
		}
		else {
			addUserToRoleGroup($online_group_list, $list, $item);
			$online_count++;
		}
		$lazy_state++;
	}
}
if($setting['max_offcount'] > 0){
	if($offline_list->num_rows > 0){
		while($offlist = $offline_list->fetch_assoc()){
			$item = '';
			if($lazy_state < $lazy_min){
				$item = createUserlist($offlist);
				$lazy_state++;
			}
			else {
				$item = createUserlist($offlist, true);
			}
			if(empty($item)){
				continue;
			}
			$offline_user .= $item;
		}
	}
}

$onair_user = renderRoleGroups($onair_group_list, 'online_user');
$online_user = renderRoleGroups($online_group_list, 'online_user');

?>
<div id="container_user" class="pad10">
	<?php if($onair_user != ''){ ?>
	<div class="user_count">
		<div class="bcell">
			<?php echo $lang['onair']; ?> <span class="ucount theme_btn"><?php echo $onair_count; ?></span>
		</div>
	</div>
	<?php echo $onair_user; ?>
	<?php } ?>
	<div class="user_count">
		<div class="bcell">
			<?php echo $lang['online']; ?> <span class="ucount theme_btn"><?php echo $online_count; ?></span>
		</div>
	</div>
	<?php echo $online_user; ?>
	<?php if($offline_user != ''){ ?>
	<div class="user_count">
		<div class="bcell">
			<?php echo $lang['offline']; ?>
		</div>
	</div>
	<?php echo $offline_user; ?>
	<?php } ?>
	<div class="clear">
	</div>
</div>