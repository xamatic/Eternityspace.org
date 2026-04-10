<?php
$load_addons = 'vip';
require_once('../../../system/config_addons.php');

if(!isset($_POST['tdetails'])){
	die();
}
$order = escape($_POST['tdetails']);
$get_order = $mysqli->query("
SELECT *,
(SELECT user_name FROM boom_users WHERE user_id = userid) as buyer,
(SELECT user_name FROM boom_users WHERE user_id = userp) as processor
FROM vip_transaction
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
if(empty($result['processor'])){
	$result['processor'] = 'N/A';
}
?>
<div class="modal_content">
	<div class="listing_element info_vip blist">
		<div class="listing_title">Buyer</div>
		<div class="listing_text"><?php echo $result['buyer']; ?></div>
	</div>
	<?php if($result['userp'] != ''){ ?>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Process by</div>
		<div class="listing_text"><?php echo $result['processor']; ?></div>
	</div>
	<?php } ?>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Purchase description</div>
		<div class="listing_text"><?php echo vipPlanName($result['plan']); ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Purchase price</div>
		<div class="listing_text"><?php echo $result['price']; ?> <?php echo $result['currency']; ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Order number</div>
		<div class="listing_text"><?php echo $result['order_id']; ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Invoice number</div>
		<div class="listing_text"><?php echo $result['invoice']; ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Account id</div>
		<div class="listing_text"><?php echo $result['email']; ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Payment source</div>
		<div class="listing_text"><?php echo $result['gateaway']; ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Transaction status</div>
		<div class="listing_text"><?php echo $result['status']; ?></div>
	</div>
	<div class="listing_element info_vip blist">
		<div class="listing_title">Transaction date</div>
		<div class="listing_text"><?php echo vipDate($result['vdate']); ?></div>
	</div>
</div>
<div class="modal_control">
	<button class="default_btn reg_button cancel_modal"><?php echo $lang['close']; ?></button>
</div>