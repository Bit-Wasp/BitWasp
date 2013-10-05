        <div class="span9 mainContent" id="manage_items">
          <h2>Listings</h2>
          <?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
          <fieldset>
<?php if($items !== FALSE){ ?>
            <table class="table">
              <thead>
                <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                </tr>
              </thead>
<?php foreach($items as $item): ?>
			  <tr>
			    <td><?php echo anchor('item/'.$item['hash'], "<img src='data:image/jpeg;base64,{$item['main_image']['encoded']}' title='{$item['name']}' width='100'>"); ?></td>
			    <td><?php echo anchor('item/'.$item['hash'], $item['name']);?><br /><?php echo $item['price_f']; ?><br /><?php echo $item['description_s']; ?></td>
		        <td><?php if($item['hidden'] == '1') echo "[hidden]"; ?></td>
			    <td><br /><?php echo anchor('listings/edit/'.$item['hash'], 'Edit', 'class="btn btn-mini"'); ?> 
<?php echo anchor('listings/images/'.$item['hash'], 'Images', 'class="btn btn-mini"'); ?> 
<?php echo anchor('listings/delete/'.$item['hash'], 'Delete', 'class="btn btn-danger btn-mini"'); ?></td>
			  </tr>
<?php endforeach; ?>
		    </table>

<? } else { ?>You have no listings!
<? } ?>
	      <form class="form-horizontal">
		    <div class="form-actions">
			  <?php echo anchor('listings/add', 'Add a new listing', 'class="btn btn-primary"');?>
			  <?php echo anchor('home', 'Cancel', 'class="btn"');?>
		    </div>
		  </form>
		  </fieldset>
	    </div>
