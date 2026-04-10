<?php
$load_addons = 'vip_gold';
require_once('../../../../system/config_addons.php');

if(!isset($_POST['tdetails'])){
	die();
}
$order = escape($_POST['tdetails'], true);
$get_order = $mysqli->query("
SELECT *,
(SELECT user_name FROM boom_users WHERE user_id = userid) as buyer,
(SELECT user_name FROM boom_users WHERE user_id = userp) as donated
FROM boom_vip
WHERE id = '$order' 
LIMIT 1
");
if($get_order->num_rows < 1){
	echo 0;
	die();
}
$result = $get_order->fetch_assoc();
if(empty($result['buyer'])){
	$result['buyer'] = 'N/A';
}
if(empty($result['donated'])){
	$result['donated'] = 'N/A';
}
?>
<div class="modal_content">
	<div class="listing_element blist">
		<div class="listing_title">Buyer</div>
		<div class="listing_text"><?php echo $result['buyer']; ?></div>
	</div>
	<?php if($result['userp'] != $result['userid']){ ?>
	<div class="listing_element blist">
		<div class="listing_title">Donation for</div>
		<div class="listing_text"><?php echo $result['donated']; ?></div>
	</div>
	<?php } ?>
	<div class="listing_element blist">
		<div class="listing_title">Purchase description</div>
		<div class="listing_text"><?php echo vipPlanName($result['plan']); ?></div>
	</div>
	<div class="listing_element blist">
		<div class="listing_title">Purchase price</div>
		<div class="listing_text"><?php echo $result['price']; ?></div>
	</div>
	<div class="listing_element blist">
		<div class="listing_title">Transaction date</div>
		<div class="listing_text"><?php echo vipDate($result['vdate']); ?></div>
	</div>
</div>