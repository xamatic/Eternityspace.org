<?php if($boom != 'US'){ ?>
<div style="width:50%;" class="form_left">
<div class="setting_element ">
	<p class="label"><?php echo $lang['vip_state2']; ?></p>
	<input value="" id="cstate" class="full_input"/>
</div>
</div>
<div style="width:50%;" class="form_right">
	<div class="setting_element ">
		<p class="label"><?php echo $lang['vip_postal']; ?></p>
		<input id="czip" class="full_input"/>
	</div>
</div>
<?php } ?>
<?php if($boom == 'US'){ ?>
<div style="width:50%;" class="form_left">
<div class="setting_element ">
	<p class="label"><?php echo $lang['vip_state']; ?></p>
	<select id="cstate">
		<?php echo vipState(); ?>
	</select>
</div>
</div>
<div style="width:50%;" class="form_right">
	<div class="setting_element ">
		<p class="label"><?php echo $lang['vip_zip']; ?></p>
		<input id="czip" class="full_input"/>
	</div>
</div>
<?php } ?>