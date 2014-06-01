		<div class="span9 mainContent" id="vendor_public_keys">
			<h2>Order Details: #<?php echo $order['id']; ?></h2>

			<?php if(isset($returnMessage)) { ?>
			<div class='alert<?php echo (isset($success)) ? ' alert-success' : ''; ?>'><?php echo $returnMessage; ?></div>			
			<?php } ?>

				<div class="row-fluid">
					<div class='span6'>				
						<div class="row-fluid">
							<div class="span5 offset1">Vendor</div>
							<div class="span6"><?php echo anchor('user/'.$order['vendor']['user_hash'], $order['vendor']['user_name']); ?></div>
						</div>			
						<div class="row-fluid">
							<div class="span5 offset1">Buyer</div>
							<div class="span6"><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></div>
						</div>			
						<div class="row-fluid">
							<div class="span5 offset1">Items Cost</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
								echo $current_user['currency']['symbol'] . number_format($order['price']*$current_user['currency']['rate'], 2)." / "; 
							}
							echo $order['currency']['symbol']." ".number_format($order['price'], 8); ?></div>
						</div>
						
						<div class="row-fluid">
							<div class="span5 offset1">Shipping Cost</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
									echo $current_user['currency']['symbol'].number_format($fees['shipping_cost']*$current_user['currency']['rate'], 2)." / ";
								}
							echo $order['currency']['symbol']." ".number_format($fees['shipping_cost'], 8); ?></div>
						</div>
						
						<div class="row-fluid">
							<div class="span5 offset1">Site's Fee</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
								echo $current_user['currency']['symbol'].number_format($fees['fee']*$current_user['currency']['rate'], 2). " / ";
							}
							echo $order['currency']['symbol']." ".number_format($fees['fee'], 8); ?></div>
						</div>
						
						<?php if($current_user['user_role'] !== 'Buyer' ) { ?>
						<div class="row-fluid">
							<div class="span5 offset1">Extra Fees</div>
							<div class="span6"><?php if($current_user['currency']['id'] !== '0') {
								echo $current_user['currency']['symbol'].number_format($fees['escrow_fees']*$current_user['currency']['rate'], 2). " / ";
							}
							echo $order['currency']['symbol']." ".number_format($fees['escrow_fees'], 8); ?></div>
						</div>
						<?php } ?>
						
						<div class="row-fluid">
							<div class="span5 offset1"><?php
							if($current_user['user_role'] == 'Vendor') {
								echo 'Total Payment';
							} else {
								echo 'Total Cost';
							} ?></div>
							<div class="span6">
								<?php if($current_user['currency']['id'] !== '0') {
									echo $current_user['currency']['symbol'].number_format($order['order_price']*$current_user['currency']['rate'], 2)." / ";
								}
								echo $order['currency']['symbol']." ".$order['order_price']; ?>
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
				
				<?php if($order['address'] !== '') { ?>
				<div class='row-fluid'>
					<div class='well'>
						<?php // payment QR code
						if(isset($qr)) { ?>
						<div class='row-fluid'>
							<div class='offset4'><a href='<?php echo $payment_url; ?>'><img src='data:image/png;base64,<?php echo $qr; ?>' /></a></div>
						</div>
						<?php } ?>
						
						<div class='row-fluid'>
							<div class='span3'>Order Address</div>
							<div class='span4'><?php echo $order['address']; ?></div>
						</div>
						<div class='row-fluid'>
							<div class='span3'>Redeem Script</div>
							<div class='span8'><textarea class='span12'><?php echo $order['redeemScript']; ?></textarea></div>
						</div>
						<div class='row-fluid'>
							<div class='span3'>Import Command</div>
							<div class='span8'><textarea class='span12'>addmultisigaddress 2 '["<?php echo $order['buyer_public_key']; ?>","<?php echo $order['vendor_public_key']; ?>","<?php echo $order['admin_public_key']; ?>"]'</textarea></div>
						</div>
					</div>
				</div>
				<?php } ?>
				
				<?php if($order['final_transaction_id'] !== '') { ?>
				<div class='row-fluid'>
					<div class='well'>
						<div class='row-fluid'>
							<div class='span3'>Transaction ID</div>
							<div class='span8'><?php echo $order['final_transaction_id']; ?></div>
						</div>
					</div>
				</div>
				<?php } else if($order['paid_time'] !== '') { ?>
				<div class='row-fluid'>
					<div class='well'>
						<div class='row-fluid'>
							<div class='span3'><?php echo ($order['partially_signed_transaction'] !== '') ? 'Partially Signed Transaction:' : 'Unsigned Transaction:'; ?></div>
							<div class='span8'>
								<textarea class='span12'><?php echo ($order['partially_signed_transaction'] !== '') ? $order['partially_signed_transaction'] : $order['unsigned_transaction'].$order['json_inputs']; ?></textarea>
							</div>
						</div>

                        <?php if($strange_address == TRUE) { ?>
                            <div class="row-fluid">
                                <div class="span8 offset3">
                                    Warning! This transaction has been tampered with, do not sign.
                                </div>
                            </div>
                        <?php } ?>

						<?php if($order['partially_signed_transaction'] !== '') { ?>
						<div class='row-fluid'>
							<div class='span8 offset3'>
								Signed by <?php echo anchor('user/'.$signer['user_hash'], $signer['user_name']); ?> <?php echo $order['partially_signed_time_f']; ?>. Sign and broadcast to complete payment.
							</div>
						</div>
						<?php } ?>

						<div class='row-fluid'>
							<div class='span3'>Paying:</div>
							<div class='span8'>
							<?php foreach($raw_tx['vout'] as $arr) { 
								$addr = $arr['scriptPubKey']['addresses'][0];
								if($addrs[$addr] !== 'admin') { ?>
								<div class='row-fluid'>
								<div class='span2'><?php echo anchor('user/'.$order[$addrs[$addr]]['user_hash'], $order[$addrs[$addr]]['user_name']); ?></div>
								<div class='span2'><?php echo $coin['symbol'] . $arr['value'] . " ". $addr; ?></div>
								</div>
								<?php } else { ?>
								<div class='row-fluid'>
								<div class='span2'>Fees</div>
								<div class='span2'><?php echo $coin['symbol'] . $arr['value'] . " ". $addr; ?></div>
								</div>
								<?php } ?>
							<?php } ?>
							</div>
						</div>
						<?php echo form_open($action_page, 'class="form-horizontal"'); ?>
						<?php if($display_form == TRUE) { ?>

							<div class='row-fluid'>
								<div class='span3'>Paste Signed Transaction</div>
								<div class='span8'>
									<textarea name='partially_signed_transaction' class='span12'></textarea>
								</div>
							</div>
							<?php if(isset($invalid_transaction_error)) echo '<span class="help-inline">'.$invalid_transaction_error.'</span>'; ?>
							<?php echo form_error('partially_signed_transaction'); ?>
						
						<?php } ?>
						
							<div class="form-actions">
								<?php if($display_form == TRUE) { ?><input type='submit' name='submit_signed_transaction' class="btn btn-primary" value='Submit Transaction' /><?php } ?>
								<?php if($can_finalize_early) { ?><?php echo anchor('orders/finalize_early/'.$order['id'], 'Finalize Early?', 'title="Request early finalization" class="btn"');?><?php } ?>
								<?php if($can_refund) { ?><?php echo anchor('orders/refund/'.$order['id'], 'Issue Refund?', 'title="Request early finalization" class="btn"');?><?php } ?>
								<?php echo anchor($cancel_page, 'Back', 'title="Bac" class="btn"');?>								
							</div>
						</form>
					</div>
				</div>
				<?php } ?>
			</form>
		</div>
