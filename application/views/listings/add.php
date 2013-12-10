        <div class="span9 mainContent" id="add_item">
          <h2>Add Item</h2>
          <?php echo form_open('listings/add', array('class' => 'form-horizontal')); ?>
            <fieldset>
              <?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
              <div class="control-group">
                <label class="control-label" for="name">Name</label>
                <div class="controls">
                  <input type='text' class='span6' name='name' value="<?php echo set_value('name'); ?>" />
                  <span class="help-inline"><?php echo form_error('name'); ?></span>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="description">Description</label>
                <div class="controls">
                  <textarea name='description' class='span9' rows="8"><?php echo set_value('description'); ?></textarea>
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
                    <span class="add-on"><i><?php echo $local_currency['symbol']; ?></i></span>
                    <input type='text' name='price' value="<?php echo set_value('price'); ?>" />
                  </div>
                  <span class="help-inline"><?php echo form_error('price'); ?></span>
                </div>
              </div>

			  <div class="control-group">
				<label class="control-label" for="ship_from">Ship From</label>
				<div class="controls">
				  <select name="ship_from">
<?php foreach($locations as $location) { 
	if($location['id'] == '1') continue;
?>
					<option value='<?php echo $location['id']; ?>'><?php echo $location['country']; ?></option>
<?php } ?>
				  </select>
                  <span class="help-inline"><?php echo form_error('ship_from'); ?></span>
				</div>
			  </div>

              <div class="control-group">
                <label class="control-label" for="hidden">Hidden Listing</label>
                <div class="controls">
                  <input name="hidden" type="checkbox" value='on' >
                  <span class="help-inline"><?php echo form_error('hidden'); ?></span>
                </div>
              </div>
 
              <div class="form-actions">
                <input type="submit" value="Create" class="btn btn-primary" />
                <?php echo anchor("listings","Cancel", 'class="btn"'); ?>
              </div>
            </fieldset>
          </form>
        </div>

