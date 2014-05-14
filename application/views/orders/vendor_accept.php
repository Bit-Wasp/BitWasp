		<div class="span9 mainContent" id="vendor_public_keys">
			<h2>Accept Order <?php echo $order['id']; ?></h2>
			<?php if(isset($returnMessage)) { ?>
			<div class='alert<?php echo (isset($success)) ? ' alert-success' : ''; ?>'><?php echo $returnMessage; ?></div>			
			<?php } ?>

			<?php
			
			if($available_public_keys == FALSE) { 
				echo 'You have no public keys available to complete this order. '.anchor('accounts/public_keys','Click here to add some.');  
			} else {
				echo "A public key will automatically be used to create the order address.";
			}?><br /><br />
		
			<?php echo form_open('orders/accept/'.$order['id'], array('class' => 'form-horizontal')); ?>
				<div class="row-fluid">
					<div class='span6'>				
						<div class="row-fluid">
							<div class="span5 offset1">Buyer</div>
							<div class="span6"><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></div>
						</div>			
						<div class="row-fluid">
							<div class="span5 offset1">Price</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
								echo $current_user['currency']['symbol'] . ' ' . number_format($order['price']*$current_user['currency']['rate'], 2)." / "; 
							}
							echo $order['currency']['symbol']." ".$order['price']; ?></div>
						</div>
						
						<div class="row-fluid">
							<div class="span5 offset1">Shipping Cost</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
									echo "{$current_user['currency']['symbol']} ".number_format($fees['shipping_cost']*$current_user['currency']['rate'], 2)." / ";
								}
							echo $order['currency']['symbol']." ".number_format($fees['shipping_cost'], 8); ?></div>
						</div>
						
						<div class="row-fluid">
							<div class="span5 offset1">Fee</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
								echo $current_user['currency']['symbol']." ".number_format($fees['fee']*$current_user['currency']['rate'], 2). " / ";
							}
							echo $order['currency']['symbol']." ".number_format($fees['fee'], 8); ?></div>
						</div>

						<div class="row-fluid">
							<div class="span5 offset1">Total</div>
							<div class="span6">
								<?php if($current_user['currency']['id'] !== '0') {
									echo "{$current_user['currency']['symbol']} ".number_format(($order['price']+$fees['total'])*$current_user['currency']['rate'], 2)." / ";
								}
								echo $order['currency']['symbol']." ".number_format($order['price']+$fees['total'], 8); ?>
							</div>
						</div>											
					</div>
											
					<div class='span6'>
						<strong>Items</strong>
						<ul><?php foreach($order['items'] as $item) { ?>
							<li><?php echo $item['quantity'] . ' x ' . anchor('item/'.$item['hash'], $item['name']); ?></li>
						<?php } ?></ul>	
					</div>
				</div>
				<br />
				
				<div class="row-fluid">
					<div class="row-fluid">
						<div class="span10">Your order will be <strong><?php echo $order_type; ?></strong>.</div>
					</div>
				</div>
				<div class="form-actions">
					<input type='submit' class="btn btn-primary" name='vendor_accept_order' value='Accept Order' onclick='messageEncrypt()' />
					<?php echo anchor('orders', 'Cancel', 'title="Cancel" class="btn"');?>
				</div>
			</form>
		</div>
