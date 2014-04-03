        <div class="span9 mainContent" id="dispute-transaction">
			<h2>Dispute: Order #<?php echo $current_order['id']; ?></h2>
			<Br />
			
			<?php if(isset($returnMessage)) echo "<div class='alert'>$returnMessage</div>"; ?>

			<?php if($form == TRUE) { ?>

			<?php echo form_open($dispute_page, array('class' => 'form-horizontal')); ?>
            
				<div class='well'>
					<div class='row-fluid'>
						<div class='span6'>
							<div class="row-fluid">
							  <div class="span4"><strong>Order Date</strong></div>
							  <div class="span8"><?php echo $current_order['created_time_f']; ?></div>
							</div>
							
							<div class="row-fluid">
							  <div class="span4"><strong>Price</strong></div>
							  <div class="span8"><?php echo $current_order['currency']['symbol'] . " " . $current_order['price'];?></div>
							</div>
							
							<div class="row-fluid">
							  <div class="span4"><strong><?php echo ucfirst($other_role); ?></strong></div>
							  <div class="span8"><?php echo anchor('user/'.$current_order[$other_role]['user_hash'], $current_order['vendor']['user_name']); ?></div>
							</div>		
						</div>
						
						<div class='span6'>
							<div class="row-fluid">
								<div class="row-fluid">
									<div class="span3"><strong>Items</strong></div>
									<div class="span9"><ul><?php foreach($current_order['items'] as $item) { ?>
									<li><?php echo $item['quantity']." x ".$item['name']; ?></li>
								<?php } ?></ul></div>
								</div>							
							</div>
						</div>
					</div>
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
	          <?php echo anchor($cancel_page, 'Cancel', 'class="btn"'); ?>
            </div>      
            
          </form>             
<?php } else { ?>

			<div class='well'>
				<div class='row-fluid'>
					<div class='span6'>
						<div class="row-fluid">
							<div class="span4"><strong>Order Date</strong></div>
							<div class="span6"><?php echo $current_order['created_time_f']; ?></div>
						</div>			  
						<div class="row-fluid">
							<div class="span4"><strong>Amount Paid</strong></div>
							<div class="span6"><?php 
									echo $coin['symbol']." ".number_format($current_order['order_price'],8);
									if($current_order['currency']['id'] !== '0') 
										echo " / ".$current_order['currency']['symbol'] . " " . $current_order['price_l']; ?><br />(<?php echo (($current_order['vendor_selected_escrow'] == '1') ? 'escrow' : 'up-front'); ?>)</div>
						</div>	
						<div class="row-fluid">
							<div class="span4"><strong>Disputing User</strong></div>	
							<div class="span6"><?php echo anchor('user/'.$disputing_user['user_hash'], $disputing_user['user_name']); ?></div>
						</div>										
					</div>
					
					<div class='span6'>
						<div class="row-fluid">
							<div class="span3"><strong>Items:</strong></div>
							<div class="span6"><ul><?php foreach($current_order['items'] as $item) { ?>
								<li><?php echo $item['quantity']." x ".$item['name']; ?></li><?php } ?></ul></div>
						</div>
					</div>
				</div>
			</div>

			<div class='well'>
				<h4>Messages</h4>
				<?php echo form_open($dispute_page, array('class' => 'form-horizontal')); ?> 
					<div class='row-fluid'>
						<div class="span2"><strong>Initial Dispute</strong></div>
						<div class="span5"><?php echo $dispute['dispute_message']; ?></div>
					</div>
					<?php if(count($dispute['updates']) > 0) { ?>
					<hr />
					<div class='row-fluid'>
						<?php foreach($dispute['updates'] as $update) { ?>
						
						
						<div class='row-fluid'>
							<div class='span2'>
								<?php if($update['posting_user_id'] == '0') { ?>
									<b>Notification</b>
								<?php } else { ?>
									Posted <?php echo $update['time_f']; ?> by <?php echo anchor('user/'.$update['posting_user_hash'], $update['posting_user_name']); ?>.
								<?php } ?>
							</div>
							<div class='span10'><?php echo $update['message']; ?></div>
						
						</div>
						<br />
						<?php } ?>
					</div>
					<?php } ?>
					<?php if($dispute['final_response'] == '0') { ?>
					<hr />
					<div class='row-fluid'>
						<div class='span2'><strong>Response</strong></div>
						<div class='span9'><textarea name='update_message' class='span12'></textarea></div>
					</div>
					<?php echo form_error('update_message'); ?>
					<div class="form-actions">
						<input type='submit' name='post_dispute_message' value='Post Message' class='btn btn-primary' />
					</div>
					<?php } ?>
				</form>
			</div>
		  
<?php } ?>
        </div>
