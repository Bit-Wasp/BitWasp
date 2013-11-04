	    <div class="span9 mainContent" id="admin-disputes-list">
		  <h2><?php echo anchor('admin/edit/bitcoin', 'Back', 'class="btn"'); ?> Topup Addresses</h2>
			
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
		
		  <?php
if($accounts == FALSE) { ?>
There are no accounts in the wallet, or bitcoind is offline. Please check the server.
<?php } else { ?>
<p>The bitcoin wallet can be topped up by sending funds directly to an account address.</p><br />
	
<?php	foreach($accounts as $account => $info) { ?>
			<div class='row-fluid'>
			  <div class='span3'><?php echo $account; ?></div>
			  <div class='span3'>BTC <?php echo $info['balance']; ?></div>
			  <div class='span3'><?php echo $info['address']; ?></div>
			</div>
<?php } 
} ?>		 
		</div>
