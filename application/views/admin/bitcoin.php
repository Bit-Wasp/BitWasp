        <div class="span9 mainContent" id="admin-bitcoin-panel">
		  
		  <?php echo $nav; ?>
			
          <?php if(isset($returnMessage)) { ?>
          <div class='alert alert-success'><?php echo $returnMessage; ?></div><?php } ?>
			
		  <div class="container-fluid">
<?php 
if($bitcoin_info == NULL) { ?>
		    <div class="row-fluid">
			  <span class="span3"><?php echo $coin['name']; ?> Status</span>
			  <span class="span7">Unable to make an outbound connection to the <?php echo strtolower($coin['name']); ?> daemon.</span>
		    </div>
<?php } else { ?>
		    <div class="row-fluid">
		  	  <span class="span3"><?php echo $coin['name']; ?> Status</span>
			  <span class="span7"><?php echo $coin['name']; ?>d is currently running<?php if($bitcoin_info['testnet'] == TRUE) echo ' <b>in the testnet</b>'; ?>.</span>
		    </div>
		  
		    <div class="row-fluid">
			  <span class="span3"><?php echo $coin['name']; ?> Version</span>
			  <span class="span7"><?php echo $bitcoin_info['version']; ?></span>
		    </div>
<?php } ?>
			  
			<div class="row-fluid">
			  <span class="span3">Use A <?php echo $coin['name']; ?> Price Index?</span>
			  <span class="span7"><?php if($bitcoin_index == '') { echo 'Disabled'; }
			  else { echo $bitcoin_index; } ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Key Usage</span>
			  <span class="span7">Used <?php echo $key_usage_count; ?> times. <?php echo anchor('admin/key_usage','View Usage'); ?></span>
			</div>
		  </div>
		</div>
