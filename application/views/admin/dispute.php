	    <div class="span9 mainContent" id="dispute-form">
			<h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Dispute Order #<?php echo $dispute['order_id']; ?></h2>

			<?php if(isset($returnMessage)) { ?>
			<div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
			<?php } ?>
		  
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
				<?php echo form_open('admin/dispute/'.$current_order['id'], array('class' => 'form-horizontal')); ?> 
					<div class='row-fluid'>
						<div class="span2"><strong>Initial Dispute</strong></div>
						<div class="span5"><?php echo nl2br($dispute['dispute_message']); ?></div>
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

			<div class='well'>
				<h4>Resolution</h4>
				<?php echo form_open('admin/dispute/'.$current_order['id'], array('class' => 'form-horizontal')); ?> 	
					<div class='row-fluid'>
						<div class='span6'>
							<div class='row-fluid'>
								<div class='span6'><strong>Order Price:</strong></div>
								<div class='span6'><?php echo $coin['symbol']." ".$current_order['order_price']; ?></div>
							</div>
							<div class='row-fluid'>
								<div class='span6'><strong>Transaction Fee:</strong></div>
								<div class='span6'><?php echo $coin['symbol']." ".$transaction_fee; ?></div>
							</div>
							<div class='row-fluid'>
								<div class='span6'><strong>Admin Fee's:</strong></div>
								<div class='span6'><?php echo $coin['symbol']." ".$admin_fee; ?></div>
							</div>
							<div class='row-fluid'>
								<div class='span6'><strong>User Funds:</strong></div>
								<div class='span6'><?php echo $coin['symbol']." ".$user_funds; ?></div>
							</div>
						</div>
						
						<div class='span6'>
							<div class='row-fluid'>
								<?php if($dispute['final_response'] == '1') { ?>
									<b>This dispute <?php echo ($current_order['vendor_selected_escrow'] == '1') ? 'has been closed as the transaction has been broadcast.' : 'has been closed.'; ?></b>
								<?php } else { ?>
										<?php if($current_order['vendor_selected_escrow'] == '1') { ?>
									
									<div class='row-fluid'>
										<div class='span3'><strong>Pay <?php echo $current_order['buyer']['user_name']; ?>:</strong></div>
										<div class='span6'><input type='text' name='pay_buyer' value='' /></div>
									</div>
									<div class='row-fluid'>
										<div class='span3'><strong>Pay <?php echo $current_order['vendor']['user_name']; ?>:</strong></div>
										<div class='span6'><input type='text' name='pay_vendor' value='' /></div>
									</div>
										<?php if(isset($amount_error)) echo $amount_error."<br />"; ?>
									<div class='row-fluid'>Distribute the entire User Funds balance appropriately between the two users.</div>
									
										<?php } else {	?>
									This order was paid up-front!
									<?php } ?>
								<?php } ?>
							</div>
						</div>					
					</div>
					<?php if($dispute['final_response'] == '0') { ?>
					<div class="form-actions">
						<input type='submit' name='resolve_dispute' value='<?php echo ($current_order['vendor_selected_escrow'] == '1') ? 'Propose Resolution' : 'Close Dispute'; ?>' class='btn btn-primary' />
					</div>					
					<?php } ?>
				</form>
			</div>
		</div>
