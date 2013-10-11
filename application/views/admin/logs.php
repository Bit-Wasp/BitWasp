        <div class="span9 mainContent" id="admin-panel">
		  
		  <?php echo $nav; ?>
			
		  <div class="container-fluid">
			<div class="row-fluid">
			  <div class="span3">Transaction Count</div>
			  <div class="span4"><?php echo $transaction_count; ?></div>
			</div>
			
			<div class="row-fluid">
			  <div class="span3">Orders Count</div>
			  <div class="span4"><?php echo $order_count; ?></div>
			</div>
			
			<div class="row-fluid">
			  <div class="span3">Messages Count</div>
			  <div class="span4"><?php echo $messages_count; ?></div>
			</div>
			
<?php foreach($intervals as $interval) { 
	if($interval['index'] == 'price_index' && $config['price_index'] == 'Disabled')
		continue;
?>
			<div class="row-fluid">
			  <div class="span3"><?php echo $interval['name']; ?></div>
			  <div class="span4"><?php 
			  
			  if($interval['interval'] == '0'){
			      echo 'Disabled';
		      } else {	
				  if($interval['index'] == 'price_index') {
					  echo round($interval['interval']*60)." minutes";
				  } else if($interval['index'] == 'user_inactivity') {
					  echo ($interval['interval'])." hours";
				  } else if($interval['index'] == 'transaction_history' || $interval['index'] == 'message_history') {
					  echo ($interval['interval']/24)." days";
				  } 
			  }?></div>
			</div>
<?php } ?>
		  </div>
		</div>
