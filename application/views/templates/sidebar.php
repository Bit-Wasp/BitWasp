
        <div class="span3">
<!-- Logged in bar-->
<?php if(! in_array($current_user['user_role'], array('guest','half'))) { ?>
		  <div class="well sidebar-nav">
			  
			<ul class="nav nav-list">
			  <li><?php echo anchor('user/'.$current_user['user_hash'], $current_user['user_name']); ?></li>
<?php if($current_user['user_role'] == 'Vendor') {
$order_str = 'My Orders'; if($count_new_orders > 0)	$order_str .= " ($count_new_orders new!)"; ?>
			  <li><?php echo anchor('listings','My Listings'); ?></li>
			  <li><?php echo anchor('orders',$order_str); ?></li>
<?php } else if($current_user['user_role'] == 'Buyer') { ?>
			  <li><?php echo anchor('purchases', 'My Purchases'); ?></li>
<?php } else if($current_user['user_role'] == 'Admin') { ?>
			  <li><?php echo anchor('admin/orders', 'Orders'); ?></li>
			  <li><?php echo anchor('admin/disputes', 'Disputes'); ?></li>
<?php } ?>
			</ul>
			
		  </div>
<?php } ?>

<!-- Categories bar-->			
<?php if($block == FALSE) { ?>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Categories</li>
			  <?php if(! in_array($current_user['user_role'], array('guest','half'))) { ?>
			  
			  <div class='row-fluid'>
				<?php echo form_open('location/ship-to'); ?>
				
				  <div class='span4'>Ship To</div>
				  <div class='span5'>
				    <?php echo $locations_w_select; ?>
				  </div>
				  <?php if(isset($ship_to_error)) echo $ship_to_error; ?>
				  <div class='span2'><input type='submit' name='ship_to_submit' class='btn' value='Go' /></div>
				</form>
			  </div>
			  
			  <div class='row-fluid'>
				<?php echo form_open('location/ship-from'); ?>
				  <div class='span4'>Ship From</div>
				  <div class='span5'>
				    <?php echo $locations_select; ?>
				  </div>
				  <?php if(isset($ship_from_error)) echo $ship_from_error; ?>
				  <div class='span2'><input type='submit' name='ship_from_submit' class='btn' value='Go' /></div>
				</form>
			  </div>
			  
<?php } ?>

              <?php echo $cats; ?>
            </ul>
          </div>
<?php } ?>
        </div>
