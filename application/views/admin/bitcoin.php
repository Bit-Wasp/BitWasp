        <div class="span9 mainContent" id="admin-bitcoin-panel">
		  
		  <?php echo $nav; ?>
			
		  <div class="container-fluid">
			<div class="row-fluid">
			  <span class="span3">Latest Block</span>
			  <span class="span7"><?php echo $latest_block['number']; ?></span>
			</div>  
			
			<div class="row-fluid">
			  <span class="span3">Transactions Processed</span>
			  <span class="span7"><?php echo $transaction_count; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Use A Bitcoin Price Index?</span>
			  <span class="span4"><?php if($bitcoin_index == '') { echo 'Disabled'; }
			  else { echo $bitcoin_index; } ?></span>
			</div>
			  
			<?php 
			foreach($accounts as $acc => $bal) { 
			if($acc !== '') { ?>
			<div class="row-fluid">
			  <span class="span3"><?php echo ucfirst($acc); ?> balance</span>
			  <span class="span7">BTC <?php echo $bal; ?></span>
			</div>
			
			<?php } } ?>
			  
		  </div>
		</div>
