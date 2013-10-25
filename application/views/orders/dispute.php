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
	  	      <div class="span2"><strong>Items</strong></div>
		      <div class="span4"><ul><?php foreach($current_order['items'] as $item) { ?>
				<li><?php echo $item['quantity']." x ".$item['name']; ?></li>
		  <?php } ?></ul></div>
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
<?php } ?>              
        </div>
