<?php
require(__DIR__ . '/../config_session.php');

ensurePublicThemeTable();

function publicThemeDbEscape($value){
	global $mysqli;
	return $mysqli->real_escape_string((string) $value);
}
function publicThemePostConfig(){
	return publicThemeSanitizeConfig([
		'header_bg' => $_POST['header_bg'] ?? '',
		'header_text' => $_POST['header_text'] ?? '',
		'chat_bg' => $_POST['chat_bg'] ?? '',
		'chat_text' => $_POST['chat_text'] ?? '',
		'bubble_bg' => $_POST['bubble_bg'] ?? '',
		'accent' => $_POST['accent'] ?? '',
		'default_btn' => $_POST['default_btn'] ?? '',
		'panel_opacity' => $_POST['panel_opacity'] ?? '',
		'panel_blur' => $_POST['panel_blur'] ?? '',
	]);
}
function publicThemeLogoPath($folder){
	$logo = 'default_images/logo.png' . boomFileVersion();
	if($folder != '' && file_exists(BOOM_PATH . '/css/themes/' . $folder . '/images/logo.png')){
		$logo = BOOM_DOMAIN . 'css/themes/' . $folder . '/images/logo.png' . boomFileVersion();
	}
	return $logo;
}
function publicThemeEnsureUploadDir(){
	$path = BOOM_PATH . '/theme_public';
	if(!is_dir($path)){
		@mkdir($path, 0755, true);
	}
	if(is_dir($path)){
		return true;
	}
	return false;
}
function publicThemeDeleteDir($path){
	if(!is_dir($path)){
		return true;
	}
	$items = scandir($path);
	if(!is_array($items)){
		return false;
	}
	foreach($items as $item){
		if($item === '.' || $item === '..'){
			continue;
		}
		$target = $path . '/' . $item;
		if(is_dir($target)){
			publicThemeDeleteDir($target);
		}
		else {
			@unlink($target);
		}
	}
	return @rmdir($path);
}
function publicThemeCanDelete($theme, $user){
	if(empty($theme) || empty($user)){
		return false;
	}
	if((int) $theme['theme_user'] === (int) $user['user_id']){
		return true;
	}
	if(publicThemeCanModerate($user)){
		return true;
	}
	return false;
}

