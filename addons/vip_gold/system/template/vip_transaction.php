<div onclick="vipDetails(<?php echo $boom['id']; ?>);" class="sub_list_item blisting">
	<div class="sub_list_avatar">
		<img class="lazy admin_user<?php echo $boom['user_id']; ?>" data-src="<?php echo checkAvatar($boom['user_tumb']); ?>" src="<?php echo imgLoader(); ?>"/>
	</div>
	<div class="sub_list_content hpad10">
		<p class="bold"><?php echo checkUsername($boom['user_name']); ?></p>
		<p class="text_small"><?php echo vipPlanName($boom['plan']); ?></p>
		<p class="text_xsmall sub_text"><?php echo vipDate($boom['vdate']); ?></p>
	</div>
</div>