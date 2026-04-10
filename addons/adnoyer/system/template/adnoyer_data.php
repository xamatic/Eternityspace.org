<div class="sub_list_item blisting adnoyer<?php echo $boom['adnoyer_id']; ?>">
	<div class="sub_list_content hpad5">
		<?php echo $boom['adnoyer_title']; ?>
	</div>
	<div onclick="editAdnoyer(<?php echo $boom['adnoyer_id']; ?>);" class="sub_list_option">
		<i class="fa fa-edit"></i>
	</div>
	<div onclick="deleteAdnoyer(<?php echo $boom['adnoyer_id']; ?>);" class="sub_list_option">
		<i class="fa fa-times"></i>
	</div>
</div>