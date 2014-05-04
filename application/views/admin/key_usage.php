        <div class="span9 mainContent" id="admin-panel">
			<h2>Key Usage</h2>
			
			<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
			
			<?php 
			if($count == 0) { 
				echo "No addresses have been created from the master public key.";
			} else { ?>
			<div class='row-fluid'>
				<div class='row-fluid'>
					<div class='span3'>Created Addresses</div>
					<div class='span5'><?php echo $count; ?></div>
				</div>
				<br />
				
				<div class='row-fluid'>
					<div class='row-fluid'>
						<div class='span1'><strong>#</strong></div>
						<div class='span2'><strong>Usage</strong></div>
						<div class='span4'><strong>Address</strong></div>
					</div>
				<?php foreach($records as $record ) { ?>
					<div class='row-fluid'>
						<div class='span1'><?php echo $record['iteration']; ?></div>
						<div class='span2'><?php echo ($record['usage'] == 'order') ? anchor('admin/order/'.$record['order_id'], 'Order #'.$record['order_id']) : anchor('user/'.$record['fees_user_hash'], 'User Fees'); ?></div>
						<div class='span4'><?php echo $record['address']; ?></div>
					</div>
				<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
