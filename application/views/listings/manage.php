        <div class="span9 mainContent" id="manage_items">
          <h2>Listings</h2>
          <?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
		  <br />
		  
<?php if($items !== FALSE) { 
	foreach($items as $item): ?>
			<div class='span11 well'>
			  <div class='span2'><?php echo anchor('item/'.$item['hash'], "<img src='data:image/jpeg;base64,{$item['main_image']['encoded']}' title='{$item['name']}' >"); ?></div>
			  <div class='span6'><?php echo anchor('item/'.$item['hash'], $item['name']);?><br /><?php echo $item['price_f']; ?><br /><?php echo $item['description_s']; ?></div>
			  <div class='span2'><?php if($item['hidden'] == '1') echo "[hidden]"; ?></div>
			  <div class='span3'><?php echo anchor('listings/edit/'.$item['hash'], 'Edit', 'class="btn btn-mini"'); ?> 
<?php echo anchor('listings/images/'.$item['hash'], 'Images', 'class="btn btn-mini"'); ?> 
<?php echo anchor('listings/delete/'.$item['hash'], 'Delete', 'class="btn btn-danger btn-mini"'); ?></div>
			</div>
<?php endforeach; 
 
} else { ?>You have no listings!
<?php } ?>		  

		  <br />
		  <div class='span12'>
	        <form class="form-horizontal">
		      <div class="form-actions">
			    <?php echo anchor('listings/add', 'Add a new listing', 'class="btn btn-primary"');?>
			    <?php echo anchor('home', 'Cancel', 'class="btn"');?>
		      </div>
		    </form>
		  </div>
	    </div>
