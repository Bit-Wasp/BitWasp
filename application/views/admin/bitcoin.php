        <div class="span9 mainContent" id="admin-bitcoin-panel">
		  
		  <?php echo $nav; ?>
			
          <?php if(isset($returnMessage)) { ?>
          <div class='alert alert-success'><?php echo $returnMessage; ?></div><?php } ?>
			
		  <div class="container-fluid">
<?php 
if($bitcoin_info == NULL) { ?>
		    <div class="row-fluid">
			  <span class="span3">Bitcoin Status</span>
			  <span class="span7">The bitcoin daemon is currently disabled.</span>
		    </div>
<?php } else { ?>
		    <div class="row-fluid">
		  	  <span class="span3">Bitcoin Status</span>
			  <span class="span7">Bitcoind is currently running<?php if($bitcoin_info['testnet'] == TRUE) echo ' <b>in the testnet</b>'; ?>.</span>
		    </div>
		  
		    <div class="row-fluid">
			  <span class="span3">Bitcoin Version</span>
			  <span class="span7"><?php echo $bitcoin_info['version']; ?></span>
		    </div>
<?php } ?>
			  
			<div class="row-fluid">
			  <span class="span3">Latest Block</span>
			  <span class="span7"><?php echo $latest_block['number']; ?></span>
			</div>  
			
			<div class="row-fluid">
			  <span class="span3">Transactions Processed</span>
			  <span class="span7"><?php echo $transaction_count; ?></span>
			</div>
		
			<div class="row-fluid">
			  <span class="span3">Delete Transactions After</span>
			  <span class="span7"><?php echo ($config['delete_transactions_after'] == '0') ? 'Disabled' : $config['delete_transactions_after'].' days'; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Use A Bitcoin Price Index?</span>
			  <span class="span4"><?php if($bitcoin_index == '') { echo 'Disabled'; }
			  else { echo $bitcoin_index; } ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Wallet Backup Method</span>
			  <span class="span4"><?php echo $config['balance_backup_method']; ?></span>
			</div>
			  
			<?php 
			foreach($accounts as $acc => $bal) { 
			if($acc !== '') { 
				$var = 'max_'.$acc.'_balance'; 
			?>
			<div class="row-fluid">
			  <span class="span3"><?php echo ucfirst($acc); ?> balance</span>
			  <span class="span2">BTC <?php echo $bal; ?></span>
			  <span class="span5"><?php echo (isset($config[$var]) && $acc !== '' && $acc !== 'topup' && $config[$var] > 0) ? 'Backup balances exceeding BTC '.$config[$var]."." : '' ; ?></span>
			</div>

			<?php } } ?>
			  
		  </div>
		</div>
