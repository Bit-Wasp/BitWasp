        <div class="span9 mainContent" id="item_detail">
<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>

		  <div class="itemInfo" id="prod_<?php echo $item['hash']; ?>">
			<h2><?php 
			
			if($logged_in == TRUE) { 
				echo anchor('message/send/'.$item['vendor']['user_hash'], 'Message Vendor', 'class="btn"')." "; 
				if($user_role == 'Admin')
					echo anchor('admin/delete_item/'.$item['hash'], 'Delete', 'class="btn"');
			} ?>
			<?php echo $item['name'] ?></h2>
			<div class="row-fluid">
		      Vendor: <?php echo anchor('user/'.$item['vendor']['user_hash'],$item['vendor']['user_name']); ?>
			  <span class="rating">(<?php echo anchor("reviews/view/user/".$item['vendor']['user_hash'], $vendor_rating); ?>)</span><br/>
			  Added: <?php echo $item['add_time_f']; ?>
			  <?php if($item['update_time'] !== '0') { ?>
				  <br />Last Updated:<?php echo $item['update_time_f']; ?>
			  <?php } ?>
			</div>
			
			<div class="price">	
			  Price: <span class="priceValue"><?php echo $item['price_f'];?></span>
			</div>
			Ship's From: <?php echo $item['ship_from_f']; ?><br />
			
			<?php 
			if($shipping_costs !== FALSE && count($shipping_costs) > 0) {
				echo "Ship's To: <br />";
				foreach($shipping_costs as $shipping_charge) { 
					echo " - {$shipping_charge['destination_f']} {$browsing_currency['symbol']} {$shipping_charge['cost']} <br />";
				} 
			}
			?>
			<div id="main">
			  <?php echo $item['description_f']; ?>
			</div>
			
<?php if(strtolower($user_role) == 'buyer'){ ?><div><?php echo anchor('order/'.$item['hash'], 'Purchase Item', 'class="btn"'); ?></div><?php } ?>
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
