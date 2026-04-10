<?php
require('../config_session.php');

ob_start();
?>
<div class="left_keep">
	<div class="pad10">
		<div class="extensions_panel_intro">
			<div class="text_med bold">Available Extensions</div>
			<div class="sub_text tmargin5">Browse your installed extensions, see what each one does, and open its GitHub page.</div>
		</div>
		<div id="extensions_dynamic_list" class="extensions_grid"></div>
	</div>
</div>
<?php
$res['content'] = ob_get_clean();
$res['title'] = 'Extensions';
echo boomCode(1, $res);
?>