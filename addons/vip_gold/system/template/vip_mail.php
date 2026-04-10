<?Php
$sale = $boom['sale'];
$user = $boom['user'];

$type = 'Personal';
$donation = 0;

if(!empty($user)){
	$type = 'Donation';
	$donation = 1;
}
?>
<html>
<head>
</head>
<body>
	<div>
		<div>
			<p>hi,</p>
			<p>Congratulation you just made a VIP sale. Below you can see the transaction details.</p>
		</div>
		<div>
			<u><h3>Transaction details</h3></u>
			<b>Site :</b> <?php echo $data['title']; ?><br>
			<b>Type :</b> <?php echo $type; ?><br>
			<b>Description :</b> <?php echo vipPlanName($sale['plan']); ?><br>
			<b>Transaction id :</b> <?php echo $sale['order_id']; ?><br>
			<b>Status :</b> <?php echo $sale['status']; ?>
		</div>
		<br>
		<div>
			<u><h3>Buyer details</h3></u>
			<b>Username :</b> <?php echo $data['user_name']; ?><br>
			<b>User id :</b> <?php echo $data['user_id']; ?><br>
			<b>Email :</b> <?php echo $data['user_email']; ?><br>
		</div>
		<br>
		<?php if($donation == 1){ ?>
		<div>
			<u><h3>Receiver details</h3></u>
			<b>Username :</b> <?php echo $user['user_name']; ?><br>
			<b>User id :</b> <?php echo $user['user_id']; ?><br>
			<b>Email :</b> <?php echo $user['user_email']; ?><br>
		</div>
		<br>
		<?php } ?>
	</div>
</body>
</html>