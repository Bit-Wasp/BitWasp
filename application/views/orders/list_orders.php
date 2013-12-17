        <div class="span9 mainContent" id="order_view">
          <h2>Orders</h2>
<?php if($escrow_balance > 0) { ?>
		  <div class="row-fluid">
			<div class="span3">Escrow Balance</div>
			<div class="span4">BTC <?php echo $escrow_balance; ?></div>
		  </div>
		  <br />
<?php } ?>

<?php if(is_array($new_orders) || is_array($await_finalization) || is_array($await_finalize_early) || is_array($await_dispatch) || is_array($in_dispute)) { ?>
		  <table class='table table-condensed table-hover'>
		  <thead>
			<tr>
			  <th>ID</th>
			  <th>Buyer</th>
			  <th>Value</th>
			  <th>Time</th>
			  <th>Items</th>
			  <th></th>
			</tr>
		  </thead>
		  <tbody>
<?php if(is_array($new_orders) ) { 
	foreach($new_orders as $order) { ?>
			  <tr>
				<td>#<?php echo $order['id']; ?></td>
				<td><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></td>
			    <td><?php echo $coin['symbol']; ?> <?php echo $order['price']; ?></td>
			    <td><?php echo $order['time_f']; ?></td>
			    <td><ul><?php
		foreach($order['items'] as $item) { ?><li><?php echo $item['quantity']; ?> x <?php echo anchor('item/'.$item['hash'], $item['name']); ?></li><?php } ?></ul></td>
			    <td><?php echo form_open('orders'); ?><input type='submit' name='dispatch[<?php echo $order['id']; ?>]' value='Dispatch' class="btn btn-mini" /> <input type='submit' name='finalize_early[<?php echo $order['id']; ?>]' value='Finalize Early' class="btn btn-mini" /></form></td>
			  </tr>
<?php } }?>

<?php 
if(is_array($await_dispatch)) {
	foreach($await_dispatch as $order) { ?>
			  <tr>
				<td>#<?php echo $order['id']; ?></td>
				<td><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></td>				
			    <td><?php echo $coin['symbol']; ?> <?php echo $order['price']; ?></td>
			    <td><?php echo $order['time_f']; ?></td>
			    <td>
				  <ul>
					<?php foreach($order['items'] as $item) { ?><li><?php echo $item['quantity']; ?> x <?php echo anchor('item/'.$item['hash'], $item['name']); ?></li>
<?php } ?>
				  </ul>
				</td>
			    <td><?php echo form_open('orders'); ?><input type='submit' name='dispatch[<?php echo $order['id']; ?>]' value='Confirm Dispatch' class="btn btn-mini" /></form></td>	
			  </tr>
<?php } }?>

<?php 
if(is_array($await_finalize_early)) {
	foreach($await_finalize_early as $order) { ?>
			  <tr>
			    <?php echo form_open('orders'); ?>
				<td>#<?php echo $order['id']; ?></td>
				<td><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></td>
			    <td><?php echo $coin['symbol']; ?> <?php echo $order['price']; ?></td>
			    <td><?php echo $order['time_f']; ?></td>
			    <td><ul><?php
		foreach($order['items'] as $item) { ?>
			      <li><?php echo $item['quantity']; ?> x <?php echo anchor('item/'.$item['hash'], $item['name']); ?></li>
	<?php } ?></ul></td>
			    <td>Awaiting early finalization. <input type='submit' name='cancel[<?php echo $order['id']; ?>]' value='Cancel' class='btn btn-mini'/></td>	
			    </form>
			  </tr>
<?php } } ?>

<?php 
if(is_array($await_finalization)) {
	foreach($await_finalization as $order) { ?>
			  <tr>
				<td>#<?php echo $order['id']; ?></td>
				<td><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></td>
			    <td><?php echo $coin['symbol']; ?> <?php echo $order['price']; ?></td>
			    <td><?php echo $order['time_f']; ?></td>
			    <td><ul><?php
		foreach($order['items'] as $item) { ?>
			      <li><?php echo $item['quantity']; ?> x <?php echo anchor('item/'.$item['hash'], $item['name']); ?></li>
	<?php } ?></ul></td>
			    <td>Awaiting <?php echo ($order['finalized'] == '0') ? 'finalization' : 'delivery'; ?>. <?php echo anchor('orders/dispute/'.$order['id'], 'Dispute', 'class="btn btn-mini"'); ?></td>	
			  </tr>
<?php } } ?>

<?php
if(is_array($in_dispute)) {
	foreach($in_dispute as $order) { ?>
			  <tr>
				<td>#<?php echo $order['id']; ?></td>
				<td><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></td>
			    <td><?php echo $coin['symbol']; ?> <?php echo $order['price']; ?></td>
			    <td><?php echo $order['time_f']; ?></td>
			    <td><ul><?php
		foreach($order['items'] as $item) { ?>
			      <li><?php echo $item['quantity']; ?> x <?php echo anchor('item/'.$item['hash'], $item['name']); ?></li>
	<?php } ?></ul></td>
			    <td>In dispute. <?php echo anchor('orders/dispute/'.$order['id'], 'View', 'class="btn btn-mini"'); ?></td>	
			  </tr>
<?php } } ?>

		    </tbody>
		  </table>
<?php } else { ?>
		  <p>You have no orders at present.</p>
<?php } ?>
        </div>

