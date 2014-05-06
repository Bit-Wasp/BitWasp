		<div class="span9 mainContent" id="issue_refund">
			<h2>Request Early Finalization: Order <?php echo $order['id']; ?></h2>
			
			<?php if(isset($returnMessage)) { ?>
			<div class='alert<?php echo (isset($success)) ? ' alert-success' : ''; ?>'><?php echo $returnMessage; ?></div>			
			<?php } ?>

			
			<?php echo form_open('orders/finalize_early/'.$order['id'], array('class' => 'form-horizontal')); ?>
			<div class='well span9' >
				If you need to, you can request early finalization for this order. <?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?> will be asked to sign the transaction immediately. Once this is done, you can sign and broadcast to claim the funds. <br /><br />
				<div class='row-fluid'>
					<div class='span5'>
						<ul>
						<?php foreach($order['items'] as $item) { ?><li><?php echo $item['quantity']; ?> x <?php echo ($item['hash'] !== 'removed') ? anchor('item/'.$item['hash'], $item['name']) : $item['name']; ?></li>
						<?php } ?></ul>
					</div>
					<div class='span7'>
						<div class='row-fluid'>
							<div class='span4'>Order Amount</div>
							<div class='span8'>							
							<?php if($local_currency['id'] !== '0') {
										echo "~{$local_currency['symbol']}".number_format($order['total_paid']*$local_currency['rate'], 2)." / ";
									}
									echo $order['currency']['symbol']." ".number_format($order['total_paid'], 8); ?>
							</div>
						</div>
						<div class='row-fluid'>
							<div class='span4'>Fees</div>
							<div class='span8'><?php echo $coin['symbol']; ?> <?php echo $order['fees']; ?></div>
						</div>
					</div>
				</div>
				<br />
				
				<div class='row-fluid'>
					<div class='span4'>Are you sure?</div>
					<div class='span6'>
						<div class='row-fluid'>
							<input type='radio' name='upfront' value='0'>  No<br />
							<input type='radio' name='upfront' value='1'>  Yes
						</div>
					</div>
					<?php echo form_error('refund'); ?>
				</div>
				
				<div class="form-actions">
					<input type='submit' class="btn btn-primary" name='request_FE' value='Continue' />
					<?php echo anchor('orders', 'Cancel', 'title="Cancel" class="btn"');?>
				</div>
			</div>
		</div>
