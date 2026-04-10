<?php

$load_addons = 'paintit';
require('../../../system/config_addons.php');

function paintColors(){
	$listing = '';
	$list = array(
		'#b71c1c','#880e4f','#4a148c','#1a237e','#0d47a1','#1b5e20','#827717','#ff6f00','#3e2723','#000000',
		'#c62828','#ad1457','#6a1b9a','#283593','#1565c0','#2e7d32','#9e9d24','#ff8f00','#4e342e','#212121',	
		'#d32f2f','#c2185b','#7b1fa2','#303f9f','#1976d2','#388e3c','#afb42b','#ffa000','#5d4037','#424242',	
		'#e53935','#d81b60','#8e24aa','#3949ab','#1e88e5','#43a047','#c0ca33','#ffb300','#6d4c41','#616161',	
		'#f44336','#e91e63','#9c27b0','#3f51b5','#2196f3','#4caf50','#cddc39','#ffc107','#795548','#757575',	
		'#ef5350','#ec407a','#ab47bc','#5c6bc0','#42a5f5','#66bb6a','#d4e157','#ffca28','#8d6e63','#9e9e9e',
		'#e57373','#f06292','#ba68c8','#7986cb','#64b5f6','#81c784','#dce775','#ffd54f','#a1887f','#bdbdbd',			
		'#ef9a9a','#f48fb1','#ce93d8','#9fa8da','#90caf9','#a5d6a7','#e6ee9c','#ffe082','#bcaaa4','#ffffff',		
	);
	foreach($list as $li){
		$listing .= '<div data="' . $li . '" onclick="doPaintColor(this);" class="paint_shadow paint_circle" style="background:' . $li . ';"></div>';
	}
	return $listing;
}
function paintBackground(){
	$listing = '';
	$list = array(
		'#b71c1c','#880e4f','#4a148c','#1a237e','#0d47a1','#1b5e20','#827717','#ff6f00','#3e2723','#000000',
		'#c62828','#ad1457','#6a1b9a','#283593','#1565c0','#2e7d32','#9e9d24','#ff8f00','#4e342e','#212121',	
		'#d32f2f','#c2185b','#7b1fa2','#303f9f','#1976d2','#388e3c','#afb42b','#ffa000','#5d4037','#424242',	
		'#e53935','#d81b60','#8e24aa','#3949ab','#1e88e5','#43a047','#c0ca33','#ffb300','#6d4c41','#616161',	
		'#f44336','#e91e63','#9c27b0','#3f51b5','#2196f3','#4caf50','#cddc39','#ffc107','#795548','#757575',	
		'#ef5350','#ec407a','#ab47bc','#5c6bc0','#42a5f5','#66bb6a','#d4e157','#ffca28','#8d6e63','#9e9e9e',
		'#e57373','#f06292','#ba68c8','#7986cb','#64b5f6','#81c784','#dce775','#ffd54f','#a1887f','#bdbdbd',			
		'#ef9a9a','#f48fb1','#ce93d8','#9fa8da','#90caf9','#a5d6a7','#e6ee9c','#ffe082','#bcaaa4','#ffffff',		
	);
	foreach($list as $li){
		$listing .= '<div data="' . $li . '" onclick="newPaint(this);" class="paint_shadow paint_circle" style="background:' . $li . ';"></div>';
	}
	return $listing;
}
function paintIcon($icon){
	global $data;
	return '<img class="paint_icon" src="addons/paintit/files/icons/' . $icon . '.svg"/>';
}
?>
<div class="modal_top bhead">
	<div class="modal_top_empty">
	</div>
	<div class="modal_top_element close_modal">
		<i class="fa fa-times"></i>
	</div>
</div>
<div class="brelative paint_menu">
	<div class="btable paint_back_menu">
		<div class="bcell_mid paint_item paint_itemb menutrig" onclick="openNewPaint();"><?php echo paintIcon('file'); ?></div>
		<div class="bcell_mid paint_item paint_itemb menutrig" onclick="openPaintBrush();"><?php echo paintIcon('paintbrush'); ?></div>
		<div class="bcell_mid paint_item paint_itemb menutrig" onclick="openPaintColor();"><div class="paint_colored menutrig"></div></div>
		<div class="bcell_mid paint_item paint_itemb" onclick="undoPaint();"><?php echo paintIcon('undo'); ?></div>
		<div class="bcell_mid paint_item paint_itemb" onclick="redoPaint();"><?php echo paintIcon('redo'); ?></div>
		<div id="paint_eraser" class="bcell_mid paint_item paint_itemb" onclick="erasePaint(this);"><?php echo paintIcon('eraser'); ?></div>
		<div class="bcell_mid paint_item paint_itemb" onclick="clearPaint();"><?php echo paintIcon('delete'); ?></div>
		<div class="bcell_mid paint_itemb"></div>
	</div>
	<div id="paint_colors" class="sysmenu paint_options paint_back_menu paint_shadow paint_box">
		<?php echo paintColors(); ?>
		<div class="clear"></div>
	</div>
	<div id="paint_new" class="sysmenu paint_options paint_back_menu paint_shadow paint_box_background">
		<?php echo paintBackground(); ?>
		<div class="clear"></div>
	</div>
	<div id="paint_brush" class="sysmenu paint_options paint_back_menu paint_shadow paint_box_brush pad15">
		<p class="bmargin10 centered_element" id="paint_size">10px</p>
		<div id="paint_slider"></div>
	</div>
</div>
<div class="paint_table">
	  <div onmousedown="closePaint();" id="sketchpad"></div>
</div>
<div class="btable paint_back_menu">
	<div class="bcell_mid pad10" onclick="sendPaintIt();"><button class="reg_button theme_btn"><i class="fa fa-paper-plane"></i> <?php echo $lang['send']; ?></button></div>
	<div class="bcell_mid"></div>
</div>