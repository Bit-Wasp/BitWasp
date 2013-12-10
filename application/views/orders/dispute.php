        <div class="span9 mainContent" id="dispute-transaction">
          <h2>Dispute: Order #<?php echo $current_order['id']; ?></h2>
          <Br />
          <?php if(isset($returnMessage)) { ?>
          <div class='alert'><?php echo $returnMessage; ?></div>
          <?php } ?>
          
          <?php 
          $action = 'order'; if($role == 'vendor') $action .= 's'; $action.= '/dispute/'.$current_order['id']; 
          $back = 'order';   if($role == 'vendor') { $back .= 's'; } else { $back .= '/list'; }
          
          if($form == TRUE) {
			
		  echo form_open($action, array('class' => 'form-horizontal')); ?>
            
            <div class="row-fluid">
			  <div class="span2"><strong>Order Date</strong></div>
			  <div class="span9"><?php echo $current_order['created_time_f']; ?></div>
            </div>
            
            <div class="row-fluid">
	  	      <div class="span2"><strong>Items</strong></div>
		      <div class="span9"><ul><?php foreach($current_order['items'] as $item) { ?>
				<li><?php echo $item['quantity']." x ".$item['name']; ?></li>
		    <?php } ?></ul></div>
            </div>
            
            <div class="row-fluid">
			  <div class="span2"><strong>Price</strong></div>
			  <div class="span9"><?php echo $current_order['currency']['symbol'] . " " . $current_order['price'];?></div>
            </div>
            
            <div class="row-fluid">
			  <div class="span2"><strong><?php echo ucfirst($other_role); ?></strong></div>
			  <div class="span9"><?php echo anchor('user/'.$current_order[$other_role]['user_hash'], $current_order['vendor']['user_name']); ?></div>
            </div>
            
            <div class="row-fluid">
			  <div class="span2"><strong>Dispute Reason</strong></div>
			  <div class="span5">
			    <textarea name='dispute_message' rows="5" class='span15'></textarea>
		 	  </div>
		      <span class='help-inline'><?php echo form_error('dispute_message'); ?></span>
            </div>
            
            <div class="form-actions">
	          <input type='submit' class="btn btn-primary" value='Submit' /> 
	          <?php echo anchor($back, 'Cancel', 'class="btn"'); ?>
            </div>      
            
          </form>             
<?php } else { ?>

          <div class="row-fluid">
			<div class="span2">Order Date</div>
			<div class="span9"><?php echo $current_order['created_time_f']; ?></div>
          </div>

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
			<div class="span9"><?php echo $current_order['currency']['symbol'] . " " . $current_order['price'];?></div>
          </div>
		  
		  <div class="row-fluid">
		    <div class="span2">Message</div>
		    <div class="span5"><?php echo $dispute['dispute_message']; ?></div>
		  </div>
		  
		  <div class="row-fluid">
		    <div class="span2">Admin Message</div>
		    <div class="span5"><?php echo $dispute['admin_message']; ?></div>
		  </div>
<?php } ?>
        </div>
