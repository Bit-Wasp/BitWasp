        <div class="span9 mainContent" id="order_view">
          <h2>Orders</h2>
          
<?php if(isset($returnMessage)){ echo "<div class='alert'>$returnMessage</div>"; } ?>
          
<?php if($escrow_balance > 0) { ?>
		  <div class="row-fluid">
			<div class="span3">Escrow Balance</div>
			<div class="span4"><?php echo $coin['symbol']; ?> <?php echo $escrow_balance; ?></div>
		  </div>
		  <br />
<?php } ?>

<?php if(is_array($orders) && count($orders) > 0) { ?>
			<br />
	        <div class="row-fluid">
	          <div class="span2"></div>
			  <div class="span4"><strong>Review Orders</strong></div>
			</div>
			
	        <table class="table table-condensed orderlist">
				
		      <thead>
                <tr>
				  <th>#</th>
			      <th>Buyer</th>
			      <th>Items</th>
			      <th>Price</th>
			      <th>Time</th>
			      <th>Progress</th>
		        </tr>
              </thead>
              
              <tbody>
<?php foreach($orders as $order) { ?>				  
				<tr>
				<?php echo form_open('orders', array('class' => 'form-horizontal')); ?>
				  <td><?php echo $order['id']; ?></td>
				  <td><?php echo anchor('user/'.$order['buyer']['user_hash'], $order['buyer']['user_name']); ?></td>
				  <td><ul><?php
		foreach($order['items'] as $item) { ?>
			      <li><?php echo $item['quantity']; ?> x <?php echo anchor('item/'.$item['hash'], $item['name']); ?></li>
	<?php } ?></ul></td>
				  <td><?php echo $coin['symbol']." ".$order['price'];
				  if($local_currency['id'] !== '0') echo '<br />'.$local_currency['symbol'].$order['price_l']; ?></td>
  				  <td><?php echo $order['time_f']; ?></td>
				  <td><?php echo $order['progress_message']; ?>
				  <?php
				  if(isset($review_auth[$order['id']]))
					  echo '<br />'.anchor("reviews/form/{$review_auth[$order['id']]}/{$order['id']}",'Please review this order.');
				  ?></td>
				  </form>
				</tr>
<?php } ?>				
              </tbody>
            </table>
            
<?php } else { echo 'You have no orders at this time!'; } ?>
        </div>

