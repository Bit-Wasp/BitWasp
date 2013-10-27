	    <div class="span9 mainContent" id="admin-dispute-form">
		  <h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Dispute Order #<?php echo $dispute['order_id']; ?></h2>
			
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
		  
		  <?php echo form_open('admin/dispute/'.$current_order['id'], array('class' => 'form-horizontal')); ?> 
		  
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
			  <div class="span2">Price</div>
			  <div class="span5"><?php echo $current_order['currency']['symbol'] . " " . $current_order['price']; ?></div>
		    </div>
<?php 
if($dispute['disputing_user_id'] == 'buyer') { ?>		  
			<div class="row-fluid">
			  <div class="span2">Vendor</div>	
			  <div class="span7"><?php echo anchor('user/'.$current_order['vendor']['user_hash'], $current_order['vendor']['user_name']); ?></div>
			</div>
<?php } else { ?>
			<div class="row-fluid">
			  <div class="span2">Buyer</div>	
			  <div class="span7"><?php echo anchor('user/'.$current_order['buyer']['user_hash'], $current_order['buyer']['user_name']); ?></div>
			</div>
<?php } ?>			
			
			<div class="row-fluid">
			  <div class="span2">Disputing User</div>	
			  <div class="span7"><?php echo anchor('user/'.$disputing_user['user_hash'], $disputing_user['user_name']); ?></div>
			</div>
			
		    <div class="row-fluid">
		      <div class="span2">Reason for Dispute</div>
		      <div class="span5"><?php echo nl2br($dispute['dispute_message']); ?></div>
		    </div>
		  
		    <div class="row-fluid">
			  <div class="span2">Response</div>
			  <div class="span5"><textarea name='admin_message' rows="5" class='span15'><?php echo str_replace('Awaiting Response', '', $dispute['admin_message']); ?></textarea></div>
		    </div>
		    <span class='help-inline'><?php echo form_error('admin_message'); ?></span>
		  
		    <div class="form-actions">
			  <input type='submit' name='update_message' value='Update' class='btn btn-primary' />
			  <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
 		    </div>
 
		  </form>
		</div>
