<?php
require('../config_session.php');

$items = reactionEmojiPool();

echo boomCode(1, [
	'items' => $items,
]);
die();
?>