if(isset($_POST['save_public_theme']) || isset($_POST['submit_public_theme'])){
	if(!publicThemeCanPublish($data)){
		echo boomCode(4);
		die();
	}
	$theme_name = publicThemeSanitizeName($_POST['theme_name'] ?? '');
	if($theme_name == ''){
		echo boomCode(2);
		die();
	}
	$current = publicThemeGetByUser($data['user_id']);
	if(!empty($current) && (int) $current['theme_locked'] > 0){
		echo boomCode(5);
		die();
	}

	$config = publicThemePostConfig();
	$theme_css = publicThemeSanitizeCss($_POST['theme_custom_css'] ?? '');
	$theme_bg = publicThemeNormalizeBackground($_POST['theme_background'] ?? '');

	$status = isset($_POST['submit_public_theme']) ? 1 : 0;
	$submitted = ($status == 1) ? time() : 0;
	$slug = strtolower(str_replace(' ', '-', $theme_name));
	$slug = preg_replace('/[^a-z0-9-]/', '', $slug);
	if($slug == ''){
		$slug = 'theme-' . (int) $data['user_id'];
	}

	$name_sql = escape($theme_name);
	$slug_sql = escape($slug);
	$config_sql = publicThemeDbEscape(json_encode($config, JSON_UNESCAPED_UNICODE));
	$css_sql = publicThemeDbEscape($theme_css);
	$bg_sql = escape($theme_bg);
	$now = time();

	if(empty($current)){
		$mysqli->query("INSERT INTO boom_public_theme
			(theme_user, theme_name, theme_slug, theme_status, theme_locked, theme_config, theme_custom_css, theme_background, theme_folder, theme_note, theme_created, theme_updated, theme_submitted, theme_reviewed, theme_reviewer)
			VALUES ('{$data['user_id']}', '$name_sql', '$slug_sql', '$status', '0', '$config_sql', '$css_sql', '$bg_sql', '', '', '$now', '$now', '$submitted', '0', '0')");
	}
	else {
		$theme_id = (int) $current['theme_id'];
		$mysqli->query("UPDATE boom_public_theme
			SET theme_name = '$name_sql',
				theme_slug = '$slug_sql',
				theme_status = '$status',
				theme_config = '$config_sql',
				theme_custom_css = '$css_sql',
				theme_background = '$bg_sql',
				theme_note = '',
				theme_updated = '$now',
				theme_submitted = '$submitted',
				theme_reviewed = '0',
				theme_reviewer = '0'
			WHERE theme_id = '$theme_id' LIMIT 1");
	}
	$response = [
		'status' => $status,
		'status_text' => publicThemeStatusText($status),
	];
	echo boomCode(1, $response);
	die();
}

if(isset($_POST['upload_public_theme_bg'])){
	if(!publicThemeCanPublish($data)){
		echo boomCode(4);
		die();
	}
	$current = publicThemeGetByUser($data['user_id']);
	if(!empty($current) && (int) $current['theme_locked'] > 0){
		echo boomCode(5);
		die();
	}
	if(!isset($_FILES['theme_background_file']) || !is_array($_FILES['theme_background_file'])){
		echo boomCode(0);
		die();
	}
	$_FILES['file'] = $_FILES['theme_background_file'];
	if(fileError(3)){
		echo boomCode(3);
		die();
	}
	$info = pathinfo($_FILES['theme_background_file']['name']);
	$ext = strtolower($info['extension'] ?? '');
	if(!isImage($ext)){
		echo boomCode(3);
		die();
	}
	if(!publicThemeEnsureUploadDir()){
		echo boomCode(0);
		die();
	}
	$file = 'ptbg_' . encodeFile($ext);
	boomMoveFile('theme_public/' . $file);
	$stored = 'theme_public/' . $file;
	if(!sourceExist($stored)){
		echo boomCode(0);
		die();
	}
	echo boomCode(1, [
		'background' => $stored,
		'url' => BOOM_DOMAIN . $stored,
	]);
	die();
}

if(isset($_POST['moderate_public_theme'])){
	if(!publicThemeCanModerate($data)){
		echo boomCode(4);
		die();
	}
	$theme_id = (int) escape($_POST['theme_id'] ?? 0, true);
	$theme = publicThemeGetById($theme_id);
	if(empty($theme) || (int) $theme['theme_status'] !== 1){
		echo boomCode(2);
		die();
	}
	$action = trim((string) ($_POST['theme_action'] ?? ''));
	if($action === 'approve'){
		$folder = publicThemeWriteCssFile($theme);
		if($folder == ''){
			echo boomCode(0);
			die();
		}
		$folder_sql = escape($folder);
		$mysqli->query("UPDATE boom_public_theme
			SET theme_status = '2',
				theme_locked = '1',
				theme_folder = '$folder_sql',
				theme_note = '',
				theme_reviewed = '" . time() . "',
				theme_reviewer = '{$data['user_id']}'
			WHERE theme_id = '$theme_id' LIMIT 1");
		echo boomCode(1, [
			'folder' => $folder,
		]);
		die();
	}
	if($action === 'reject'){
		$note = trim((string) ($_POST['theme_note'] ?? ''));
		$note = preg_replace('/\s+/', ' ', strip_tags($note));
		if($note == ''){
			echo boomCode(3);
			die();
		}
		if(strlen($note) > 255){
			$note = substr($note, 0, 255);
		}
		$note_sql = escape($note);
		$mysqli->query("UPDATE boom_public_theme
			SET theme_status = '3',
				theme_locked = '0',
				theme_note = '$note_sql',
				theme_reviewed = '" . time() . "',
				theme_reviewer = '{$data['user_id']}'
			WHERE theme_id = '$theme_id' LIMIT 1");
		echo boomCode(1);
		die();
	}
	echo boomCode(0);
	die();
}

if(isset($_POST['apply_public_theme'])){
	$theme_id = (int) escape($_POST['theme_id'] ?? 0, true);
	$theme = publicThemeGetById($theme_id);
	if(empty($theme) || (int) $theme['theme_status'] !== 2){
		echo boomCode(2);
		die();
	}
	$folder = trim((string) $theme['theme_folder']);
	if($folder == '' || !file_exists(BOOM_PATH . '/css/themes/' . $folder . '/' . $folder . '.css')){
		$folder = publicThemeWriteCssFile($theme);
		if($folder == ''){
			echo boomCode(0);
			die();
		}
		$folder_sql = escape($folder);
		$mysqli->query("UPDATE boom_public_theme SET theme_folder = '$folder_sql' WHERE theme_id = '$theme_id' LIMIT 1");
	}
	$theme_sql = escape($folder);
	$mysqli->query("UPDATE boom_users SET user_theme = '$theme_sql', user_action = user_action + 1 WHERE user_id = '{$data['user_id']}' LIMIT 1");
	redisUpdateUser($data['user_id']);
	echo boomCode(1, [
		'theme' => $folder,
		'logo' => publicThemeLogoPath($folder),
		'tv' => time(),
	]);
	die();
}

if(isset($_POST['delete_public_theme'])){
	$theme_id = (int) escape($_POST['theme_id'] ?? 0, true);
	$theme = publicThemeGetById($theme_id);
	if(empty($theme)){
		echo boomCode(2);
		die();
	}
	if(!publicThemeCanDelete($theme, $data)){
		echo boomCode(4);
		die();
	}

	$folder = trim((string) $theme['theme_folder']);
	if($folder != '' && strpos($folder, 'pt_') === 0){
		$folder_sql = escape($folder);
		$mysqli->query("UPDATE boom_users SET user_theme = 'system', user_action = user_action + 1 WHERE user_theme = '$folder_sql'");
		$theme_dir = BOOM_PATH . '/css/themes/' . $folder;
		if(is_dir($theme_dir)){
			publicThemeDeleteDir($theme_dir);
		}
	}

	$bg = publicThemeNormalizeBackground((string) $theme['theme_background']);
	if($bg != '' && strpos($bg, 'theme_public/') === 0){
		$bg_path = BOOM_PATH . '/' . $bg;
		if(file_exists($bg_path)){
			@unlink($bg_path);
		}
	}

	$mysqli->query("DELETE FROM boom_public_theme WHERE theme_id = '$theme_id' LIMIT 1");
	redisUpdateUser($data['user_id']);
	echo boomCode(1);
	die();
}

echo boomCode(0);
die();
?>