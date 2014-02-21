
        <div class="span3">
<?php if($role !== 'guest' && $role !== 'half') { ?>
		  <div class="well sidebar-nav">
			  
			<ul class="nav nav-list">
			  <li><?php echo anchor('user/'.$current_user['user_hash'], $current_user['user_name']); ?></li>
			  <li>Balance: <?php echo $current_user['currency']['symbol'].$local_balance; ?><br />
			  <li><?php echo anchor('bitcoin', $coin['name']); ?></li>
<?php if($role == 'vendor') { 
$order_str = 'My Orders'; if($count_new_orders > 0)	$order_str .= " ($count_new_orders new!)"; ?>
			  <li><?php echo anchor('listings','My Listings'); ?></li>
			  <li><?php echo anchor('orders',$order_str); ?></li>
<?php } else if($current_user['user_role'] == 'Buyer') { ?>
			  <li><?php echo anchor('order/list', 'My Purchases'); ?></li>
<?php } else if($current_user['user_role'] == 'Admin') { ?>
			  <li><?php echo anchor('admin/disputes', 'Disputes'); ?></li>
<?php } ?>
			</ul>
			
		  </div>
<?php } ?>
			
<?php if($block == FALSE) { ?>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Categories</li>
			  <?php if($role !== 'guest' && $role !== 'half') { ?>
			  
			  <div class='row-fluid'>
				<?php echo form_open('location/ship-to'); ?>
				  <div class='span4'>Ship To</div>
				  <div class='span5'>
				    <?php echo $locations_w_select; ?>
				  </div>
				  <div class='span2'><input type='submit' name='ship_to_submit' class='btn' value='Go' /></div>
				</form>
			  </div>
			  
			  <div class='row-fluid'>
				<?php echo form_open('location/ship-from'); ?>
				  <div class='span4'>Ship From</div>
				  <div class='span5'>
				    <?php echo $locations_select; ?>
				  </div>
				  <div class='span2'><input type='submit' name='ship_from_submit' class='btn' value='Go' /></div>
				</form>
			  </div>
			  
<?php } ?>
              <?php echo $cats; ?>
            </ul>
          </div>
<?php } ?>
        </div>
