	    <div class="span9 mainContent" id="admin-dispute-form">
		  <h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Dispute Order #<?php echo $dispute['order_id']; ?></h2>
			
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
		  
		  <div class="row-fluid">
		    <div class="span2">Last Update</div>
		    <div class="span5"><?php echo $dispute['last_update_f']; ?></div>
		  </div>
		  
		  <div class="row-fluid">
	  	    <div class="span2">Items</div>
		    <div class="span9"><ul><?php foreach($current_order['items'] as $item) { ?>
			  <li><?php echo $item['quantity']." x ".$item['name']; ?></li>
		  <?php } ?></ul></div>
          </div>
		  
		  <div class="row-fluid">
		    <div class="span2">Message</div>
		    <div class="span5"><?php echo $dispute['dispute_message']; ?></div>
		  </div>		  
		  
		  <div class="row-fluid">
			<div class="span2">Response</div>
			<div class="span5"><textarea name='dispute_message' rows="5" class='span15'></textarea></div>
		  </div>
		  
		</div>
