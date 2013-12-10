        <div class="span9 mainContent" id="manage_items">
          <h2>Edit Item</h2>
          <?php if(isset($returnMessage)) echo '<div class="alert alert-success">' . $returnMessage . '</div>'; ?>

<?php echo form_open('listings/edit/'.$item['hash'], array('class' => 'form-horizontal')); ?>
		    <fieldset>
			  <div class="control-group">
			    <label class="control-label" for="name">Name</label>
			    <div class="controls">
				  <input type='text' name='name' class='span6' value="<?php echo $item['name']; ?>" size='12' />
				  <span class="help-inline"><?php echo form_error('name'); ?></span>
			    </div>
			  </div> 
		
              <div class="control-group">
                <label class="control-label" for="description">Description</label>
                <div class="controls">
				  <textarea name='description' class='span9' rows='8'><?php echo $item['description']; ?></textarea>	
				  <span class="help-inline"><?php echo form_error('description'); ?></span>
	            </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="category">Category</label>
                <div class="controls">
                  <?php echo $categories; ?>
                  <span class="help-inline"><?php echo form_error('category'); ?></span>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label" for="price">Price</label>
                <div class="controls">
                  <div class="input-prepend">
                    <span class="add-on"><i><?php echo $item['currency']['symbol']; ?></i></span>
                    <input type='text' class='span12' name='price' value="<?php echo $item['price']; ?>" />
                  </div>
                  <span class="help-inline"><?php echo form_error('price'); ?></span>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="currency">Currency</label>
                <div class="controls">
				  <select name='currency' class='span5' autocomplete="off">
<?php foreach ($currencies as $currency) { ?>
		            <option value="<?php echo $currency['id'];?>" <?php echo ($item['currency']['id'] == $currency['id']) ? 'selected="selected"' : '';?>><?php echo $currency['name'];?> (<?php echo $currency['symbol'];?>)</option>
<?php } ?>
                  </select>
                  <span class="help-inline"><?php echo form_error('currency'); ?></span>
                </div>
              </div>

			  <div class="control-group">
				<label class="control-label" for="ship_from">Ship From</label>
				<div class="controls">
				  <select name="ship_from" autocomplete="off">
<?php foreach($locations as $location) { 
	if($location['id'] == '1') continue; 
?>
					<option value='<?php echo $location['id']; ?>' <?php echo ($location['id'] == $item['ship_from']) ? 'selected="selected"' : ''; ?>><?php echo $location['country']; ?></option>
<?php } ?>
				  </select>
                  <span class="help-inline"><?php echo form_error('ship_from'); ?></span>
				</div>
			  </div>

			  <div class="control-group">
				<label class="control-label" for="shipping_charges">Shipping Costs</label>
				<div class="controls">
				  <label class='help-inline'><?php echo anchor('listings/shipping/'.$item['hash'], 'Configure'); ?></label>
				</div>
			  </div>

              <div class="form-actions">
		        <input type='submit' value='Update' class='btn btn-primary' />
                <?php echo anchor('listings','Cancel', array('class'=>'returnLink btn'));?>
		      </div>
		    </fieldset>
		  </form>
		</div>
