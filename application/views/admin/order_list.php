	    <div class="span9 mainContent" id="admin-order-list">
			<h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Order List</h2>

			<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
			
			<?php if($orders == FALSE) { ?>
			There are no orders at this time.
			<?php } else { ?>
			<br />
			
			<div class='row-fluid offset1'>
				<div class='row-fluid'>
					<div class='span1'><strong>#</strong></div>
					<div class='span2'><strong>Vendor</strong></div>
					<div class='span2'><strong>Buyer</strong></div>
					<div class='span2'><strong>Price</strong></div>
					<dev class='span1'><strong>Step</strong></dev>
					<div class='span2'><strong>Confirmed Date</strong></div>
				</div>
			<?php	foreach($orders as $order) { ?>
				<div class='row-fluid'>
					<div class='span1'><?php echo anchor('admin/order/'.$order['id'],'#'.$order['id']); ?></div>
					<div class='span2'><?php echo anchor('user/'.$order['vendor']['user_hash'], $order['vendor']['user_name']); ?></div>
					<div class='span2'><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></div>
					<div class='span2'><?php echo $coin['symbol']; ?> <?php echo $order['order_price']; ?></div>
					<div class='span1'><?php echo $order['progress']; ?></div>
					<div class='span2'><?php echo $order['time_f']; ?></div>
				</div>
			<?php   } ?>
			</div>
			<?php } ?>
				
		</div>
