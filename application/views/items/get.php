        <div class="span9 mainContent" id="item_detail">
<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>

		  <div class="itemInfo" id="prod_<?php echo $item['hash']; ?>">
			<h2><?php 
			
			if($current_user['logged_in'] == TRUE) {
				echo anchor('message/send/'.$item['vendor']['user_hash'], 'Message Vendor', 'class="btn"')." "; 
				if($current_user['user_role'] == 'Admin')
					echo anchor('admin/delete_item/'.$item['hash'], 'Delete', 'class="btn"');
			} ?>
			<?php echo $item['name'] ?></h2>
			
			<div class="well">
				<div class='row-fluid'>
					<div class='span1'>Vendor</div>
					<div class='span5'><?php echo anchor('user/'.$item['vendor']['user_hash'],$item['vendor']['user_name']); ?> 			  <span class="rating">(<?php echo anchor("reviews/view/user/".$item['vendor']['user_hash'], $vendor_rating); ?>)</span></div>
				</div>
				<div class='row-fluid'>
					<div class='span1'>Added:</div>
					<div class='span5'><?php echo $item['add_time_f']; ?></div>
				</div>
			  <?php if($item['update_time'] !== '0') { ?>
				<div class='row-fluid'>
					<div class='span1'>Updated:</div>
					<div class='span5'><?php echo $item['update_time_f']; ?></div>
				</div>
			  <?php } ?>
				<div class='row-fluid'>
					<div class='span1'>Price</div>
					<div class='span2'><?php
			  if($current_user['currency']['id'] !== '0') echo $current_user['currency']['symbol'].number_format($item['price_l'],2)." / ";
			  echo $coin['symbol']." ".number_format($item['price_b'],8); ?></div>
					<?php if($current_user['user_role']== 'Buyer') { ?>
					<div class='span2'><?php echo anchor('purchase/'.$item['hash'], 'Purchase Item', 'class="btn"'); ?></div>
					<?php } ?>
				</div>
			</div>
			
			<div class='well'>
				<div class='row-fluid'>
					<div class='span2'>Ship's From:</div>
					<div class='span6'><?php echo $item['ship_from_f']; ?></div>
				</div>
				<?php 
				if($shipping_costs !== FALSE && count($shipping_costs) > 0) { ?>
				<div class='row-fluid'>
					<div class='row-fluid'>
						<div class='span2'>Ship's To:</div>
						<div class='span6'>
						<?php $c = 0; foreach($shipping_costs as $shipping_charge) { ?>
							<div class='row-fluid'>
								<div class='span5'><?php echo $shipping_charge['destination_f']; ?></div>
								<div class='span6'><?php 
								if($current_user['currency']['id'] !== '0')  echo $current_user['currency']['symbol'].number_format($shipping_charge['cost']*$current_user['currency']['rate'],2)." / "; ?>
								<?php echo $coin['symbol']; ?> <?php echo number_format($shipping_charge['cost'],8); ?></div>
							</div>
						<?php } ?>
						</div>
					</div>
				</div>
				<?php }	?>
			</div>

			<div class="well">
			  <?php echo $item['description_f']; ?>
			</div>

		  </div>
		  
          <ul id="item_listing" class="thumbnails">
<?php foreach ($item['images'] as $image): ?>

            <li class="span2 productBox" id="prod_<?php echo $item['hash']; ?>">
              <div class="thumbnail">
				<?php echo anchor('image/'.$image['hash'], "<img src='data:image/jpeg;base64,{$image['encoded']}' class='productImg' title='{$item['name']}' >"); ?>
              </div>
            </li>
<?php endforeach; ?>

		  </ul>
		  
		  <?php if($reviews !== FALSE) { ?>
			<div class='row-fluid'>
				<div class='well'><strong>Recent Reviews</strong><br />
				<?php echo anchor('reviews/view/item/'.$item['hash'], "[All Reviews ({$review_count['all']})]"); ?> <?php echo anchor('reviews/view/item/'.$item['hash'].'/0', "[Positive Reviews ({$review_count['positive']})]"); ?> <?php echo anchor('reviews/view/item/'.$item['hash'].'/1', "[Disputed Reviews {$review_count['disputed']}]"); ?>
				<?php	foreach($reviews as $review) { ?>
					<br /><div class='row-fluid'>
						<div class='span3'><?php foreach($review['rating'] as $rating_name => $rating){ echo ucfirst($rating_name) ." - $rating/5<br />"; } ?>Average: <?php echo $review['average_rating']; ?></div>
						<div class='span2'>Comments:</div>
						<div class='span6'><?php echo $review['comments']; ?></div>
					</div><?php	} ?>
				</div>
			</div>
			<?php } ?>
	    </div>
