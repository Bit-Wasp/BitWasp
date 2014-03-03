        <div class="span9 mainContent" id="my-orders">
		  <h2>My Purchases</h2>

			<?php if(isset($returnMessage)) { ?>
			<div class='alert<?php echo (isset($success)) ? ' alert-success' : ''; ?>'><?php echo $returnMessage; ?></div>			
			<?php } ?>
		  
<?php if($escrow_balance > 0) { ?>
		  <div class="row-fluid">
			<div class="span3">Escrow Balance</div>
			<div class="span4"><?php echo $coin['symbol']; ?> <?php echo $escrow_balance; ?></div>
		  </div>
<?php } ?>
		  
<?php if(is_array($orders)) { ?>
			<br />
	        <div class="row-fluid">
	          <div class="span2"></div>
			  <div class="span4"><strong>Review Orders</strong></div>
			</div>
			
	        <table class="table table-condensed orderlist">
				
		      <thead>
                <tr>
			      <th>Vendor</th>
			      <th>Items</th>
			      <th>Price</th>
			      <th>Progress</th>
		        </tr>
              </thead>
              
              <tbody>
<?php foreach($orders as $order) { ?>				  
				<tr>
				<?php echo form_open('order/list', array('class' => 'form-horizontal')); ?>
				  <td><?php echo anchor('user/'.$order['vendor']['user_hash'], $order['vendor']['user_name']).'<br />#'.$order['id']; ?></td>
				  <td>
<?php foreach($order['items'] as $item) { 
			if($order['progress'] == '0') { ?>
					<select name='quantity[<?php echo $item['hash']; ?>]' class="span1" autocomplete="off">
<?php			for($i = 0; $i < 11; $i++) {?>
					  <option value='<?php echo $i; ?>' <?php if($i == $item['quantity']) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
<?php 			} ?>
					</select> - <?php echo (($item['hash'] == 'removed') ? $item['name'] : anchor('item/'.$item['hash'], $item['name']));  ?><br />
<?php		} else {
				echo $item['quantity'] . ' x '. (($item['hash'] == 'removed') ? $item['name'] : anchor('item/'.$item['hash'], $item['name'])). '<br />'; 
			}
	} ?>
				  </td>
				  <td><?php echo $coin['symbol']." ".$order['price'];
				  if($local_currency['id'] !== '0') echo '<br />'.$local_currency['symbol'].$order['price_l']; ?></td>
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
            
<?php } else { ?>
          <p>You have no purchases at present.</p>
<?php } ?>
</div>
