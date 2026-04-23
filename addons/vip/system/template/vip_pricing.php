<?php $vip_price = vipPrice($boom); ?>
<?php if($vip_price > 0){ ?>

<div class="vip_plan sub_list_item blist" onclick="vipPlan(this, <?php echo $boom; ?>);">
	<div class="bcell_mid vip_checkbox">
		<i class="fa-regular fa-circle"></i>
	</div>
	<div class="bcell_mid vip_plan_title bold hpad5">
		<?php echo vipPlanName($boom); ?>
	</div>
	<div class="bcell_mid_right vip_price_cell rtl_aleft">
		<span class="vip_price"><?php echo $vip_price; ?></span>
	</div>
</div>
<?php } ?>