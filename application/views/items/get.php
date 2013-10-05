        <div class="span9 mainContent" id="item_detail">
<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>

		  <div class="itemInfo" id="prod_<?php echo $item['hash']; ?>">
			<h2><?php 
			
			if($logged_in == TRUE) { ?>
			<?php echo anchor('message/send/'.$item['vendor']['user_hash'], 'Message Vendor', 'class="btn"'); ?> 
			<?php } ?>
			<?php echo $item['name'] ?></h2>
			<p class="vendor">
		      Vendor: <?php echo anchor('user/'.$item['vendor']['user_hash'],$item['vendor']['user_name']); ?>
			  <span class="rating">(0)</span>
			</p>
			
			<div class="price">
			  Price: <span class="priceValue"><?php echo $item['price_f'];?></span>
			</div>

			<div id="main">
			  <?php echo $item['description_f']; ?>
			</div>
			
<?php if(strtolower($user_role) == 'buyer'){ ?><div><?php echo anchor('order/'.$item['hash'], 'Purchase Item', 'class="btn"'); ?></div><?php } ?>
		  </div>
		  
          <ul id="item_listing" class="thumbnails">
<?php foreach ($item['images'] as $image): ?>

            <li class="span2 productBox" id="prod_<?php echo $item['hash']; ?>">
              <div class="thumbnail">
				<?php echo anchor('image/'.$image['hash'], "<img src='data:image/jpeg;base64,{$image['encoded']}' class='productImg' title='{$item['name']}' width='100'>"); ?>
              </div>
            </li>
<?php endforeach; ?>

		  </ul>
	    </div>

