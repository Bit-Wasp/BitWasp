        <div class="span9 mainContent" id="admin_edit_">
		  <h2>Delete Item</h2>
		  
		  <?php echo form_open('admin/delete_item/'.$item['hash'], array('class' => 'form-horizontal')); ?>
		  
		    <p>Complete the following form to inform <?php echo $item['vendor']['user_name']; ?> why this listing is going to be removed.</p>
		  
		    <div class="row-fluid">
			  <div class="span2 offset1">Item</div>
			  <div class="span4"><?php echo $item['name']; ?></div>
		    </div>

			<div class="control-group">
			  <label class="control-label" for="reason_for_removal">Reason for Removal</label>
			  <div class="controls">
				<textarea name="reason_for_removal" class="span7" rows="4"></textarea>
			  </div>
			  <span class="help-inline offset3"><?php echo form_error('reason_for_removal'); ?></span>
			</div>
		  
			<div class="form-actions">
			  <button type='submit' class="btn btn-primary">Remove</button>
			  <?php echo anchor('item/'.$item['hash'], 'Cancel', 'title="Cancel" class="btn"');?>
			</div>
		  
		  </form>
		</div>
