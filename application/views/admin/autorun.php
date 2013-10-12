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
			
<?php foreach($jobs as $index => $job) { 
	if($index == 'price_index' && $config['price_index'] == 'Disabled')
		continue;
?>
			<div class="row-fluid">
			  <div class="span3"><?php echo $job['name']; ?></div>
			  <div class="span4"><?php 
			  
			  if($job['interval'] == '0'){
			      echo 'Disabled';
		      } else {	
				  echo $job['interval']." ".$job['interval_type']; 
			  }?></div>
			</div>
<?php } ?>
		  </div>
		</div>
