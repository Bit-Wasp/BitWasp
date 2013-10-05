
        <div class="span3">
			
<?php if($role !== 'guest' && $role !== 'half') { ?>
		  <div class="well sidebar-nav">
			  
			<ul class="nav nav-list">
			  <li><?php echo anchor('user/'.$current_user['user_hash'], $current_user['user_name']); ?></li>
			  <li>Balance: BTC <?php echo $balance; ?><?php
			  if($current_user['currency']['id'] !== '0') 
				  echo '<br />'.$current_user['currency']['symbol'].$local_balance; ?></li>
			  <li><?php echo anchor('bitcoin', 'Bitcoin'); ?></li>
<?php if($role == 'vendor') { 
$order_str = 'My Orders'; if($count_new_orders > 0)	$order_str .= " ($count_new_orders new!)"; ?>
			  <li><?php echo anchor('listings','My Listings'); ?></li>
			  <li><?php echo anchor('orders',$order_str); ?></li>
<?php } else if(strtolower($current_user['user_role']) == 'buyer') { ?>
			  <li><?php echo anchor('order/list', 'My Purchases'); ?></li>
<?php } ?>
			</ul>
			
		  </div>
<?php } ?>
			
<?php if($block == FALSE) { ?>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Categories</li>
              <?php echo $cats; ?>
            </ul>
          </div>
<?php } ?>
        </div>
