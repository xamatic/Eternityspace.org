<?php
require('../config_session.php');

ob_start();
?>
<div class="left_keep">
	<div class="pad10">
		<div class="discord_embed_wrap" style="width:100%;height:calc(100vh - 180px);min-height:520px;border-radius:8px;overflow:hidden;">
			<iframe
				class="discord_embed_frame"
				src="https://e.widgetbot.io/channels/1447474462227431597/1458310522691653665"
				title="Discord"
				allow="clipboard-write; microphone; camera"
				style="width:100%;height:100%;border:0;display:block;"
				loading="lazy">
			</iframe>
		</div>
		<a class="discord_embed_link" href="https://discord.com/channels/1447474462227431597/1458310522691653665" target="_blank" rel="noopener noreferrer">Open Discord in a new tab</a>
	</div>
</div>
<?php
$res['content'] = ob_get_clean();
$res['title'] = 'Discord';
echo boomCode(1, $res);
?>