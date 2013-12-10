        <div class="span9 mainContent" id="manage_items">
          <h2>Shipping Costs: <?php echo $item['name']; ?></h2>
          <?php if(isset($returnMessage)) echo '<div class="alert alert-success">' . $returnMessage . '</div>'; ?>
		  <?php echo form_open('listings/shipping/'.$item['hash'], array('class' => 'form-horizontal')); ?>

		    <table cellspacing='0' class='table'>
			  <thead>
			    <tr>
				  <th>Destination</th>
				  <th>Cost</th>
				  <th>Offered?</th>
				  <th><?php if($shipping_costs !== FALSE) echo "Delete?"; ?></th>
			    </tr>
			  </thead>
			  <tbody>
<?php if($shipping_costs == FALSE) { ?>

			    <tr>
				  <td class='span3'>Worldwide <input type='hidden' name='country[worldwide]' value='worldwide' /> </td>
				  <td class='span4'>
                    <div class="input-prepend">
                      <span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span>
                      <input type="text" class="span2" name="price[worldwide]" value="<?php echo ($item['currency']['id'] == '0') ? '0.1' : '10'; ?>" />
                    </div>
				  </td>
				  <td class="span3"><input type="checkbox" name="enabled[worldwide]" value="1" /></td>
				  <td class="span3"></td>
			    </tr>
			  
			    <tr>
				  <td class="span3">
			        <select name="country[]" autocomplete='off'>
				      <option value=''></option>
<?php foreach($locations as $location) { 
	if($location['id'] == '1') continue; ?>
					  <option value="<?php echo $location['id']; ?>"<?php if($location['id'] == $account['location']) echo ' selected="selected" '; ?>><?php echo $location['country']; ?></option>
<?php } ?>
				    </select>
				  </td>
				  <td class="span4">
                    <div class="input-prepend">
                      <span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span>
                      <input type="text" class="span2" name="price[]" value="<?php echo ($item['currency']['id'] == '0') ? '0.1' : '10'; ?>" />
                    </div>
				  </td>
				  <td class="span3"><input type="checkbox" name="enabled[]" value="1" /></td>
				  <td class="span3"></td>
			    </tr>
<?php } else { 
	
	foreach($shipping_costs as $cost) {?>

			    <tr>
				  <td class='span3'><?php echo $cost['destination_f']; ?><input type='hidden' name='country[]' value='<?php echo $cost['destination_id']; ?>' /></td>
				  <td class='span4'>
                    <div class="input-prepend">
                      <span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span>
                      <input type="text" class="span2" name="price[]" value="<?php echo $cost['cost']; ?>" />
                    </div>
				  </td>
				  <td class="span3"><input type="checkbox" name="enabled[<?php echo $cost['destination_id']; ?>]" value="1" <?php if($cost['enabled'] == '1') echo 'checked '; ?>/></td>
				  <td class="span3"><input type="checkbox" name="delete[<?php echo $cost['destination_id']; ?>]" value="1" /></td>
			    </tr>
<?php }
 } ?>		    
		        <tr>
				  <td class='span3'>
				    <select name="country[]" autocomplete='off'>
				      <option value=''></option>
<?php foreach($locations as $location) { 
	if($location['id'] == '1') continue; ?>
					  <option value="<?php echo $location['id']; ?>"><?php echo $location['country']; ?></option>
<?php } ?>
				    </select>
				  </td>
				  <td class='span4'>
                    <div class="input-prepend">
                      <span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span>
                      <input type='text' class='span2' name='price[]' value="<?php echo ($item['currency']['id'] == '0') ? '0.1' : '10'; ?>" />
                    </div>
				  </td>
				  <td class='span3'></td>
				  <td class="span3"></td>				  
			    </tr>
			  			
			  </tbody>
		    </table>
		    
            <div class="form-actions">
		      <input type='submit' name='shipping_costs_update' value='Update' class='btn btn-primary' />
              <?php echo anchor('listings/edit/'.$item['hash'],'Cancel', array('class'=>'returnLink btn'));?>
		    </div>
		  </form>
		</div>
